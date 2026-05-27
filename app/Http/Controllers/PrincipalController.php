<?php

namespace App\Http\Controllers;

use App\Services\Principal\CebasRepository;
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
        return $this->excelResponse('cebas-completo.xls');
    }

    public function downloadState(string $uf): StreamedResponse
    {
        $uf = $this->cebas->normalizeUf($uf) ?: 'UF';

        return $this->excelResponse("cebas-{$uf}.xls", $uf);
    }

    private function excelResponse(string $filename, ?string $uf = null): StreamedResponse
    {
        $columns = $this->cebas->downloadColumns();

        return response()->streamDownload(function () use ($columns, $uf) {
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
            foreach ($this->cebas->recordsForDownload($uf) as $record) {
                $row = (array) $record;
                echo '<tr>';
                foreach ($columns as $column) {
                    echo '<td style="mso-number-format:\'\\@\';">' . $this->excelCellValue($row[$column] ?? '') . '</td>';
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
