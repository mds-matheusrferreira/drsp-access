<?php

namespace App\Http\Controllers\Coordenacao\Planilhas;

use App\Http\Controllers\Controller;
use App\Services\Planilhas\VisdataCebasService;
use App\Services\XlsxWriter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VisdataCebasController extends Controller
{
    public function __construct(private readonly VisdataCebasService $visdata)
    {
    }

    public function index()
    {
        return view('coordenacao.planilhas.visdata-cebas', [
            'stats' => $this->visdata->stats(),
        ]);
    }

    public function import(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'excelFile' => ['required', 'file', 'max:51200', 'mimes:xlsx,xls'],
        ]);

        try {
            $result = $this->visdata->import($validated['excelFile']);
        } catch (RuntimeException $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ], 422);
            }

            return back()->with('error', $exception->getMessage());
        }

        if (! $request->expectsJson()) {
            return back()->with('success', 'Importação CEBAS concluída com sucesso. Registros inseridos: '.number_format((int) ($result['inserted_rows'] ?? 0), 0, ',', '.').'.');
        }

        return response()->json([
            'success' => true,
            'message' => 'Importação CEBAS concluída com sucesso.',
            'data' => $result,
        ]);
    }

    public function modelo(): StreamedResponse
    {
        return response()->streamDownload(function () {
            echo $this->visdata->templateHtmlTable();
        }, 'modelo-visdata-cebas.xls', [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function backup(): StreamedResponse
    {
        $xlsxPath = XlsxWriter::generate(
            $this->visdata->downloadColumns(),
            $this->visdata->recordsForDownload()
        );
        $filename = 'cebas-suas-backup-' . now()->format('Ymd-His') . '.xlsx';

        return response()->streamDownload(function () use ($xlsxPath) {
            readfile($xlsxPath);
            @unlink($xlsxPath);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
