const form = document.getElementById('search-form');
const input = document.getElementById('reserca');
const resultsBox = document.getElementById('search-results');

if (form && input && resultsBox) {
    const ajaxUrl = form.dataset.ajaxUrl;
    const searchUrl = form.dataset.searchUrl;

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function hideResults() {
        resultsBox.classList.add('is-hidden');
        resultsBox.innerHTML = '';
    }

    function showResults(html) {
        resultsBox.innerHTML = html;
        resultsBox.classList.remove('is-hidden');
    }

    async function fetchArticles(keyword) {
        const response = await fetch(`${ajaxUrl}?keyword=${encodeURIComponent(keyword)}`);

        if (!response.ok) {
            throw new Error('Error en la petición');
        }

        return response.json();
    }

    function renderResults(items, keyword) {
        if (!items.length) {
            showResults(`<div class="search-result-empty">No hay resultados para "${escapeHtml(keyword)}".</div>`);
            return;
        }

        const html = items.map((item) => {
            const title = escapeHtml(item.title || 'Sin título');
            const excerpt = escapeHtml(item.excerpt || '');
            const href = `${searchUrl}?keyword=${encodeURIComponent(item.title || keyword)}`;

            return `
                <a class="search-result-item" href="${href}">
                    <span class="search-result-title">${title}</span>
                    <span class="search-result-excerpt">${excerpt}</span>
                </a>
            `;
        }).join('');

        showResults(html);
    }

    input.addEventListener('input', async () => {
        const keyword = input.value.trim();

        if (keyword.length < 2) {
            hideResults();
            return;
        }

        try {
            const res = await fetchArticles(keyword);
            renderResults(res.items || [], keyword);
        } catch (error) {
            showResults('<div class="search-result-error">Ha fallado la petición</div>');
        }
    });

    document.addEventListener('click', (event) => {
        if (!form.contains(event.target)) {
            hideResults();
        }
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            hideResults();
        }
    });
}
