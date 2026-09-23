function paintQrTargets() {
    if (typeof QRCode === 'undefined') {
        return;
    }
    document.querySelectorAll('.qr-target[data-qr]').forEach((el) => {
        if (el.querySelector('canvas, img, table')) {
            return;
        }
        const size = Number(el.dataset.size) || 220;
        el.innerHTML = '';
        new QRCode(el, {
            text: el.dataset.qr,
            width: size,
            height: size,
            colorDark: '#0b3d2e',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M,
        });
        el.querySelectorAll('canvas, img').forEach((graphic) => {
            graphic.classList.add('qr-image');
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', paintQrTargets);
} else {
    paintQrTargets();
}

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-lightbox]');
    if (!trigger) {
        return;
    }
    event.preventDefault();
    const src = trigger.getAttribute('href') || trigger.getAttribute('src');
    if (!src) {
        return;
    }
    let overlay = document.getElementById('lightbox');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'lightbox';
        overlay.style.cssText = 'position:fixed;inset:0;background:#000c;display:grid;place-items:center;z-index:50;padding:20px;';
        overlay.innerHTML = '<img alt="" style="max-width:100%;max-height:90vh;border-radius:12px">';
        overlay.addEventListener('click', () => overlay.classList.add('d-none'));
        document.body.appendChild(overlay);
    }
    overlay.querySelector('img').src = src;
    overlay.classList.remove('d-none');
});
