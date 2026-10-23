document.addEventListener('click', event => {
    if (!(event.target instanceof Element)) return;
    const link = event.target.closest('[data-teams-reminder]');
    if (!link) return;

    link.textContent = 'Opened';
    const row = link.closest('[data-employee-row]');
    const tbody = row?.parentElement;
    if (!tbody) return;
    row.classList.add('bg-slate-50');
    tbody.appendChild(row);
    const first = Number(tbody.dataset.firstItem || 1);
    tbody.querySelectorAll('[data-row-number]').forEach((cell, index) => {
        cell.textContent = first + index;
    });
});
