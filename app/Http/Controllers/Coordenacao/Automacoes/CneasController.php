<?php

namespace App\Http\Controllers\Coordenacao\Automacoes;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Process;

class CneasController extends Controller
{
    private const DIR = 'cneas/relatorios';

    public function index()
    {
        return view('coordenacao.automacoes.cneas', [
            'latest' => $this->latestReport(),
        ]);
    }

    public function generate(): BinaryFileResponse|RedirectResponse
    {
        Storage::makeDirectory(self::DIR);

        $base = base_path('docs/Automação_CNEAS');
        $filename = 'Relatório_CNEAS gerado em ' . now()->format('d.m.Y H\hi') . '.xlsx';
        $relative = self::DIR . '/' . $filename;
        $output = Storage::path($relative);

        $python = base_path('.venv/Scripts/python.exe');
        $process = new Process([$python, 'script/gerar_relatorio_cneas.py', 'Origem', '--saida', $output, '--sobrescrever'], $base, null, null, 600);
        $process->run();

        if (! $process->isSuccessful() || ! is_file($output)) {
            return back()->with('error', trim($process->getErrorOutput() ?: $process->getOutput()) ?: 'Falha ao gerar relatório CNEAS.');
        }

        return response()->download($output, $filename);
    }

    public function downloadLatest(): BinaryFileResponse|RedirectResponse
    {
        $latest = $this->latestReport();

        if (! $latest) {
            return back()->with('error', 'Nenhum relatório CNEAS gerado.');
        }

        return response()->download(Storage::path($latest['path']), $latest['name']);
    }

    private function latestReport(): ?array
    {
        $files = Storage::files(self::DIR);

        if ($files === []) {
            return null;
        }

        usort($files, fn ($a, $b) => Storage::lastModified($b) <=> Storage::lastModified($a));
        $path = $files[0];

        return [
            'path' => $path,
            'name' => basename($path),
            'date' => date('d/m/Y H:i', Storage::lastModified($path)),
        ];
    }
}
