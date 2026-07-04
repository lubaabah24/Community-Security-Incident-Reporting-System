document.addEventListener('DOMContentLoaded', () => {
    const dateLabel = document.querySelector('[data-current-date]');

    if (dateLabel) {
        const today = new Date();
        dateLabel.textContent = today.toLocaleDateString('en', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }
});
