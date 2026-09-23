(() => {
    const config = window.COMPILER_CONFIG;
    if (!config) {
        return;
    }

    const select = document.getElementById('compilePoster');
    const fill = document.getElementById('compileFill');
    const text = document.getElementById('compileText');
    const button = document.getElementById('compileBtn');

    const setProgress = (value, label) => {
        fill.style.width = `${Math.round(value * 100)}%`;
        text.textContent = label;
    };

    const loadImage = (src) => new Promise((resolve, reject) => {
        const image = new Image();
        image.crossOrigin = 'anonymous';
        image.onload = () => resolve(image);
        image.onerror = () => reject(new Error('Unable to load the poster image.'));
        image.src = src;
    });

    select?.addEventListener('change', () => {
        const upload = document.getElementById('uploadPosterId');
        if (upload) {
            upload.value = select.value;
        }
    });

    button.addEventListener('click', async () => {
        const option = select.selectedOptions[0];
        if (!option) {
            return;
        }
        button.disabled = true;
        setProgress(0.05, 'Preparing AR Target');
        try {
            const Compiler = window.MINDAR?.IMAGE?.Compiler;
            if (!Compiler) {
                throw new Error('MindAR compiler is not available in this browser.');
            }
            const image = await loadImage(option.dataset.image);
            const compiler = new Compiler();
            await compiler.compileImageTargets([image], (progress) => {
                setProgress(0.1 + progress * 0.8, `Preparing AR Target  ${Math.round(progress * 100)}%`);
            });
            const exported = await compiler.exportData();
            const blob = new Blob([exported], { type: 'application/octet-stream' });
            const body = new FormData();
            body.append('csrf_token', config.csrf);
            body.append('poster_id', option.value);
            body.append('target', blob, `poster-${option.value}.mind`);
            setProgress(0.95, 'Saving compiled target…');
            const response = await fetch(config.saveUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': config.csrf },
                body,
            });
            const data = await response.json();
            if (!data.ok) {
                throw new Error(data.error || 'The server could not store the target.');
            }
            setProgress(1, 'AR Target Ready');
        } catch (error) {
            setProgress(0, error.message || 'Compilation failed.');
        } finally {
            button.disabled = false;
        }
    });
})();
