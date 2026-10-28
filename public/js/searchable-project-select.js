(() => {
    let nextId = 0;

    function enhance(select) {
        if (select.dataset.projectPickerReady) return;
        select.dataset.projectPickerReady = 'true';
        const wrapper = document.createElement('div');
        wrapper.className = 'project-picker';
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'form-control form-control-sm project-picker-input';
        input.placeholder = 'Search projects...';
        input.autocomplete = 'off';
        input.setAttribute('role', 'combobox');
        input.setAttribute('aria-autocomplete', 'list');
        input.setAttribute('aria-expanded', 'false');
        input.setAttribute('aria-label', select.getAttribute('aria-label') || 'Search and select a project');
        const icon = document.createElement('i');
        icon.className = 'bi bi-search project-picker-icon';
        icon.setAttribute('aria-hidden', 'true');
        const menu = document.createElement('div');
        menu.className = 'project-picker-menu';
        menu.hidden = true;
        const list = document.createElement('div');
        list.id = `project-picker-${++nextId}`;
        list.setAttribute('role', 'listbox');
        input.setAttribute('aria-controls', list.id);
        const status = document.createElement('div');
        status.className = 'project-picker-status';
        status.setAttribute('role', 'status');
        menu.append(list, status);
        wrapper.append(input, icon, menu);
        select.before(wrapper);
        select.hidden = true;
        select.classList.add('d-none');
        let visibleOptions = [];
        let activeIndex = -1;

        function syncLabel() {
            input.value = select.selectedOptions[0]?.textContent.trim() || '';
        }

        function close() {
            menu.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
            syncLabel();
        }

        function setActive(index) {
            activeIndex = index;
            Array.from(list.children).forEach((element, i) => {
                element.setAttribute('aria-selected', String(i === index));
            });
            if (list.children[index]) input.setAttribute('aria-activedescendant', list.children[index].id);
            else input.removeAttribute('aria-activedescendant');
        }

        function choose(index) {
            const option = visibleOptions[index];
            if (!option) return;
            const changed = select.value !== option.value;
            select.value = option.value;
            close();
            if (changed) select.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function render(query = '') {
            const term = query.trim().toLocaleLowerCase();
            const options = Array.from(select.options).filter(option => !option.disabled && !option.hidden);
            const reset = options.find(option => option.value === '');
            const matches = options.filter(option => option.value !== '' && option.textContent.toLocaleLowerCase().includes(term));
            // Keep reset accessible and show at most eight choices, regardless of project count.
            visibleOptions = [...(reset ? [reset] : []), ...matches.slice(0, reset ? 7 : 8)];
            list.replaceChildren();
            visibleOptions.forEach((option, index) => {
                const row = document.createElement('div');
                row.id = `${list.id}-option-${index}`;
                row.className = 'project-picker-option';
                row.setAttribute('role', 'option');
                row.textContent = option.textContent.trim();
                row.addEventListener('pointerdown', event => {
                    event.preventDefault();
                    choose(index);
                });
                row.addEventListener('pointermove', () => setActive(index));
                list.appendChild(row);
            });
            status.textContent = matches.length === 0 ? 'No matching projects. Try a different name.'
                : matches.length > (reset ? 7 : 8) ? `${matches.length} projects found. Type more to narrow the list.`
                : `${matches.length} project${matches.length === 1 ? '' : 's'} found.`;
            menu.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            setActive(term && matches.length ? (reset ? 1 : 0) : -1);
        }

        input.addEventListener('focus', () => { input.select(); render(); });
        input.addEventListener('click', () => { if (menu.hidden) { input.select(); render(); } });
        input.addEventListener('input', () => render(input.value));
        input.addEventListener('blur', close);
        input.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                event.preventDefault();
                event.stopPropagation();
                close();
            } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                if (menu.hidden) render();
                const step = event.key === 'ArrowDown' ? 1 : -1;
                if (visibleOptions.length) {
                    const next = activeIndex < 0 ? (step > 0 ? 0 : visibleOptions.length - 1)
                        : (activeIndex + step + visibleOptions.length) % visibleOptions.length;
                    setActive(next);
                }
            } else if (event.key === 'Enter') {
                event.preventDefault();
                if (!menu.hidden && activeIndex >= 0) choose(activeIndex);
            }
        });
        select.addEventListener('change', syncLabel);
        select.addEventListener('project-picker:sync', syncLabel);
        select.form?.addEventListener('reset', () => setTimeout(close, 0));
        syncLabel();
    }

    function enhanceProjectMenu(menu) {
        if (menu.dataset.projectMenuReady) return;
        const search = menu.querySelector('[data-project-menu-search]');
        if (!search) return;
        menu.dataset.projectMenuReady = 'true';
        const options = Array.from(menu.querySelectorAll('[data-project-menu-option]'));
        const status = document.createElement('div');
        status.className = 'project-picker-status';
        status.setAttribute('role', 'status');
        menu.appendChild(status);

        const filter = () => {
            const term = search.value.trim().toLocaleLowerCase();
            let visible = 0;
            options.forEach(option => {
                const matches = option.textContent.trim().toLocaleLowerCase().includes(term);
                option.hidden = !matches;
                if (matches) visible++;
            });
            status.textContent = visible === 0
                ? 'No matching projects. Try a different name.'
                : `${visible} project${visible === 1 ? '' : 's'} found.`;
        };
        search.addEventListener('input', filter);
        filter();
    }

    function scan(root) {
        if (!(root instanceof Element || root instanceof Document)) return;
        const projectSelect = 'select[data-searchable-project], select[name="project_id"]';
        if (root.matches?.(projectSelect)) enhance(root);
        if (root.matches?.('[data-project-search-menu]')) enhanceProjectMenu(root);
        root.querySelectorAll(projectSelect).forEach(enhance);
        root.querySelectorAll('[data-project-search-menu]').forEach(enhanceProjectMenu);
    }

    function start() {
        scan(document);
        // Project selectors may be added when role-specific panels are refreshed.
        new MutationObserver(records => records.forEach(record => record.addedNodes.forEach(scan)))
            .observe(document.body, { childList: true, subtree: true });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
})();
