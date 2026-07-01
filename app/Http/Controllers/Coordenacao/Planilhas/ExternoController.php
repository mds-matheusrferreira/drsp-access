<?php

namespace App\Http\Controllers\Coordenacao\Planilhas;

use App\Http\Controllers\Controller;
use App\Services\Planilhas\ExternoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExternoController extends Controller
{
    public function __construct(private readonly ExternoService $externo)
    {
    }

    public function index()
    {
        return view('coordenacao.planilhas.externo', [
            'stats' => $this->externo->stats(),
            'importHistory' => $this->externo->importHistory(),
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'excelFile' => ['required', 'file', 'max:51200', 'mimes:xlsx,xls'],
        ]);

        try {
            $result = $this->externo->import($validated['excelFile']);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $user = $request->user();
        $userName = trim((string) ($user?->name ?: $user?->user ?: 'Usuário desconhecido'));

        DB::table('logs')->insert([
            'log' => json_encode([
                'area' => 'planilha_externo',
                'acao' => 'importacao',
                'registros' => $result['inserted_rows'] ?? 0,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'user' => $userName,
            'date_created' => now(),
        ]);

        return back()
            ->with('success', 'Importação Externo concluída com sucesso. Registros inseridos: '.number_format((int) ($result['inserted_rows'] ?? 0), 0, ',', '.').'.')
            ->with('backup_filename', $result['backup_filename'] ?? null);
    }

    public function modelo(): StreamedResponse
    {
        return response()->streamDownload(function () {
            echo $this->externo->templateHtmlTable();
        }, 'modelo-externo.xls', [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function backup(): StreamedResponse
    {
        $xlsxPath = $this->externo->xlsxPath();
        $filename = 'access-backup-' . now()->format('Ymd-His') . '.xlsx';

        return response()->streamDownload(function () use ($xlsxPath) {
            readfile($xlsxPath);
            @unlink($xlsxPath);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function downloadBackup(string $filename): StreamedResponse
    {
        $path = $this->externo->backupPath($filename);

        abort_if(! file_exists($path), 404, 'Backup não encontrado.');

        return response()->streamDownload(function () use ($path) {
            readfile($path);
            @unlink($path);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
