document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('dossiers-container');
    const baseUrl = container.dataset.url;
    const buttons = document.querySelectorAll('.page-btn');

    buttons.forEach(btn => {
        btn.addEventListener('click', async () => {
            const perPage = btn.dataset.size;
            const url = baseUrl.includes('?')
                ? `${baseUrl}&perPage=${perPage}`
                : `${baseUrl}?perPage=${perPage}`;

            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });

            if (response.ok) {
                container.innerHTML = await response.text();
            } else {
                console.error('Erreur chargement pagination');
            }
        });
    });

    container.addEventListener('click', async (e) => {
        const link = e.target.closest('a.page-link');
        if (!link) return;

        e.preventDefault();
        const response = await fetch(link.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        });
        container.innerHTML = await response.text();
    });
});
