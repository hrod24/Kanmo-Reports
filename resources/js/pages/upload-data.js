document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('upload-form'),
        button = document.getElementById('import-button');
    const inputs = [...document.querySelectorAll('.upload-input')],
        list = document.getElementById('progress-items');
    const resume = document.getElementById('resume-button'),
        notice = document.getElementById('progress-warning');
    const csrf = form.querySelector('[name="_token"]').value,
        key = 'attendance_batch_active_ids';
    const startUrl = form.action;
    const processTemplate = form.dataset.processUrl;
    const statusTemplate = form.dataset.statusUrl;
    let jobs = [],
        busy = false;
    const url = (tpl, id) => tpl.replace('_ID_', id);

    function showFile(input) {
        const field = input.dataset.field,
            info = document.querySelector(`[data-info="${field}"]`);
        const label = document.querySelector(`[data-filename="${field}"]`),
            file = input.files[0];
        label.textContent = file ? `${file.name} (${(file.size/1048576).toFixed(2)} MB)` : '';
        info.classList.toggle('hidden', !file);
        info.classList.toggle('flex', !!file);
    }

    function valid(file) {
        if (!/\.(xlsx|xls)$/i.test(file.name)) {
            alert('Gunakan .xlsx atau .xls');
            return false;
        }
        if (file.size > 20 * 1048576) {
            alert('Maksimal 20 MB per file');
            return false;
        }
        return true;
    }
    inputs.forEach(i => i.addEventListener('change', () => {
        if (i.files[0] && !valid(i.files[0])) i.value = '';
        showFile(i);
    }));
    document.querySelectorAll('.drop-zone').forEach(zone => {
        const input = zone.querySelector('input[type=file]');
        for (const n of ['dragenter', 'dragover']) zone.addEventListener(n, e => {
            e.preventDefault();
            zone.classList.add('border-blue-500', 'bg-blue-50');
        });
        for (const n of ['dragleave', 'drop']) zone.addEventListener(n, e => {
            e.preventDefault();
            if (n === 'dragleave' && zone.contains(e.relatedTarget)) return;
            zone.classList.remove('border-blue-500', 'bg-blue-50');
        });
        zone.addEventListener('drop', e => {
            if (e.dataTransfer.files.length !== 1) {
                alert('Satu file per kotak');
                return;
            }
            const f = e.dataTransfer.files[0];
            if (!valid(f)) return;
            const dt = new DataTransfer();
            dt.items.add(f);
            input.files = dt.files;
            showFile(input);
        });
    });
    document.querySelectorAll('.remove-file').forEach(b => b.addEventListener('click', () => {
        const input = document.querySelector(`.upload-input[data-field="${b.dataset.remove}"]`);
        input.value = '';
        showFile(input);
    }));
    document.addEventListener('dragover', e => {
        if (e.dataTransfer?.types.includes('Files')) e.preventDefault();
    });
    document.addEventListener('drop', e => {
        if (e.dataTransfer?.files.length) e.preventDefault();
    });
    async function api(url, opts = {}) {
        const res = await fetch(url, {
            credentials: 'same-origin',
            ...opts,
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
                ...(opts.headers || {})
            }
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw Error(data.message || `HTTP ${res.status}`);
        return data;
    }

    function render() {
        list.replaceChildren();
        if (!jobs.length) {
            const p = document.createElement('p');
            p.className = 'text-sm text-slate-400';
            p.textContent = 'Belum ada import aktif.';
            list.append(p);
        }
        for (const j of jobs) {
            const box = document.createElement('div');
            box.className = 'rounded-lg border border-slate-200 p-3';
            const head = document.createElement('div');
            head.className = 'mb-2 flex justify-between gap-3 text-sm';
            const name = document.createElement('span');
            name.className = 'break-all font-medium';
            name.textContent = `${j.report_type}: ${j.original_filename}`;
            const status = document.createElement('span');
            status.className = 'shrink-0 text-xs ' + (j.status === 'failed' ? 'text-rose-600' : j.status ===
                'success' ? 'text-emerald-600' : 'text-blue-600');
            status.textContent = `${j.status} · ${j.percent}%`;
            head.append(name, status);
            box.append(head);
            const track = document.createElement('div');
            track.className = 'h-2 rounded-full bg-slate-100 overflow-hidden';
            const fill = document.createElement('div');
            fill.className = 'h-full ' + (j.status === 'failed' ? 'bg-rose-500' : j.status === 'success' ?
                'bg-emerald-500' : 'bg-blue-600');
            fill.style.width = j.percent + '%';
            track.append(fill);
            box.append(track);
            if (j.error_message) {
                const p = document.createElement('p');
                p.className = 'mt-2 text-xs text-rose-600';
                p.textContent = j.error_message;
                box.append(p);
            }
            list.append(box);
        }
        sessionStorage.setItem(key, JSON.stringify(jobs.filter(j => !['success', 'failed'].includes(j
            .status)).map(j => j.id)));
        resume.classList.toggle('hidden', busy || !jobs.some(j => !['success', 'failed'].includes(j
            .status)));
    }
    async function run() {
        if (busy) return;
        busy = true;
        button.disabled = true;
        notice.classList.add('hidden');
        render();
        try {
            for (let i = 0; i < jobs.length; i++) {
                while (!['success', 'failed'].includes(jobs[i].status)) {
                    jobs[i] = await api(url(processTemplate, jobs[i].id), {
                        method: 'POST'
                    });
                    render();
                }
            }
        } catch (e) {
            notice.textContent = `Terhenti: ${e.message}. Klik Resume untuk melanjutkan.`;
            notice.classList.remove('hidden');
        } finally {
            busy = false;
            button.disabled = false;
            button.textContent = 'Import Data';
            render();
            if (jobs.length && jobs.every(j => ['success', 'failed'].includes(j.status))) setTimeout(
                () => location.reload(), 1200);
        }
    }
    resume.addEventListener('click', run);
    form.addEventListener('submit', async function(event) {
        event.preventDefault();

        if (busy) return;

        const selected = inputs.some(input => input.files.length > 0);

        if (!selected) {
            alert('Pilih minimal satu file Excel.');
            return;
        }

        busy = true;
        button.disabled = true;
        button.textContent = 'Uploading...';

        notice.classList.add('hidden');

        try {
            const result = await api(startUrl, {
                method: 'POST',
                body: new FormData(form),
            });

            if (!Array.isArray(result.jobs) || !result.jobs.length) {
                throw new Error('Server tidak mengembalikan data import.');
            }

            // Simpan pekerjaan baru ke daftar progress
            jobs = result.jobs;

            // Simpan ID agar dapat dilanjutkan setelah halaman di-refresh
            sessionStorage.setItem(
                key,
                JSON.stringify(jobs.map(job => job.id))
            );

            render();

            // Lepaskan status busy agar run() dapat dimulai
            busy = false;

            // Jalankan pemrosesan 200 baris per request
            await run();

        } catch (error) {
            console.error('Upload error:', error);

            notice.textContent = 'Upload gagal: ' + error.message;
            notice.classList.remove('hidden');

        } finally {
            busy = false;
            button.disabled = false;
            button.textContent = 'Import Data';
        }
    });
    (async () => {
        try {
            const ids = JSON.parse(sessionStorage.getItem(key) || '[]');
            if (!Array.isArray(ids) || !ids.length) return;
            jobs = await Promise.all(ids.map(id => api(url(statusTemplate, id))));
            render();
        } catch (e) {
            sessionStorage.removeItem(key);
        }
    })();
});
