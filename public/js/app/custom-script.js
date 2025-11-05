document.addEventListener('alpine:init', () => {
    const form = document.getElementById('form');
    if (form) {
        form.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        });
    }
});
