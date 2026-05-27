const principal = document.querySelector('[data-principal-dashboard]');

if (principal) {
    const urls = principal.dataset;
    const stateTotals = new Map();
    const stateModal = document.getElementById('state-modal');
    const stateTitle = document.getElementById('state-modal-title');
    const stateSummary = document.getElementById('state-modal-summary');
    const stateTable = document.getElementById('state-modal-table');
    const statePagination = document.getElementById('state-modal-pagination');
    const stateDownload = document.getElementById('state-download');
    const searchForm = document.getElementById('principal-search-form');
    const searchInput = document.getElementById('principal-search');
    const searchResults = document.getElementById('principal-search-results');
    const updatedAt = document.getElementById('principal-updated-at');
    let currentUf = null;

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const formatNumber = (value) => new Intl.NumberFormat('pt-BR').format(Number(value || 0));

    const colorForTotal = (total) => {
        if (total <= 0) {
            return '#e9f7ef';
        }

        if (total <= 100) {
            return '#b7e4c7';
        }

        if (total <= 500) {
            return '#74c69d';
        }

        return '#2d6a4f';
    };

    const textColorForTotal = () => '#064e3b';

    const fetchJson = async (url) => {
        const response = await fetch(url, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error('Falha ao carregar dados.');
        }

        return response.json();
    };

    const tableHtml = (rows, columns, scrollClass = 'overflow-x-auto') => {
        if (!rows.length) {
            return '<p class="rounded border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">Nenhum registro encontrado.</p>';
        }

        return `
            <div class="${scrollClass} rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>${columns.map((column) => `<th class="whitespace-nowrap px-3 py-2 text-left font-semibold text-gray-700">${escapeHtml(column)}</th>`).join('')}</tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        ${rows.map((row) => `
                            <tr>${columns.map((column) => `<td class="whitespace-nowrap px-3 py-2 text-gray-700">${escapeHtml(row[column])}</td>`).join('')}</tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
    };

    const visibleColumns = (rows, preferredColumns = []) => {
        const allColumns = [...new Set(rows.flatMap((row) => Object.keys(row)))];
        const preferred = preferredColumns.filter((column) => allColumns.includes(column));
        const remaining = allColumns.filter((column) => !preferred.includes(column));

        return [...preferred, ...remaining].slice(0, 12);
    };

    const loadUpdatedAt = async () => {
        try {
            const data = await fetchJson(urls.updatedAtUrl);
            updatedAt.textContent = data.updated_at ? `Atualizado em ${data.updated_at}` : 'Atualização indisponível';
        } catch {
            updatedAt.textContent = 'Atualização indisponível';
        }
    };

    const loadStateTotals = async () => {
        try {
            const data = await fetchJson(urls.stateTotalsUrl);
            data.totals.forEach(({ uf, total }) => stateTotals.set(uf, total));

            document.querySelectorAll('[data-map-state]').forEach((state) => {
                const uf = state.dataset.mapState;
                const total = stateTotals.get(uf) || 0;
                state.dataset.total = total;
                state.querySelectorAll('path').forEach((path) => {
                    path.style.fill = colorForTotal(total);
                });
                state.querySelectorAll('text').forEach((text) => {
                    text.style.fill = textColorForTotal(total);
                });
                state.setAttribute('aria-label', `${uf}: ${formatNumber(total)} entidades CEBAS`);
            });
        } catch {
            document.getElementById('map-status').textContent = 'Não foi possível carregar os totais por UF.';
        }
    };

    const search = async (event) => {
        event.preventDefault();
        const term = searchInput.value.trim();

        if (!term) {
            searchResults.innerHTML = '<p class="text-sm text-gray-600">Digite um termo para pesquisar.</p>';
            return;
        }

        searchResults.innerHTML = '<p class="text-sm text-gray-600">Pesquisando...</p>';

        try {
            const data = await fetchJson(`${urls.searchUrl}?search=${encodeURIComponent(term)}`);
            const columns = visibleColumns(data.data, ['CNPJ', 'ENTIDADE', 'PROCESSO', 'PROTOCOLO', 'BASE', 'UF']);
            searchResults.innerHTML = `
                <div class="mb-3 text-sm text-gray-700">${formatNumber(data.count_total)} resultado(s) encontrado(s). Exibindo até 100 registros.</div>
                ${tableHtml(data.data, columns)}
            `;
        } catch (error) {
            searchResults.innerHTML = `<p class="rounded border border-red-200 bg-red-50 p-4 text-sm text-red-700">${escapeHtml(error.message)}</p>`;
        }
    };

    const openState = async (uf, page = 1) => {
        currentUf = uf;
        stateModal.classList.remove('hidden');
        stateModal.classList.add('flex');
        stateTitle.textContent = `CEBAS - ${uf}`;
        stateSummary.textContent = 'Carregando registros...';
        stateTable.innerHTML = '';
        statePagination.innerHTML = '';
        stateDownload.href = urls.stateDownloadUrl.replace('__UF__', uf);

        try {
            const data = await fetchJson(`${urls.stateUrl.replace('__UF__', uf)}?page=${page}`);
            const columns = visibleColumns(data.cebas, ['UF', 'CNPJ', 'ENTIDADE', 'MUNICIPIO', 'MUNICÍPIO', 'PROCESSO', 'PROTOCOLO']);
            stateSummary.textContent = `${formatNumber(data.total_uf)} registro(s) em ${uf}. Página ${data.page} de ${Math.max(data.total_pages, 1)}.`;
            stateTable.innerHTML = tableHtml(data.cebas, columns, 'max-h-[52vh] overflow-auto');
            statePagination.innerHTML = `
                <button type="button" data-state-page="${Math.max(data.page - 1, 1)}" ${data.page <= 1 ? 'disabled' : ''} class="rounded border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 disabled:cursor-not-allowed disabled:opacity-50">Anterior</button>
                <button type="button" data-state-page="${Math.min(data.page + 1, Math.max(data.total_pages, 1))}" ${data.page >= data.total_pages ? 'disabled' : ''} class="rounded border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 disabled:cursor-not-allowed disabled:opacity-50">Próxima</button>
            `;
        } catch (error) {
            stateSummary.textContent = error.message;
        }
    };

    const closeState = () => {
        stateModal.classList.add('hidden');
        stateModal.classList.remove('flex');
        currentUf = null;
    };

    searchForm.addEventListener('submit', search);

    document.querySelectorAll('[data-map-state]').forEach((state) => {
        state.addEventListener('click', () => openState(state.dataset.mapState));
        state.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openState(state.dataset.mapState);
            }
        });
    });

    document.querySelectorAll('[data-state-close]').forEach((button) => {
        button.addEventListener('click', closeState);
    });

    statePagination.addEventListener('click', (event) => {
        const pageButton = event.target.closest('[data-state-page]');
        if (pageButton && !pageButton.disabled && currentUf) {
            openState(currentUf, Number(pageButton.dataset.statePage));
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && currentUf) {
            closeState();
        }
    });

    loadUpdatedAt();
    loadStateTotals();
}
