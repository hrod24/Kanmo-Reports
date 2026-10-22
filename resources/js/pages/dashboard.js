
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('absent-modal');

    if (!modal) return;

    const title = document.getElementById('absent-modal-title');
    const subtitle = document.getElementById('absent-modal-subtitle');
    const tbody = document.getElementById('absent-modal-rows');

    document.querySelectorAll('.business-unit-card')
        .forEach(card => {
            card.addEventListener('click', () => {
                const unit = card.dataset.unit;

                let employees = [];

                try {
                    employees = JSON.parse(
                        card.dataset.employees || '[]'
                    );
                } catch (error) {
                    console.error('Invalid employee data', error);
                }

                title.textContent = `Absent Employees — ${unit}`;

                subtitle.textContent =
                    `${employees.length} absent employees`;

                tbody.replaceChildren();

                if (employees.length === 0) {
                    const row = tbody.insertRow();
                    const cell = row.insertCell();

                    cell.colSpan = 5;
                    cell.className =
                        'px-5 py-10 text-center text-slate-500';
                    cell.textContent =
                        'No absent employees for this business unit.';
                }

                employees.forEach((employee, index) => {
                    const row = tbody.insertRow();
                    row.className = 'hover:bg-slate-50';

                    const values = [
                        index + 1,
                        employee.employee_id ?? '-',
                        employee.display_name ?? '-',
                        employee.department ?? '-',
                        'Absent',
                    ];

                    values.forEach((value, i) => {
                        const cell = row.insertCell();
                        cell.className =
                            'whitespace-nowrap px-5 py-3 text-slate-700';

                        cell.textContent = value;

                        if (i === 4) {
                            cell.className +=
                                ' font-semibold text-rose-600';
                        }
                    });
                });

                modal.showModal();
            });
        });

    document.getElementById('close-absent-modal')
        ?.addEventListener('click', () => modal.close());

    document.getElementById('close-absent-modal-footer')
        ?.addEventListener('click', () => modal.close());

    modal.addEventListener('click', event => {
        if (event.target === modal) {
            modal.close();
        }
    });
});
