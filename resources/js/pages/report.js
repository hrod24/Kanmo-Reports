document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('report-filters');
    if (!form) return;
    const table = document.getElementById('report-results');
    const count = document.getElementById('filtered-count');
    const download = document.getElementById('download-report');
    const loading = document.getElementById('report-loading');
    const error = document.getElementById('report-error');
    const pageUrl = form.action;
    const exportUrl = form.dataset.exportUrl;
    let currentSort = form.dataset.sort;
    let currentDirection = form.dataset.direction;
    let activeRequest = null;
    let debounce = null;
    let sequence = 0;

    function parameters() {
        const params = new URLSearchParams(new FormData(form));
        // Carry over chosen sorting even if there is no visible sort input.
        params.set('sort', currentSort);
        params.set('direction', currentDirection);
        return params;
    }

    function downloadUrl(params) {
        const p = new URLSearchParams(params);
        p.delete('page');
        p.delete('per_page');
        return exportUrl + '?' + p.toString();
    }

    async function load(url, historyMode = 'push') {
        if (activeRequest) activeRequest.abort();
        const controller = new AbortController();
        activeRequest = controller;
        const token = ++sequence;
        loading.classList.remove('hidden');
        error.classList.add('hidden');
        try {
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                signal: controller.signal,
            });
            const data = await response.json();
            if (!response.ok) {
                const details = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
                throw new Error(details || `HTTP ${response.status}`);
            }
            if (token !== sequence) return;
            table.innerHTML = data.html;
            count.textContent = Number(data.total).toLocaleString('en-US');
            download.href = downloadUrl(new URL(url).searchParams);
            if (historyMode === 'push') history.pushState({}, '', url);
            if (historyMode === 'replace') history.replaceState({}, '', url);
        } catch (e) {
            if (e.name !== 'AbortError') {
                error.textContent = 'Tidak dapat memuat report: ' + e.message;
                error.classList.remove('hidden');
            }
        } finally {
            if (token === sequence) {
                loading.classList.add('hidden');
                activeRequest = null;
            }
        }
    }

    function filterNow() {
        const params = parameters();
        params.delete('page');
        load(pageUrl + '?' + params.toString(), 'replace');
    }

    function scheduleSearch() {
        clearTimeout(debounce);
        debounce = setTimeout(filterNow, 350);
    }

    form.addEventListener('submit', event => {
        event.preventDefault();
        clearTimeout(debounce);
        filterNow();
    });
    form.querySelector('[name="search"]').addEventListener('input', scheduleSearch);
    for (const name of ['from', 'to', 'status', 'per_page']) {
        form.querySelector(`[name="${name}"]`)?.addEventListener('change', () => {
            clearTimeout(debounce);
            filterNow();
        });
    }
    table.addEventListener('click', event => {
        const link = event.target.closest('a[href]');
        if (!link || !table.contains(link)) return;
        event.preventDefault();
        const next = new URL(link.href);
        currentSort = next.searchParams.get('sort') || currentSort;
        currentDirection = next.searchParams.get('direction') || currentDirection;
        load(next.href);
    });
    document.getElementById('reset-report').addEventListener('click', event => {
        event.preventDefault();
        form.reset();
        form.querySelector('[name="search"]').value = '';
        form.querySelector('[name="from"]').value = '';
        form.querySelector('[name="to"]').value = '';
        const status = form.querySelector('[name="status"]');
        if (status) status.value = '';
        form.querySelector('[name="per_page"]').value = '25';
        currentSort = 'id';
        currentDirection = 'desc';
        filterNow();
    });
    window.addEventListener('popstate', () => {
        // Full navigation on Back/Forward keeps inputs and results synchronized.
        location.reload();
    });
});
