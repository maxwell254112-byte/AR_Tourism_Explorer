(() => {
    const config = window.AR_CONFIG || {};
    const startBtn = document.getElementById('startBtn');
    const welcome = document.getElementById('welcome');
    const stage = document.getElementById('stage');
    const statusEl = document.getElementById('arStatus');
    const hintEl = document.getElementById('arHint');
    const cardEl = document.getElementById('arCard');
    const muteBtn = document.getElementById('muteBtn');
    const infoBtn = document.getElementById('infoBtn');
    const youtubeBtn = document.getElementById('youtubeBtn');
    const mapBtn = document.getElementById('mapBtn');
    const closeCardBtn = document.getElementById('closeCardBtn');

    let selected = null;
    let tracked = false;
    let muted = true;
    let markers = [];

    const setStatus = (text) => { statusEl.textContent = text; };
    const setHint = (text) => { hintEl.textContent = text; };

    const youtubeId = (url) => {
        const match = String(url || '').match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([A-Za-z0-9_-]{11})/);
        return match ? match[1] : null;
    };

    const hideCard = () => {
        selected = null;
        cardEl.classList.add('hidden');
        cardEl.innerHTML = '';
        youtubeBtn.removeAttribute('href');
        mapBtn.removeAttribute('href');
    };

    const showCard = (hotspot) => {
        const attraction = hotspot.attraction;
        if (!attraction) {
            cardEl.innerHTML = `<div class="body"><h2>${hotspot.name}</h2><p>No video has been configured for this photo.</p></div>`;
            cardEl.classList.remove('hidden');
            return;
        }
        selected = hotspot;
        const videoId = youtubeId(hotspot.videoUrl || attraction.youtubeUrl);
        const videoBlock = videoId
            ? `<iframe src="https://www.youtube.com/embed/${videoId}?autoplay=1&mute=1&playsinline=1&rel=0" title="${attraction.name}" allow="autoplay; encrypted-media; fullscreen" style="width:100%;height:180px;border:0"></iframe>`
            : '<p>Video unavailable. Please try again later.</p>';
        cardEl.innerHTML = `
            <img src="${attraction.image}" alt="${attraction.name}">
            <div class="body">
                <h2>${attraction.name}</h2>
                <p>${attraction.shortDescription || ''}</p>
                ${videoBlock}
            </div>
        `;
        youtubeBtn.href = attraction.youtubeUrl || '#';
        mapBtn.href = attraction.mapUrl || '#';
        cardEl.classList.remove('hidden');
    };

    const requestCamera = async () => {
        if (!navigator.mediaDevices?.getUserMedia) {
            throw new Error('This browser does not support camera access.');
        }
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: 'environment' } },
            audio: false,
        });
        stream.getTracks().forEach((track) => track.stop());
    };

    const addHotspotMarkers = (anchor, hotspots, THREE) => {
        return hotspots.map((hotspot) => {
            const width = Math.max(0.08, hotspot.width);
            const height = Math.max(0.08, hotspot.height);
            const geometry = new THREE.PlaneGeometry(width, height);
            const material = new THREE.MeshBasicMaterial({
                color: 0xc4a35a,
                transparent: true,
                opacity: 0.28,
                side: THREE.DoubleSide,
            });
            const mesh = new THREE.Mesh(geometry, material);
            mesh.position.set(
                hotspot.x + hotspot.width / 2 - 0.5,
                0.5 - (hotspot.y + hotspot.height / 2),
                0.01
            );
            mesh.userData.hotspot = hotspot;
            anchor.group.add(mesh);
            return mesh;
        });
    };

    const startMindAr = async (targetSrc, targetGroups) => {
        await requestCamera();
        welcome.classList.add('hidden');
        stage.classList.remove('hidden');

        const mindarThree = new window.MINDAR.IMAGE.MindARThree({
            container: document.getElementById('ar-container'),
            imageTargetSrc: targetSrc,
            uiLoading: 'no',
            uiScanning: 'no',
            uiError: 'no',
        });

        const { renderer, scene, camera } = mindarThree;
        const THREE = window.THREE;
        markers = [];

        targetGroups.forEach((group) => {
            const anchor = mindarThree.addAnchor(group.index);
            const groupMarkers = addHotspotMarkers(anchor, group.hotspots, THREE);
            markers.push(...groupMarkers);

            anchor.onTargetFound = () => {
                tracked = true;
                setStatus('PHOTO DETECTED');
                setHint(group.name + ' detected. Playing the YouTube video.');
                const first = group.hotspots[0];
                if (first) {
                    showCard(first);
                }
            };
            anchor.onTargetLost = () => {
                tracked = false;
                hideCard();
                setStatus('SEARCHING');
                setHint('Photo not detected. Move closer and keep the whole photo visible.');
            };
        });

        const raycaster = new THREE.Raycaster();
        const pointer = new THREE.Vector2();
        renderer.domElement.addEventListener('click', (event) => {
            if (!tracked) {
                return;
            }
            const rect = renderer.domElement.getBoundingClientRect();
            pointer.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
            pointer.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
            raycaster.setFromCamera(pointer, camera);
            const hits = raycaster.intersectObjects(markers);
            if (hits[0]) {
                showCard(hits[0].object.userData.hotspot);
            }
        });

        renderer.setAnimationLoop(() => {
            renderer.render(scene, camera);
        });

        await mindarThree.start();
        setStatus('SEARCHING');
        setHint('Point your camera at a Penang photo');
    };

    const startExperience = async () => {
        setStatus('LOADING');
        if (config.mode === 'set') {
            const response = await fetch(config.setEndpoint);
            const data = await response.json();
            if (!data.ok) {
                throw new Error(data.error || 'Unable to load AR photos.');
            }
            await startMindAr(data.target, data.targets);
            return;
        }

        const response = await fetch(`${config.posterEndpoint}?id=${encodeURIComponent(startBtn.dataset.poster)}`);
        const data = await response.json();
        if (!data.ok) {
            throw new Error(data.error || 'Unable to load AR content.');
        }
        await startMindAr(data.poster.target, [{
            index: 0,
            name: data.poster.name,
            hotspots: data.hotspots,
        }]);
    };

    startBtn?.addEventListener('click', async () => {
        try {
            await startExperience();
        } catch (error) {
            if (String(error.message || error).toLowerCase().includes('denied') || error.name === 'NotAllowedError') {
                setStatus('CAMERA DENIED');
                window.alert('Camera access was denied. Please allow camera permission in your browser settings and try again.');
                return;
            }
            setStatus('ERROR');
            window.alert(error.message || 'Unable to start the AR experience.');
        }
    });

    muteBtn.addEventListener('click', () => {
        muted = !muted;
        muteBtn.innerHTML = muted
            ? '<i class="fa-solid fa-volume-xmark"></i><br>Mute'
            : '<i class="fa-solid fa-volume-high"></i><br>Unmute';
        cardEl.querySelectorAll('iframe').forEach((frame) => {
            const src = new URL(frame.src);
            src.searchParams.set('mute', muted ? '1' : '0');
            frame.src = src.toString();
        });
    });

    infoBtn.addEventListener('click', () => {
        if (selected?.attraction) {
            window.location.href = `attraction.php?id=${selected.attraction.id}`;
        } else {
            setHint('Scan a photo first to view information.');
        }
    });

    closeCardBtn.addEventListener('click', hideCard);
})();
