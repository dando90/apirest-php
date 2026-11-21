(function () {
    'use strict';

    const examples = {
        courses: {
            POST: '{\n  "name": "New course",\n  "places": 24,\n  "subjects": [1, 2]\n}',
            PUT: '{\n  "name": "Updated course",\n  "places": 18,\n  "subjects": [1]\n}'
        },
        subjects: {
            POST: '{\n  "name": "New subject"\n}',
            PUT: '{\n  "name": "Updated subject"\n}'
        }
    };
    const descriptions = {
        courses: {
            GET: 'List courses with optional filters, or provide an ID to retrieve one course.',
            POST: 'Create a course. Add subject IDs to link existing subjects to it.',
            PUT: 'Update a course and replace its linked subjects. Use an empty array to remove every subject link.',
            DELETE: 'Delete a course. Its relationship rows are removed by the database foreign key.'
        },
        subjects: {
            GET: 'List all subjects, or provide an ID to retrieve one subject.',
            POST: 'Create a subject that can be linked to courses.',
            PUT: 'Update the name of the subject with the selected ID.',
            DELETE: 'Delete a subject. Its course links are removed by the database foreign key.'
        }
    };
    const relevantHeaders = ['content-type', 'cache-control', 'content-length', 'date', 'allow'];
    const $ = (selector) => document.querySelector(selector);

    const methodSelect = $('#method-select');
    const resourceSelect = $('#resource-select');
    const recordId = $('#record-id');
    const requestBody = $('#request-body');
    const idField = $('#id-field');
    const filtersField = $('#filters-field');
    const bodyField = $('#body-field');
    const sendButton = $('#send-request');
    const responseState = $('#response-state');
    const placeholder = $('#response-placeholder');
    const resultPanel = $('#response-result');
    const responseBody = $('#response-body');
    const responseHeaders = $('#response-headers');
    const apiBase = document.body.dataset.apiBase;

    function currentResource() {
        return resourceSelect.value;
    }

    function currentMethod() {
        return methodSelect.value;
    }

    function isItemRequest() {
        return currentMethod() === 'GET' ? Boolean(recordId.value) : ['PUT', 'DELETE'].includes(currentMethod());
    }

    function routeForPreview() {
        const base = '/api/' + currentResource();
        if (isItemRequest()) return base + '/' + (recordId.value || ':id');
        return base;
    }

    function refreshView(resetBody) {
        const resource = currentResource();
        const method = currentMethod();
        const idSupported = method === 'GET' || method === 'PUT' || method === 'DELETE';
        const hasBody = method === 'POST' || method === 'PUT';
        const isCourseCollection = resource === 'courses' && method === 'GET' && !recordId.value;

        idField.classList.toggle('is-hidden', !idSupported);
        filtersField.classList.toggle('is-hidden', !isCourseCollection);
        bodyField.classList.toggle('is-hidden', !hasBody);
        $('#route-preview').textContent = routeForPreview();
        $('#route-kind').textContent = isItemRequest() ? 'ITEM' : 'COLLECTION';
        $('#endpoint-description').textContent = descriptions[resource][method];
        if (resetBody) $('#request-body').value = hasBody ? examples[resource][method] : '';
    }

    function setState(label, type) {
        responseState.className = 'response-state' + (type ? ' is-' + type : '');
        responseState.innerHTML = '<span class="state-dot"></span> ' + label;
    }

    function showToast(message) {
        const toast = $('#toast');
        toast.textContent = message;
        toast.classList.add('is-visible');
        window.setTimeout(() => toast.classList.remove('is-visible'), 2600);
    }

    function buildRequestUrl() {
        const path = currentResource();
        const url = new URL(apiBase + '/' + path, window.location.origin);
        if (isItemRequest()) {
            const id = recordId.value.trim();
            if (!/^\d+$/.test(id) || Number(id) < 1) throw new Error('Enter a valid positive integer record ID for this item request.');
            url.pathname += '/' + encodeURIComponent(id);
        }

        if (currentMethod() === 'GET' && currentResource() === 'courses' && !isItemRequest()) {
            const filters = [
                ['name', $('#filter-name').value.trim()],
                ['subjects', $('#filter-subjects').value.trim()],
                ['places', $('#filter-places').value.trim()]
            ];
            filters.forEach(([key, value]) => { if (value !== '') url.searchParams.set(key, value); });
        }
        return url;
    }

    function showHeaders(headers) {
        responseHeaders.replaceChildren();
        const headerList = document.createElement('dl');
        headerList.className = 'header-list';
        relevantHeaders.forEach((key) => {
            const value = headers.get(key);
            if (value === null) return;
            const row = document.createElement('div');
            row.className = 'header-row';
            const name = document.createElement('dt');
            const content = document.createElement('dd');
            name.textContent = key;
            content.textContent = value;
            row.append(name, content);
            headerList.append(row);
        });
        responseHeaders.append(headerList);
        $('#header-count').textContent = String(headerList.children.length);
    }

    function renderResponse(url, method, status, elapsed, body, headers, contentType, networkError) {
        placeholder.classList.add('is-hidden');
        resultPanel.classList.remove('is-hidden');
        const statusText = networkError ? 'Network error' : String(status);
        $('#response-status').textContent = statusText;
        $('#response-status').className = networkError ? 'status-bad' : (status >= 200 && status < 300 ? 'status-good' : 'status-bad');
        $('#response-method').textContent = method;
        $('#response-time').textContent = Math.round(elapsed) + ' ms';
        $('#response-url').textContent = url;
        $('#response-url').title = url;
        $('#response-content-type').textContent = contentType && contentType.includes('json') ? 'JSON' : 'TEXT';
        responseBody.textContent = typeof body === 'string' ? body : JSON.stringify(body, null, 2);
        showHeaders(headers);
        setState(networkError ? 'NETWORK ERROR' : (status >= 200 && status < 400 ? 'COMPLETE' : 'HTTP ERROR'), networkError ? 'error' : (status >= 200 && status < 400 ? 'success' : 'error'));
    }

    async function sendRequest() {
        let url;
        let parsedBody;
        try {
            url = buildRequestUrl();
            if (['POST', 'PUT'].includes(currentMethod())) {
                parsedBody = JSON.parse(requestBody.value);
                if (!parsedBody || typeof parsedBody !== 'object' || Array.isArray(parsedBody)) {
                    throw new Error('The request body must be a JSON object.');
                }
            }
        } catch (error) {
            showToast(error.message);
            return;
        }

        sendButton.disabled = true;
        sendButton.classList.add('is-loading');
        sendButton.querySelector('.send-label').textContent = 'Sending…';
        setState('SENDING', 'loading');
        $('#response-url').textContent = url.href;
        const requestStarted = performance.now();
        const requestMethod = currentMethod();

        try {
            const result = await PlaygroundApi.sendRequest(url.href, requestMethod, parsedBody === undefined ? undefined : JSON.stringify(parsedBody));
            renderResponse(
                url.href,
                requestMethod,
                result.response.status,
                result.elapsed,
                result.body,
                result.response.headers,
                result.response.headers.get('content-type') || '',
                false
            );
        } catch (error) {
            const headers = new Headers();
            renderResponse(url.href, requestMethod, 0, performance.now() - requestStarted, error.message || 'The server could not be reached.', headers, 'text/plain', true);
        } finally {
            sendButton.disabled = false;
            sendButton.classList.remove('is-loading');
            sendButton.querySelector('.send-label').textContent = 'Send request';
        }
    }

    function setResponseTab(tab) {
        const showingHeaders = tab === 'headers';
        responseHeaders.classList.toggle('is-hidden', !showingHeaders);
        responseBody.classList.toggle('is-hidden', showingHeaders);
        document.querySelectorAll('.response-tabs button[data-tab]').forEach((button) => {
            button.classList.toggle('tab-active', button.dataset.tab === tab);
        });
    }

    methodSelect.addEventListener('change', () => refreshView(true));
    resourceSelect.addEventListener('change', () => {
        document.querySelectorAll('[data-resource-nav]').forEach((button) => button.classList.toggle('is-active', button.dataset.resourceNav === currentResource()));
        refreshView(true);
    });
    recordId.addEventListener('input', refreshView);
    $('#reset-example').addEventListener('click', () => { requestBody.value = examples[currentResource()][currentMethod()]; });
    sendButton.addEventListener('click', sendRequest);
    document.querySelectorAll('[data-resource-nav]').forEach((button) => {
        button.addEventListener('click', () => {
            resourceSelect.value = button.dataset.resourceNav;
            resourceSelect.dispatchEvent(new Event('change'));
        });
    });
    document.querySelectorAll('.response-tabs button[data-tab]').forEach((button) => button.addEventListener('click', () => setResponseTab(button.dataset.tab)));

    refreshView(true);
}());
