<?php

namespace App\Http\Controllers\BaseExterna;

use App\Http\Controllers\Controller;
use App\Services\BaseExterna\AccessProcessRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ParecerTecnicoController extends Controller
{
    public function __construct(private readonly AccessProcessRepository $accessProcesses) {}

    public function edit(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'protocolo' => ['required', 'string', 'max:255'],
        ]);

        $protocolo = trim($validated['protocolo']);
        $count = $this->accessProcesses->protocolCount($protocolo);

        if ($count !== 1) {
            return $this->blockedRedirect($protocolo, $count);
        }

        return view('base-externa.analise-processo.parecer.edit', $this->viewData($protocolo));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'original_protocolo' => ['required', 'string', 'max:255'],
            '_action' => ['nullable', 'string', 'in:save,save_pdf'],
        ]);

        $originalProtocolo = trim($validated['original_protocolo']);
        $count = $this->accessProcesses->protocolCount($originalProtocolo);

        if ($count !== 1) {
            return $this->blockedRedirect($originalProtocolo, $count);
        }

        $payload = $request->only($this->accessProcesses->parecerTecnicoColumns());
        $this->accessProcesses->updateByProtocolo($originalProtocolo, $payload);

        if (($validated['_action'] ?? 'save') === 'save_pdf') {
            return redirect()
                ->route('base-externa.analise-processo.parecer.pdf', ['protocolo' => $originalProtocolo])
                ->with('success', 'Parecer técnico atualizado com sucesso.');
        }

        return redirect()
            ->route('base-externa.analise-processo.parecer.edit', ['protocolo' => $originalProtocolo])
            ->with('success', 'Parecer técnico atualizado com sucesso.');
    }

    public function pdf(Request $request): Response|RedirectResponse
    {
        $validated = $request->validate([
            'protocolo' => ['required', 'string', 'max:255'],
        ]);

        $protocolo = trim($validated['protocolo']);
        $count = $this->accessProcesses->protocolCount($protocolo);

        if ($count !== 1) {
            return $this->blockedRedirect($protocolo, $count);
        }

        $pdf = Pdf::loadView('base-externa.analise-processo.parecer.pdf', $this->viewData($protocolo))
            ->setPaper('a4');

        return $pdf->download('parecer-tecnico-'.$this->safeFilename($protocolo).'.pdf');
    }

    /**
     * @return array<string, mixed>
     */
    private function viewData(string $protocolo): array
    {
        return [
            'processo' => $this->accessProcesses->findByProtocolo($protocolo),
            'headerColumns' => $this->accessProcesses->parecerTecnicoHeaderColumns(),
            'sections' => $this->accessProcesses->parecerTecnicoSections(),
            'columnTypes' => $this->accessProcesses->columnTypes(),
            'repository' => $this->accessProcesses,
            'originalProtocolo' => $protocolo,
            'offerRomanNumerals' => ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII'],
        ];
    }

    private function blockedRedirect(string $protocolo, int $count): RedirectResponse
    {
        return redirect()
            ->route('base-externa.analise-processo.index', ['search' => $protocolo])
            ->with('error', $this->blockedMessage($count));
    }

    private function blockedMessage(int $count): string
    {
        if ($count > 1) {
            return 'Parecer técnico bloqueado: este protocolo aparece em mais de um registro.';
        }

        return 'Parecer técnico bloqueado: protocolo não encontrado ou vazio.';
    }

    private function safeFilename(string $protocolo): string
    {
        return Str::of($protocolo)
            ->replaceMatches('/[^A-Za-z0-9._-]+/', '-')
            ->trim('-')
            ->lower()
            ->toString() ?: 'processo';
    }
}
