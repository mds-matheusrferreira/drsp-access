<?php

namespace App\Http\Controllers\Coordenacao\Planilhas;

use App\Http\Controllers\Controller;
use App\Services\Planilhas\VisdataCebasService;
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
        $columns = $this->visdata->downloadColumns();
        $filename = 'cebas-suas-backup-' . now()->format('Ymd-His') . '.xls';

        return response()->streamDownload(function () use ($columns) {
            echo "\xEF\xBB\xBF";
            echo '<table border="1">';

            if ($columns !== []) {
                echo '<thead><tr>';
                foreach ($columns as $column) {
                    echo '<th>' . $this->excelCellValue($column) . '</th>';
                }
                echo '</tr></thead>';
            }

            echo '<tbody>';
            foreach ($this->visdata->recordsForDownload() as $record) {
                $row = (array) $record;
                echo '<tr>';
                foreach ($columns as $column) {
                    echo '<td style="mso-number-format:\'\@\';">' . $this->excelCellValue($row[$column] ?? '') . '</td>';
                }
                echo '</tr>';
            }
            echo '</tbody></table>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    private function excelCellValue(mixed $value): string
    {
        $value = (string) $value;

        if (preg_match('/^[=+\-@]/', $value) === 1) {
            $value = '\'' . $value;
        }

        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
