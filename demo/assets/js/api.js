(function (global) {
    'use strict';

    async function sendRequest(url, method, body) {
        const options = { method: method, headers: { Accept: 'application/json' } };
        if (body !== undefined) {
            options.headers['Content-Type'] = 'application/json';
            options.body = body;
        }

        const startedAt = performance.now();
        const response = await fetch(url, options);
        const text = await response.text();
        const elapsed = performance.now() - startedAt;
        let responseBody = text;
        try {
            responseBody = text ? JSON.parse(text) : null;
        } catch (error) {
            // Keep non-JSON server output visible for debugging HTTP errors.
        }

        return {
            response: response,
            elapsed: elapsed,
            body: responseBody,
            rawBody: text
        };
    }

    global.PlaygroundApi = { sendRequest: sendRequest };
}(window));
