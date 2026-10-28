(() => {
    function start() {
        const modalElement = document.getElementById('workerAllocationModal');
        const form = document.getElementById('workerAllocationForm');
        if (!modalElement || !form) return;
        document.body.appendChild(modalElement);
        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        const checkboxes = Array.from(document.querySelectorAll('.worker-select:not(:disabled)'));
        const selectAll = document.getElementById('selectPageWorkers');
        const bulkButton = document.getElementById('bulkAllocateWorkers');
        const clearButton = document.getElementById('clearWorkerSelection');
        const confirmButton = document.getElementById('confirmWorkerAllocation');
        const error = document.getElementById('allocationError');
        let currentWorkers = [];
        let trigger = null;
        let saving = false;

        function updateSelection() {
            const count = checkboxes.filter(box => box.checked).length;
            document.getElementById('workerSelectionCount').textContent = `${count} selected`;
            selectAll.checked = count > 0 && count === checkboxes.length;
            selectAll.indeterminate = count > 0 && count < checkboxes.length;
            selectAll.disabled = checkboxes.length === 0;
            bulkButton.disabled = count === 0;
            clearButton.disabled = count === 0;
        }

        function updateAction() {
            const transfer = form.elements.mode.value === 'transfer';
            document.getElementById('allocationTransferNotice').hidden = !transfer;
            confirmButton.textContent = `${transfer ? 'Transfer' : 'Assign'} ${currentWorkers.length} worker${currentWorkers.length === 1 ? '' : 's'}`;
        }

        function open(workers, button) {
            currentWorkers = workers;
            trigger = button;
            form.reset();
            error.hidden = true;
            const list = document.getElementById('allocationWorkerList');
            const ids = document.getElementById('allocationWorkerIds');
            list.replaceChildren();
            ids.replaceChildren();
            document.getElementById('allocationWorkerCount').textContent = `${workers.length} worker${workers.length === 1 ? '' : 's'} to allocate`;
            workers.forEach(worker => {
                const item = document.createElement('li');
                item.className = 'list-group-item px-0';
                const name = document.createElement('strong');
                name.textContent = worker.dataset.workerName;
                const projects = document.createElement('div');
                projects.className = 'small text-muted';
                projects.textContent = `Current projects: ${worker.dataset.workerProjects}`;
                item.append(name, projects);
                list.appendChild(item);
                const id = document.createElement('input');
                id.type = 'hidden';
                id.name = 'worker_ids[]';
                id.value = worker.value;
                ids.appendChild(id);
            });
            updateAction();
            modal.show();
        }

        checkboxes.forEach(box => box.addEventListener('change', updateSelection));
        selectAll.addEventListener('change', () => {
            checkboxes.forEach(box => { box.checked = selectAll.checked; });
            updateSelection();
        });
        clearButton.addEventListener('click', () => {
            checkboxes.forEach(box => { box.checked = false; });
            updateSelection();
        });
        bulkButton.addEventListener('click', () => open(checkboxes.filter(box => box.checked), bulkButton));
        document.querySelectorAll('.allocate-worker').forEach(button => button.addEventListener('click', () => {
            const worker = checkboxes.find(box => box.value === button.dataset.workerId);
            if (worker) open([worker], button);
        }));
        form.querySelectorAll('[name="mode"]').forEach(radio => radio.addEventListener('change', updateAction));
        modalElement.addEventListener('hide.bs.modal', event => { if (saving) event.preventDefault(); });
        modalElement.addEventListener('hidden.bs.modal', () => { if (trigger?.isConnected) trigger.focus(); });
        form.addEventListener('submit', async event => {
            event.preventDefault();
            if (saving) return;
            error.hidden = true;
            if (!form.elements.project_id.value) {
                error.textContent = 'Choose a destination project.';
                error.hidden = false;
                error.focus();
                return;
            }
            saving = true;
            confirmButton.disabled = true;
            confirmButton.textContent = 'Saving…';
            form.setAttribute('aria-busy', 'true');
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(form),
                });
                const payload = await response.json().catch(() => ({}));
                if (!response.ok || !payload.success) {
                    throw new Error(Object.values(payload.errors || {}).flat()[0] || payload.message || 'Unable to save allocations. Please try again.');
                }
                window.location.reload();
            } catch (failure) {
                error.textContent = failure.message;
                error.hidden = false;
                error.focus();
                saving = false;
                confirmButton.disabled = false;
                form.removeAttribute('aria-busy');
                updateAction();
            }
        });
        updateSelection();
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
})();
