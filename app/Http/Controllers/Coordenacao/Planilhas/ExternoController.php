<?php

namespace App\Http\Controllers\Coordenacao\Planilhas;

use App\Http\Controllers\Controller;
use App\Services\Planilhas\ExternoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'excelFile' => ['required', 'file', 'max:10240', 'mimes:xlsx,xls'],
        ]);

        try {
            $result = $this->externo->import($validated['excelFile']);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Importação Externo concluída com sucesso. Registros inseridos: '.number_format((int) ($result['inserted_rows'] ?? 0), 0, ',', '.').'.');
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
        $columns = $this->externo->downloadColumns();
        $filename = 'access-backup-' . now()->format('Ymd-His') . '.xls';

        return response()->streamDownload(function () use ($columns) {
            echo "\xEF\xBB\xBF";
            echo '<table border="1">';
            echo '<thead><tr>';
            foreach ($columns as $column) {
                echo '<th>' . $this->excelCellValue($column) . '</th>';
            }
            echo '</tr></thead><tbody>';

            foreach ($this->externo->recordsForDownload() as $record) {
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
