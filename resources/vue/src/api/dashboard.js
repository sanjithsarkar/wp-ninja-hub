const config = window.wpNinjaHub || {};

async function apiFetch(endpoint) {
    const response = await fetch(config.restUrl + endpoint, {
        headers: {
            'X-WP-Nonce': config.nonce,
        },
    });

    if (!response.ok) {
        throw new Error(`API error: ${response.status}`);
    }

    return response.json();
}

export function getMenu() {
    return apiFetch('menu');
}

export function getProductData(slug) {
    return apiFetch('data/' + slug);
}
