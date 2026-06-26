<?php

namespace App\Http\Controllers;

use App\Services\Principal\CebasRepository;
use App\Services\XlsxWriter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrincipalController extends Controller
{
    public function __construct(private readonly CebasRepository $cebas)
    {
    }

    public function index()
    {
        return view('dashboard');
    }

    public function updatedAt(): JsonResponse
    {
        return response()->json([
            'updated_at' => $this->cebas->updatedAt(),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json($this->cebas->search($validated['search'] ?? ''));
    }

    public function stateTotals(): JsonResponse
    {
        return response()->json([
            'totals' => $this->cebas->stateTotals(),
        ]);
    }

    public function stateRecords(Request $request, string $uf): JsonResponse
    {
        $validated = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        return response()->json($this->cebas->stateRecords($uf, (int) ($validated['page'] ?? 1)));
    }

    public function downloadAll(): StreamedResponse
    {
        return $this->xlsxResponse('cebas-completo.xlsx');
    }

    public function downloadState(string $uf): StreamedResponse
    {
        $uf = $this->cebas->normalizeUf($uf) ?: 'UF';

        return $this->xlsxResponse("cebas-{$uf}.xlsx", $uf);
    }

    private function xlsxResponse(string $filename, ?string $uf = null): StreamedResponse
    {
        $xlsxPath = XlsxWriter::generate(
            $this->cebas->downloadColumns(),
            $this->cebas->recordsForDownload($uf)
        );

        return response()->streamDownload(function () use ($xlsxPath) {
            readfile($xlsxPath);
            @unlink($xlsxPath);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
