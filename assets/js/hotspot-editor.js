(() => {
    const config = window.EDITOR_CONFIG;
    if (!config) {
        return;
    }

    const canvas = document.getElementById('posterCanvas');
    const image = document.getElementById('posterImage');
    const form = document.getElementById('hotspotForm');
    const empty = document.getElementById('hotspotEmpty');
    const fields = {
        name: document.getElementById('hsName'),
        attraction: document.getElementById('hsAttraction'),
        video: document.getElementById('hsVideo'),
        type: document.getElementById('hsType'),
    };

    let hotspots = (config.hotspots || []).map((item, index) => ({
        localId: item.id || `new-${index}`,
        name: item.name || `Hotspot ${index + 1}`,
        attraction_id: item.attraction_id || '',
        x: Number(item.x),
        y: Number(item.y),
        width: Number(item.width),
        height: Number(item.height),
        z_index: Number(item.z_index || 1),
        video_url: item.video_url || '',
        content_type: item.content_type || 'card',
    }));
    let selected = null;
    let drag = null;

    const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

    const render = () => {
        canvas.querySelectorAll('.hotspot').forEach((node) => node.remove());
        const rect = image.getBoundingClientRect();
        hotspots.forEach((spot) => {
            const el = document.createElement('div');
            el.className = 'hotspot' + (selected === spot ? ' active' : '');
            el.style.left = `${spot.x * 100}%`;
            el.style.top = `${spot.y * 100}%`;
            el.style.width = `${spot.width * 100}%`;
            el.style.height = `${spot.height * 100}%`;
            el.style.zIndex = String(spot.z_index);
            el.textContent = spot.name;
            const handle = document.createElement('span');
            handle.className = 'resize';
            el.appendChild(handle);
            el.addEventListener('pointerdown', (event) => {
                event.stopPropagation();
                selected = spot;
                bindForm();
                const canvasRect = image.getBoundingClientRect();
                drag = {
                    mode: event.target === handle ? 'resize' : 'move',
                    startX: event.clientX,
                    startY: event.clientY,
                    x: spot.x,
                    y: spot.y,
                    width: spot.width,
                    height: spot.height,
                    canvasW: canvasRect.width,
                    canvasH: canvasRect.height,
                };
                el.setPointerCapture(event.pointerId);
                render();
            });
            el.addEventListener('pointermove', (event) => {
                if (!drag || selected !== spot) {
                    return;
                }
                const dx = (event.clientX - drag.startX) / drag.canvasW;
                const dy = (event.clientY - drag.startY) / drag.canvasH;
                if (drag.mode === 'move') {
                    spot.x = clamp(drag.x + dx, 0, 1 - spot.width);
                    spot.y = clamp(drag.y + dy, 0, 1 - spot.height);
                } else {
                    spot.width = clamp(drag.width + dx, 0.06, 1 - spot.x);
                    spot.height = clamp(drag.height + dy, 0.06, 1 - spot.y);
                }
                render();
            });
            el.addEventListener('pointerup', () => { drag = null; });
            canvas.appendChild(el);
        });
        void rect;
    };

    const bindForm = () => {
        if (!selected) {
            form.classList.add('d-none');
            empty.classList.remove('d-none');
            return;
        }
        form.classList.remove('d-none');
        empty.classList.add('d-none');
        fields.name.value = selected.name;
        fields.attraction.value = selected.attraction_id || '';
        fields.video.value = selected.video_url || '';
        fields.type.value = selected.content_type || 'card';
    };

    image.addEventListener('click', (event) => {
        const rect = image.getBoundingClientRect();
        const width = 0.18;
        const height = 0.14;
        const x = clamp((event.clientX - rect.left) / rect.width - width / 2, 0, 1 - width);
        const y = clamp((event.clientY - rect.top) / rect.height - height / 2, 0, 1 - height);
        selected = {
            localId: `new-${Date.now()}`,
            name: `Hotspot ${hotspots.length + 1}`,
            attraction_id: '',
            x, y, width, height,
            z_index: hotspots.length + 1,
            video_url: '',
            content_type: 'card',
        };
        hotspots.push(selected);
        bindForm();
        render();
    });

    ['name', 'attraction', 'video', 'type'].forEach((key) => {
        fields[key].addEventListener('input', () => {
            if (!selected) {
                return;
            }
            selected.name = fields.name.value;
            selected.attraction_id = fields.attraction.value;
            selected.video_url = fields.video.value;
            selected.content_type = fields.type.value;
            render();
        });
    });

    document.getElementById('deleteHotspot').addEventListener('click', () => {
        if (!selected) {
            return;
        }
        hotspots = hotspots.filter((item) => item !== selected);
        selected = null;
        bindForm();
        render();
    });

    document.getElementById('saveHotspots').addEventListener('click', async () => {
        const response = await fetch(config.saveUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrf },
            body: JSON.stringify({
                csrf: config.csrf,
                poster_id: config.posterId,
                hotspots: hotspots.map((item) => ({
                    name: item.name,
                    attraction_id: item.attraction_id,
                    x: item.x,
                    y: item.y,
                    width: item.width,
                    height: item.height,
                    z_index: item.z_index,
                    video_url: item.video_url,
                    content_type: item.content_type,
                })),
            }),
        });
        const data = await response.json();
        window.alert(data.ok ? 'Hotspots saved.' : (data.error || 'Unable to save hotspots.'));
    });

    if (image.complete) {
        render();
    } else {
        image.addEventListener('load', render);
    }
    window.addEventListener('resize', render);
    bindForm();
})();
