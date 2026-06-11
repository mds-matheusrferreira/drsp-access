const ufSelect = document.getElementById('uf');
const municipioSelect = document.getElementById('municipio');

if (ufSelect && municipioSelect) {
    const normalizeMunicipio = (value) => value
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toUpperCase();

    const resetMunicipios = (message = 'Selecione a uf primeiro') => {
        municipioSelect.innerHTML = `<option value="">${message}</option>`;
        municipioSelect.disabled = true;
    };

    const fillMunicipios = (municipios) => {
        const selectedMunicipio = normalizeMunicipio(municipioSelect.dataset.selectedMunicipio || '');
        municipioSelect.innerHTML = '<option value="">Selecione o município</option>';

        municipios
            .sort((a, b) => a.nome.localeCompare(b.nome))
            .forEach((municipio) => {
                const option = document.createElement('option');
                const municipioNormalizado = normalizeMunicipio(municipio.nome);
                option.value = municipioNormalizado;
                option.textContent = municipioNormalizado;

                if (selectedMunicipio === municipioNormalizado) {
                    option.selected = true;
                }

                municipioSelect.appendChild(option);
            });

        municipioSelect.disabled = false;
    };

    const loadMunicipios = async (uf) => {
        if (!uf) {
            resetMunicipios();
            return;
        }

        resetMunicipios('Carregando municípios...');

        try {
            const response = await fetch(`https://servicodados.ibge.gov.br/api/v1/localidades/estados/${encodeURIComponent(uf)}/municipios`);

            if (!response.ok) {
                throw new Error('Não foi possível carregar os municípios.');
            }

            fillMunicipios(await response.json());
        } catch (error) {
            resetMunicipios('Erro ao carregar municípios');
            console.error(error);
        }
    };

    ufSelect.addEventListener('change', () => {
        municipioSelect.dataset.selectedMunicipio = '';
        loadMunicipios(ufSelect.value);
    });

    if (ufSelect.value) {
        loadMunicipios(ufSelect.value);
    } else {
        resetMunicipios();
    }
}
