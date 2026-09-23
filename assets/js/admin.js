document.querySelectorAll('.js-confirm').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm('Delete this item? This cannot be undone.')) {
            event.preventDefault();
        }
    });
});
