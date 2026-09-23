(() => {
    const config = window.COMPILER_SET;
    if (!config) {
        return;
    }

    const fill = document.getElementById('compileFill');
    const text = document.getElementById('compileText');
    const button = document.getElementById('compileBtn');

    const setProgress = (value, label) => {
        if (fill) {
            fill.style.width = `${Math.round(value * 100)}%`;
        }
        if (text) {
            text.textContent = label;
        }
    };

    const loadImage = (src) => new Promise((resolve, reject) => {
        const image = new Image();
        image.crossOrigin = 'anonymous';
        image.onload = () => resolve(image);
        image.onerror = () => reject(new Error('Unable to load ' + src));
        image.src = src;
    });

    const compile = async () => {
        button.disabled = true;
        setProgress(0.04, 'Preparing AR Target');
        const Compiler = window.MINDAR?.IMAGE?.Compiler;
        if (!Compiler) {
            throw new Error('MindAR compiler is not available in this browser.');
        }
        const images = [];
        for (const item of config.images) {
            images.push(await loadImage(item.src));
        }
        const compiler = new Compiler();
        await compiler.compileImageTargets(images, (progress) => {
            const ratio = progress > 1 ? progress / 100 : progress;
            setProgress(0.08 + ratio * 0.82, `Preparing AR Target  ${Math.round(ratio * 100)}%`);
        });
        const exported = await compiler.exportData();
        const bytes = exported instanceof Uint8Array
            ? exported
            : new Uint8Array(exported instanceof ArrayBuffer ? exported : (exported?.buffer || exported));
        if (!bytes.byteLength) {
            throw new Error('Compiler produced an empty target file.');
        }
        let binary = '';
        const step = 0x8000;
        for (let i = 0; i < bytes.length; i += step) {
            binary += String.fromCharCode.apply(null, bytes.subarray(i, i + step));
        }
        const body = new FormData();
        body.append('csrf_token', config.csrf);
        body.append('poster_ids', JSON.stringify(config.ids));
        body.append('target_b64', btoa(binary));
        body.append('target', new Blob([bytes], { type: 'application/octet-stream' }), 'penang-set.mind');
        setProgress(0.94, 'Saving compiled target…');
        const response = await fetch(config.saveUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': config.csrf },
            body,
        });
        const data = await response.json();
        if (!data.ok) {
            throw new Error(data.error || 'The server could not store the target.');
        }
        setProgress(1, 'AR Target Ready. You can scan the Penang photos now.');
    };

    button.addEventListener('click', async () => {
        try {
            await compile();
        } catch (error) {
            setProgress(0, error.message || 'Compilation failed.');
            button.disabled = false;
        }
    });

    if (new URLSearchParams(window.location.search).get('auto') === '1') {
        button.click();
    }
})();
