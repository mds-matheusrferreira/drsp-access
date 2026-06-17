<?php

namespace App\Http\Controllers\BaseExterna;

use App\Http\Controllers\Controller;
use App\Services\BaseExterna\AccessProcessRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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
        $payload['legislacao_parecer'] = $request->input('legislacao_parecer');
        $original = $this->accessProcesses->findByProtocolo($originalProtocolo) ?? [];
        $sanitized = $this->accessProcesses->sanitizeForUpdate($payload);
        $changedFields = $this->changedFields($original, $sanitized);
        $this->accessProcesses->updateByProtocolo($originalProtocolo, $payload);

        if (($validated['_action'] ?? 'save') === 'save' && $changedFields !== []) {
            $this->logParecerSave($request, $originalProtocolo, $changedFields);
        }

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
        $processo = $this->accessProcesses->findByProtocolo($protocolo);
        $processo['legislacao_parecer'] = $this->legislacaoByDataProtocolo($processo['dt_protocolo'] ?? null);

        return [
            'processo' => $processo,
            'headerColumns' => $this->accessProcesses->parecerTecnicoHeaderColumns(),
            'sections' => $this->accessProcesses->parecerTecnicoSections(),
            'columnTypes' => $this->accessProcesses->columnTypes(),
            'repository' => $this->accessProcesses,
            'originalProtocolo' => $protocolo,
            'parecerLogs' => $this->parecerLogs($protocolo),
            'offerRomanNumerals' => ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII'],
        ];
    }

    /**
     * @param  array<string, mixed>  $original
     * @param  array<string, mixed>  $sanitized
     * @return array<int, string>
     */
    private function changedFields(array $original, array $sanitized): array
    {
        return array_values(array_filter(array_keys($sanitized), fn (string $field) => $this->normalizeForLog($original[$field] ?? null) !== $this->normalizeForLog($sanitized[$field] ?? null)));
    }

    private function normalizeForLog(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return trim(str_replace('_x000D_', "\n", (string) $value));
    }

    private function logParecerSave(Request $request, string $protocolo, array $campos): void
    {
        $user = $request->user();
        $userName = trim((string) ($user?->name ?: $user?->user ?: 'Usuário desconhecido'));

        DB::table('logs')->insert([
            'log' => json_encode([
                'area' => 'parecer_tecnico',
                'acao' => 'salvar',
                'protocolo' => $protocolo,
                'campos_alterados' => array_values($campos),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'user' => $userName,
            'date_created' => now(),
        ]);
    }

    /**
     * @return array<int, array{user: string, date_created: string|null, campos: array<int, string>}>
     */
    private function parecerLogs(string $protocolo): array
    {
        return DB::table('logs')
            ->where('log', 'like', '%"area":"parecer_tecnico"%')
            ->where('log', 'like', '%'.$protocolo.'%')
            ->orderByDesc('date_created')
            ->get()
            ->map(function ($row): array {
                $data = json_decode((string) $row->log, true) ?: [];

                return [
                    'user' => (string) $row->user,
                    'date_created' => $row->date_created ? Carbon::parse($row->date_created)->format('d/m/Y H:i') : null,
                    'campos' => $data['campos_alterados'] ?? [],
                ];
            })
            ->all();
    }

    private function legislacaoByDataProtocolo(mixed $dtProtocolo): string
    {
        if (! $dtProtocolo) {
            return 'Lei 12.101/2009';
        }

        return Carbon::parse($dtProtocolo)->startOfDay()->gte(Carbon::create(2021, 12, 17)->startOfDay())
            ? 'Lei Complementar 187/2021'
            : 'Lei 12.101/2009';
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
