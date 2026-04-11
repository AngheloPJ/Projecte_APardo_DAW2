function previewAvatar(input) {
    const fileNameDiv = document.getElementById('file-name');
    const avatarImg = document.getElementById('avatar-img');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        fileNameDiv.textContent = file.name;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            avatarImg.src = e.target.result;
        }
        reader.readAsDataURL(file);
        
        const removeFlag = document.getElementById('remove-avatar-flag');
        if (removeFlag) removeFlag.value = '0';
    }
}

function removeAvatar() {
    if (confirm('¿Seguro que quieres eliminar tu avatar?')) {
        document.getElementById('remove-avatar-flag').value = '1';
        document.getElementById('avatar-img').src = document.getElementById('default-avatar-url').value;
        
        const avatarInput = document.getElementById('avatar');
        avatarInput.value = '';
        document.getElementById('file-name').textContent = '';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const generateApiKeyBtn = document.getElementById('generate-api-key-btn');

    if (generateApiKeyBtn) {
        generateApiKeyBtn.addEventListener('click', async () => {
            const statusEl = document.getElementById('api-key-status');
            const valueEl = document.getElementById('api-key-value');
            const apiKeyUrl = generateApiKeyBtn.dataset.url;

            statusEl.textContent = 'Generando API KEY...';
            valueEl.textContent = '';

            try {
                const response = await fetch(apiKeyUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    statusEl.textContent = data.error || 'No se pudo generar la API key.';
                    return;
                }

                statusEl.textContent = 'Copia la API KEY porque no la podrás ver nuevamente.';
                valueEl.textContent = data.api_key || '';
            } catch (error) {
                statusEl.textContent = 'Error de red al generar la API key.';
            }
        });
    }
});
