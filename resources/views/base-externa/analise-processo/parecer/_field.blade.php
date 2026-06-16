@php
    $value = old($field, $processo[$field] ?? '');
    $type = $repository->inputType($field, $columnTypes[$field] ?? null);

    if ($type === 'date' && is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
        $value = \Carbon\Carbon::parse($value)->format('d/m/Y');
        $type = 'text';
    }
@endphp

@if (isset($selectValues[$field]))
    @php
        $isMultiple = in_array($field, ['documentos_pendentes', 'motivo_indeferimento', 'orgao_encaminhamento'], true)
            || str_starts_with($field, 'usuario_')
            || str_starts_with($field, 'qualificacao_usuario_');
        $selectedValues = $isMultiple
            ? array_map('trim', preg_split('/\r\n|\r|\n|;/', (string) $value, -1, PREG_SPLIT_NO_EMPTY))
            : [];
    @endphp

    @if ($isMultiple)
        <div id="{{ $field }}" class="rounded-lg border border-gray-300 bg-gray-50 px-5 py-4 shadow-sm">
            <div class="grid gap-x-10 gap-y-3 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($selectValues[$field] as $option)
                <label class="flex items-start gap-2 text-sm leading-5 text-gray-700">
                    <input type="checkbox" name="{{ $field }}[]" value="{{ $option }}" @checked(in_array($option, $selectedValues, true)) class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span>{{ $option }}</span>
                </label>
            @endforeach
            </div>
        </div>
    @else
        <select id="{{ $field }}" name="{{ $field }}" class="{{ $inputClass }}">
            <option value="">Selecione...</option>
            @foreach ($selectValues[$field] as $optionValue => $optionLabel)
                @php
                    if (is_array($optionLabel)) {
                        $optionValue = $optionLabel['value'];
                        $optionLabel = $optionLabel['label'];
                    } elseif (is_int($optionValue)) {
                        $optionValue = $optionLabel;
                    }

                    $isSelected = is_numeric($value) && is_numeric($optionValue)
                        ? (float) $value === (float) $optionValue
                        : (string) $value === (string) $optionValue;
                @endphp
                <option value="{{ $optionValue }}" @selected($isSelected)>{{ $optionLabel }}</option>
            @endforeach
        </select>
    @endif
@elseif ($type === 'textarea')
    <textarea id="{{ $field }}" name="{{ $field }}" rows="3" class="{{ $inputClass }} resize-none overflow-hidden" data-autoresize>{{ $value }}</textarea>
@else
    <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ $value }}" class="{{ $inputClass }}">
@endif
