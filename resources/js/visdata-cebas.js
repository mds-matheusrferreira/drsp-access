const page = document.querySelector('[data-visdata-cebas]');

if (page) {
    const form = page.querySelector('[data-visdata-form]');
    const dropzone = page.querySelector('[data-dropzone]');
    const fileInput = page.querySelector('[data-file-input]');
    const fileLabel = page.querySelector('[data-file-label]');
    const submitButton = page.querySelector('[data-submit-button]');
    const result = page.querySelector('[data-result]');
    const maxSize = 10 * 1024 * 1024;

    const showResult = (type, message) => {
        result.textContent = message;
        result.className = `rounded-lg px-4 py-3 text-sm font-medium ${type === 'success' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'}`;
    };

    const setEnabled = (enabled) => {
        submitButton.disabled = !enabled;
        submitButton.classList.toggle('cursor-not-allowed', !enabled);
        submitButton.classList.toggle('bg-gray-300', !enabled);
        submitButton.classList.toggle('bg-blue-600', enabled);
        submitButton.classList.toggle('hover:bg-blue-700', enabled);
    };

    const selectedFile = () => fileInput.files?.[0] || null;

    const validateFile = () => {
        const file = selectedFile();
        result.classList.add('hidden');

        if (!file) {
            fileLabel.textContent = 'Arquivo Excel .xlsx - Máximo 10MB';
            setEnabled(false);
            return false;
        }

        fileLabel.textContent = file.name;

        if (!/\.(xlsx|xls)$/i.test(file.name)) {
            showResult('error', 'Selecione um arquivo Excel (.xlsx).');
            setEnabled(false);
            return false;
        }

        if (file.size > maxSize) {
            showResult('error', 'O arquivo deve ter no máximo 10MB.');
            setEnabled(false);
            return false;
        }

        setEnabled(true);
        return true;
    };

    const setFiles = (files) => {
        if (!files?.length) {
            return;
        }

        const transfer = new DataTransfer();
        transfer.items.add(files[0]);
        fileInput.files = transfer.files;
        validateFile();
    };

    fileInput?.addEventListener('change', validateFile);

    ['dragenter', 'dragover'].forEach((eventName) => {
        dropzone?.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.add('border-blue-500', 'bg-blue-50');
        });
    });

    ['dragleave', 'drop'].forEach((eventName) => {
        dropzone?.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.remove('border-blue-500', 'bg-blue-50');
        });
    });

    dropzone?.addEventListener('drop', (event) => setFiles(event.dataTransfer.files));

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!validateFile()) {
            return;
        }

        const originalText = submitButton.textContent;
        submitButton.disabled = true;
        submitButton.textContent = 'Importando...';
        showResult('success', 'Importando novos dados CEBAS, aguarde...');

        const data = new FormData();
        data.append('excelFile', selectedFile());

        try {
            const response = await fetch(page.dataset.importUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': page.dataset.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: data,
            });
            const json = await response.json();

            if (!response.ok || !json.success) {
                throw new Error(json.message || 'Erro ao importar planilha.');
            }

            const rows = json.data?.inserted_rows ?? 0;
            showResult('success', `${json.message} Registros inseridos: ${rows}.`);
            page.querySelector('[data-current-total]').textContent = new Intl.NumberFormat('pt-BR').format(rows);
            form.reset();
            validateFile();
        } catch (error) {
            showResult('error', error.message || 'Erro ao importar planilha.');
            setEnabled(true);
        } finally {
            submitButton.textContent = originalText;
        }
    });
}
