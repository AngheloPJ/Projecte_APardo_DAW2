"use strict";

const steamPanel = document.querySelector('.steam-import');
const appIdInput = document.getElementById('steam-appid');
const gameSelect = document.getElementById('steam-game-select');
const customAppIdWrap = document.getElementById('steam-custom-appid-wrap');

const countInput = document.getElementById('steam-count');
const loadButton = document.getElementById('steam-load-btn');
const statusElement = document.getElementById('steam-status');
const resultsElement = document.getElementById('steam-results');

const titleInput = document.getElementById('titol');
const contentInput = document.getElementById('cos');
const steamImageInput = document.getElementById('steam-image-url');
const steamSourceInput = document.getElementById('steam-source-url');
const formPreview = document.getElementById('steam-form-preview');
const formPreviewImage = document.getElementById('steam-form-preview-image');
const changeNewsButton = document.getElementById('steam-change-btn');

const baseUrl = steamPanel ? (steamPanel.dataset.baseUrl || '/') : '/';

function setStatus(message, isError) {
    statusElement.textContent = message;
    statusElement.classList.toggle('is-error', Boolean(isError));
    statusElement.classList.toggle('is-success', !isError);
}

async function fetchRequest(url, method, headers, params) {
    const response = await fetch(url, {
        method,
        headers,
        body: params ? JSON.stringify(params) : undefined,
        credentials: 'same-origin',
    });

    const raw = await response.text();
    let data = {};

    if (raw) {
        try {
            data = JSON.parse(raw);
        } catch (error) {
            throw new Error('La respuesta no es JSON valido.');
        }
    }

    if (!response.ok) {
        const error = new Error(data.error || ('Error HTTP ' + response.status));
        error.response = data;
        error.status = response.status;
        throw error;
    }

    return data;
}

function normalizeItems(data) {
    if (Array.isArray(data)) return data;
    if (data && Array.isArray(data.items)) return data.items;
    if (data && Array.isArray(data.articles)) return data.articles;
    return [];
}

function showSteamPanel() {
    if (steamPanel) {
        steamPanel.classList.remove('is-hidden');
    }
}

function updateAppIdFromSelector() {
    if (!gameSelect || !appIdInput || !customAppIdWrap) {
        return;
    }

    if (gameSelect.value === 'custom') {
        customAppIdWrap.classList.remove('is-hidden');
        return;
    }

    customAppIdWrap.classList.add('is-hidden');
    appIdInput.value = gameSelect.value;
}

function hideSteamPanel() {
    if (steamPanel) {
        steamPanel.classList.add('is-hidden');
    }
}

function setFormPreview(imageUrl) {
    if (!formPreview || !formPreviewImage || !steamImageInput) {
        return;
    }

    if (!imageUrl) {
        steamImageInput.value = '';
        formPreviewImage.src = '';
        formPreview.classList.add('is-hidden');
        return;
    }

    steamImageInput.value = imageUrl;
    formPreviewImage.src = imageUrl;
    formPreview.classList.remove('is-hidden');
}

function useInForm(item) {
    titleInput.value = (item.title || '').slice(0, 150);

    const summary = item.summary ? String(item.summary) : '';
    const source = item.url ? ('\n\nFuente: ' + item.url) : '';
    contentInput.value = (summary + source).trim();

    titleInput.dispatchEvent(new Event('input', { bubbles: true }));
    contentInput.dispatchEvent(new Event('input', { bubbles: true }));

    setFormPreview(item.image_url || '');
    if (steamSourceInput) {
        steamSourceInput.value = item.url || '';
    }
    hideSteamPanel();

    setStatus('Noticia copiada al formulario.', false);
}

