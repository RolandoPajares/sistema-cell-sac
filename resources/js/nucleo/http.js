const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

export async function request(url, options = {}) {
    const headers = new Headers(options.headers ?? {});

    headers.set('Accept', 'application/json');

    if (!headers.has('X-CSRF-TOKEN')) {
        headers.set('X-CSRF-TOKEN', csrfToken());
    }

    const response = await fetch(url, { ...options, headers });
    const contentType = response.headers.get('content-type') ?? '';
    const body = contentType.includes('application/json') ? await response.json() : await response.text();

    if (!response.ok) {
        const message = typeof body === 'object' && body?.message ? body.message : `HTTP ${response.status}`;
        throw new Error(message);
    }

    return body;
}

export const get = (url, options = {}) => request(url, { ...options, method: 'GET' });

export const post = (url, body, options = {}) => request(url, {
    ...options,
    method: 'POST',
    body: body instanceof FormData ? body : JSON.stringify(body),
    headers: {
        'Content-Type': 'application/json',
        ...(options.headers ?? {}),
    },
});
