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
    const simplifiedModal = document.getElementById('simplified-modal');
    const simplifiedPrint = document.getElementById('simplified-print');
    const simplifiedFields = document.querySelectorAll('[data-simplified-field]');
    const searchForm = document.getElementById('principal-search-form');
    const searchInput = document.getElementById('principal-search');
    const searchResults = document.getElementById('principal-search-results');
    const updatedAt = document.getElementById('principal-updated-at');
    let currentUf = null;
    let currentSearchRows = [];
    let currentSimplifiedRow = null;

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

    const searchTableHtml = (rows, columns) => {
        if (!rows.length) {
            return '<p class="rounded border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">Nenhum registro encontrado.</p>';
        }

        return `
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="whitespace-nowrap px-3 py-2 text-left font-semibold text-gray-700">Ações</th>
                            ${columns.map((column) => `<th class="whitespace-nowrap px-3 py-2 text-left font-semibold text-gray-700">${escapeHtml(column)}</th>`).join('')}
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        ${rows.map((row, index) => `
                            <tr>
                                <td class="whitespace-nowrap px-3 py-2">
                                    <button type="button" data-simplified-index="${index}" class="rounded bg-blue-600 px-3 py-2 text-xs font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Leitura Simplificada</button>
                                </td>
                                ${columns.map((column) => `<td class="whitespace-nowrap px-3 py-2 text-gray-700">${escapeHtml(row[column])}</td>`).join('')}
                            </tr>
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

    const simplifiedReportFields = [
        ['PROTOCOLO', 'PROTOCOLO'],
        ['ENTIDADE', 'NOME DA ENTIDADE'],
        ['MUNICIPIO', 'MUNICÍPIO'],
        ['UF', 'UF'],
        ['DT_PROTOCOLO', 'DATA DO PROTOCOLO'],
        ['FASE_PROCESSO', 'FASE DO PROCESSO'],
        ['DT_DECISAO_SNAS', 'DT_DECISAO_SNAS'],
        ['DT_PUBLICACAO_CERTIFICACAO_ANTERIOR_DOU', 'DATA PUBLICAÇÃO CERTIFICAÇÃO ANTERIOR DOU'],
        ['DT_PUBLICACAO_DOU_RECONSIDERACAO_SNAS', 'DATA PUBLICAÇÃO DOU RECONSIDERAÇÃO SNAS'],
        ['DT_CERTIFICACAO_ANTERIOR_INICIO', 'DATA DE CERTIFICAÇÃO ANTERIOR INÍCIO'],
        ['DT_INICIO_CERTIFICACAO_ATUAL', 'DATA INÍCIO DE CERTIFICAÇÃO ATUAL'],
        ['CNPJ', 'CNPJ'],
        ['CEBAS', 'STATUS DA CERTIFICAÇÃO'],
        ['TIPO_PROCESSO', 'TIPO DE PROCESSO'],
        ['PORTARIAS_SNAS', 'PORTARIA SNAS'],
        ['DT_PUBLICACAO_PORTARIA_SNAS_DOU', 'DT_PUBLICACAO_PORTARIA_SNAS_DOU'],
        ['PORTARIA_DECISAO_RECURSO_SNAS', 'PORTARIA DECISÃO RECURSO SNAS'],
        ['OFERTAS', 'OFERTAS'],
        ['DT_CERTIFICACAO_ANTERIOR_FIM', 'DATA DE CERTIFICAÇÃO ANTERIOR FIM'],
        ['DT_FIM_CERTIFICACAO_ATUAL', 'DATA FIM DE CERTIFICAÇÃO ATUAL'],
    ];

    const rowValue = (row, field) => {
        const value = row?.[field];

        if (value === undefined || value === null || String(value).trim() === '') {
            return '-';
        }

        return value;
    };

    const openSimplified = (row) => {
        currentSimplifiedRow = row;
        simplifiedFields.forEach((field) => {
            field.textContent = rowValue(row, field.dataset.simplifiedField);
        });
        simplifiedModal.classList.remove('hidden');
        simplifiedModal.classList.add('flex');
    };

    const closeSimplified = () => {
        simplifiedModal.classList.add('hidden');
        simplifiedModal.classList.remove('flex');
        currentSimplifiedRow = null;
    };

    const printSimplified = () => {
        if (!currentSimplifiedRow) {
            return;
        }

        const reportRows = simplifiedReportFields.map(([field, label]) => `
            <tr>
                <th>${escapeHtml(label)}</th>
                <td>${escapeHtml(rowValue(currentSimplifiedRow, field))}</td>
            </tr>
        `).join('');
        const printWindow = window.open('', '_blank', 'width=900,height=700');

        if (!printWindow) {
            return;
        }

        printWindow.document.write(`
            <!DOCTYPE html>
            <html lang="pt-BR">
            <head>
                <meta charset="utf-8">
                <title>Informações Simplificadas</title>
                <style>
                    body { color: #374151; font-family: Arial, sans-serif; margin: 32px; }
                    h1 { color: #1f2937; font-size: 24px; margin-bottom: 8px; }
                    p { margin: 0 0 24px; }
                    table { border-collapse: collapse; width: 100%; }
                    th, td { border-bottom: 1px solid #e5e7eb; padding: 10px 8px; text-align: left; vertical-align: top; }
                    th { color: #6b7280; font-size: 12px; text-transform: uppercase; width: 38%; }
                    td { font-size: 15px; }
                </style>
            </head>
            <body>
                <h1>Informações Simplificadas</h1>
                <p>Resumo do processo ${escapeHtml(rowValue(currentSimplifiedRow, 'PROTOCOLO'))}</p>
                <table>${reportRows}</table>
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
    };

    const loadUpdatedAt = async () => {
        try {
            const data = await fetchJson(urls.updatedAtUrl);
            updatedAt.textContent = data.updated_at ? `Atualizado em ${data.updated_at}` : 'Atualização indisponível';
        } catch {
            updatedAt.textContent = 'Atualização indisponível';
        }
    };

    const mapStates = () => document.querySelectorAll('[data-map-state], .principal-brazil-map .estado');

    const stateUf = (state) => state.dataset.mapState || state.querySelector('text')?.textContent.trim();

    const loadStateTotals = async () => {
        try {
            const data = await fetchJson(urls.stateTotalsUrl);
            data.totals.forEach(({ uf, total }) => stateTotals.set(uf, total));

            mapStates().forEach((state) => {
                const uf = stateUf(state);
                const total = stateTotals.get(uf) || 0;
                state.dataset.mapState = uf;
                state.dataset.total = total;
                state.classList.add('map-state');
                state.setAttribute('role', 'button');
                state.setAttribute('tabindex', '0');
                state.setAttribute('href', '#');
                state.setAttribute('xlink:href', '#');
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
            currentSearchRows = data.data;
            const columns = visibleColumns(data.data, ['BASE', 'PROCESSO', 'PROTOCOLO', 'PROTOCOLO_SEI', 'ENTIDADE', 'CNPJ', 'MUNICIPIO', 'UF', 'DT_PROTOCOLO', 'FASE_PROCESSO']);
            searchResults.innerHTML = `
                <div class="mb-3 text-sm text-gray-700">${formatNumber(data.count_total)} resultado(s) encontrado(s). Exibindo até 100 registros.</div>
                ${searchTableHtml(data.data, columns)}
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

    searchResults.addEventListener('click', (event) => {
        const button = event.target.closest('[data-simplified-index]');

        if (!button) {
            return;
        }

        openSimplified(currentSearchRows[Number(button.dataset.simplifiedIndex)]);
    });

    document.querySelectorAll('[data-simplified-close]').forEach((button) => {
        button.addEventListener('click', closeSimplified);
    });

    simplifiedPrint.addEventListener('click', printSimplified);

    simplifiedModal.addEventListener('click', (event) => {
        if (event.target === simplifiedModal) {
            closeSimplified();
        }
    });

    mapStates().forEach((state) => {
        const openMapState = (event) => {
            event.preventDefault();
            openState(stateUf(state));
        };

        state.classList.add('map-state');
        state.setAttribute('role', 'button');
        state.setAttribute('tabindex', '0');
        state.addEventListener('click', openMapState);
        state.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                openMapState(event);
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

        if (event.key === 'Escape' && currentSimplifiedRow) {
            closeSimplified();
        }
    });

    loadUpdatedAt();
    loadStateTotals();
}