async function publishDirectly(item) {
    setStatus('Publicando noticia...', false);

    const payload = {
        title: item.title || 'Steam news',
        summary: item.summary || '',
        url: item.url || '',
        image_url: item.image_url || '',
        appid: parseInt(appIdInput.value, 10) || 0,
    };

    const data = await fetchRequest(
        baseUrl + 'api/steam/news/publish',
        'POST',
        {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        payload
    );

    setStatus('Noticia publicada con exito (ID ' + data.article_id + '). Redirigiendo...', false);
    setTimeout(function () {
        window.location.href = baseUrl + 'my-articles';
    }, 1200);
}

function createImage(item) {
    const image = document.createElement('img');
    image.className = 'steam-card-image';
    image.alt = item.title || 'Imagen de la noticia';
    image.loading = 'lazy';
    image.src = item.image_url || '';
    image.addEventListener('error', function () {
        image.src = baseUrl + 'public/assets/img/articles/NoImgDisplay.webp';
    });

    return image;
}

function createCard(item) {
    const card = document.createElement('article');
    card.className = 'steam-card';

    const title = document.createElement('h4');
    title.textContent = item.title || 'Sin titulo';

    const summary = document.createElement('p');
    summary.textContent = item.summary || '';

    const date = document.createElement('small');
    date.textContent = item.published_at || '';

    const actions = document.createElement('div');
    actions.className = 'steam-card-actions';

    const useButton = document.createElement('button');
    useButton.type = 'button';
    useButton.className = 'steam-use-btn';
    useButton.textContent = 'Usar en formulario';
    useButton.addEventListener('click', function () {
        useInForm(item);
    });

    const publishButton = document.createElement('button');
    publishButton.type = 'button';
    publishButton.className = 'steam-publish-btn';
    publishButton.textContent = 'Publicar directo';
    publishButton.addEventListener('click', async function () {
        publishButton.disabled = true;
        try {
            await publishDirectly(item);
            publishButton.textContent = 'Publicado';
        } catch (error) {
            if (error.status === 409 && error.response && error.response.existing) {
                publishButton.textContent = 'Ya publicado';
                setStatus('Error: Esta noticia ya está publicada.', false);
                return;
            }

            setStatus(error.message || 'No se pudo publicar la noticia.', true);
            publishButton.disabled = false;
        }
    });

    actions.appendChild(useButton);
    actions.appendChild(publishButton);

    card.appendChild(createImage(item));
    card.appendChild(title);
    card.appendChild(summary);
    card.appendChild(date);
    card.appendChild(actions);

    return card;
}

function renderItems(items) {
    resultsElement.innerHTML = '';

    if (!items.length) {
        setStatus('No se encontraron noticias para ese AppID.', false);
        return;
    }

    const fragment = document.createDocumentFragment();
    items.forEach(function (item) {
        fragment.appendChild(createCard(item));
    });

    resultsElement.appendChild(fragment);
    setStatus('Noticias cargadas: ' + items.length, false);
}

async function loadArticles() {
    const appId = parseInt(appIdInput.value, 10) || 0;
    const count = parseInt(countInput.value, 10) || 5;

    if (appId <= 0) {
        setStatus('El AppID debe ser un numero mayor que 0.', true);
        return;
    }

    loadButton.disabled = true;
    setStatus('Cargando noticias desde Steam...', false);
    resultsElement.innerHTML = '';

    try {
        const query = new URLSearchParams({
            appid: String(appId),
            count: String(count),
        });

        const data = await fetchRequest(
            baseUrl + 'api/steam/news?' + query.toString(),
            'GET',
            { 'Accept': 'application/json' }
        );
        renderItems(normalizeItems(data));
    } catch (error) {
        setStatus(error.message, true);
    } finally {
        loadButton.disabled = false;
    }
}

function initSteamImport() {
    if (!steamPanel || !loadButton || !statusElement || !resultsElement || !appIdInput || !countInput) {
        return;
    }

    if (gameSelect) {
        gameSelect.addEventListener('change', updateAppIdFromSelector);
        updateAppIdFromSelector();
    }

    loadButton.addEventListener('click', loadArticles);

    if (changeNewsButton) {
        changeNewsButton.addEventListener('click', function () {
            showSteamPanel();
            if (steamSourceInput) {
                steamSourceInput.value = '';
            }
            setStatus('Selecciona otra noticia de Steam.', false);
        });
    }
}

document.addEventListener('DOMContentLoaded', initSteamImport);
