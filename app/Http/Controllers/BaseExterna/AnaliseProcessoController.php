<?php

namespace App\Http\Controllers\BaseExterna;

use App\Http\Controllers\Controller;
use App\Services\BaseExterna\AccessProcessRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AnaliseProcessoController extends Controller
{
    public function __construct(private readonly AccessProcessRepository $accessProcesses)
    {
    }

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $search = $validated['search'] ?? '';

        return view('base-externa.analise-processo.index', [
            'search' => $search,
            'results' => $this->accessProcesses->search($search),
            'summaryColumns' => $this->summaryColumns(),
        ]);
    }

    public function edit(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'protocolo' => ['required', 'string', 'max:255'],
        ]);

        $protocolo = trim($validated['protocolo']);
        $count = $this->accessProcesses->protocolCount($protocolo);

        if ($count !== 1) {
            return redirect()
                ->route('base-externa.analise-processo.index', ['search' => $protocolo])
                ->with('error', $this->blockedMessage($count));
        }

        return view('base-externa.analise-processo.edit', [
            'processo' => $this->accessProcesses->findByProtocolo($protocolo),
            'sections' => $this->accessProcesses->fieldSections(),
            'columnTypes' => $this->accessProcesses->columnTypes(),
            'repository' => $this->accessProcesses,
            'originalProtocolo' => $protocolo,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ORIGINAL_PROTOCOLO' => ['required', 'string', 'max:255'],
        ]);

        $originalProtocolo = trim($validated['ORIGINAL_PROTOCOLO']);
        $count = $this->accessProcesses->protocolCount($originalProtocolo);

        if ($count !== 1) {
            return redirect()
                ->route('base-externa.analise-processo.index', ['search' => $originalProtocolo])
                ->with('error', $this->blockedMessage($count));
        }

        $newProtocolo = trim((string) $request->input('PROTOCOLO', $originalProtocolo));

        if ($newProtocolo === '') {
            return redirect()
                ->route('base-externa.analise-processo.edit', ['protocolo' => $originalProtocolo])
                ->withInput()
                ->with('error', 'Edição bloqueada: o protocolo não pode ficar vazio.');
        }

        if ($newProtocolo !== $originalProtocolo && $this->accessProcesses->protocolCount($newProtocolo) > 0) {
            return redirect()
                ->route('base-externa.analise-processo.edit', ['protocolo' => $originalProtocolo])
                ->withInput()
                ->with('error', 'Edição bloqueada: o novo protocolo informado já existe em outro registro.');
        }

        $this->accessProcesses->updateByProtocolo($originalProtocolo, $request->except(['_token', '_method', 'ORIGINAL_PROTOCOLO']));

        return redirect()
            ->route('base-externa.analise-processo.edit', ['protocolo' => $newProtocolo])
            ->with('success', 'Processo atualizado com sucesso.');
    }

    /**
     * @return array<int, string>
     */
    private function summaryColumns(): array
    {
        return [
            'PROTOCOLO',
            'PROTOCOLO_SEI',
            'ENTIDADE',
            'CNPJ',
            'MUNICIPIO',
            'UF',
            'TIPO_PROCESSO',
            'FASE_PROCESSO',
            'STATUS_PROCESSO',
        ];
    }

    private function blockedMessage(int $count): string
    {
        if ($count > 1) {
            return 'Edição bloqueada: este protocolo aparece em mais de um registro.';
        }

        return 'Edição bloqueada: protocolo não encontrado ou vazio.';
    }
}
