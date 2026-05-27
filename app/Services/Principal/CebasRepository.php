<?php

namespace App\Services\Principal;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CebasRepository
{
    private const SEARCH_TABLE = 'cebas';
    private const MAP_TABLE = 'cebas_suas';
    private const UF_REGEX = '/^[A-Z]{2}$/';

    public function updatedAt(): ?string
    {
        if (! Schema::hasTable(self::MAP_TABLE) || ! Schema::hasColumn(self::MAP_TABLE, 'DT_REFERÊNCIA')) {
            return null;
        }

        $value = DB::table(self::MAP_TABLE)
            ->whereNotNull('DT_REFERÊNCIA')
            ->value('DT_REFERÊNCIA');

        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    public function search(string $term, int $limit = 100): array
    {
        if (! Schema::hasTable(self::SEARCH_TABLE) || trim($term) === '') {
            return [
                'search' => $term,
                'columns' => [],
                'data' => [],
                'count_total' => 0,
            ];
        }

        $columns = Schema::getColumnListing(self::SEARCH_TABLE);
        $searchColumns = array_values(array_intersect([
            'CNPJ',
            'PROCESSO',
            'PROTOCOLO',
            'BASE',
            'ENTIDADE',
            'PORTARIAS_SNAS',
            'PORTARIA_DECISAO_RECURSO_SNAS',
            '#Processo',
        ], $columns));

        if ($searchColumns === []) {
            return [
                'search' => $term,
                'columns' => $columns,
                'data' => [],
                'count_total' => 0,
            ];
        }

        $query = DB::table(self::SEARCH_TABLE)->where(function ($query) use ($searchColumns, $term) {
            foreach ($searchColumns as $column) {
                $query->orWhere($column, 'like', '%' . $term . '%');
            }
        });

        $count = (clone $query)->count();
        $rows = $query
            ->limit(max(1, min($limit, 200)))
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();

        return [
            'search' => $term,
            'columns' => $columns,
            'data' => $rows,
            'count_total' => $count,
        ];
    }

    public function stateTotals(): array
    {
        if (! Schema::hasTable(self::MAP_TABLE) || ! Schema::hasColumn(self::MAP_TABLE, 'UF') || ! Schema::hasColumn(self::MAP_TABLE, 'CNPJ')) {
            return [];
        }

        return DB::table(self::MAP_TABLE)
            ->select('UF', DB::raw('COUNT(DISTINCT CNPJ) AS total'))
            ->whereNotNull('UF')
            ->groupBy('UF')
            ->orderBy('UF')
            ->get()
            ->map(fn ($row) => [
                'uf' => strtoupper((string) $row->UF),
                'total' => (int) $row->total,
            ])
            ->all();
    }

    public function stateRecords(string $uf, int $page = 1, int $perPage = 100): array
    {
        $uf = $this->normalizeUf($uf);
        $perPage = max(1, min($perPage, 100));
        $page = max(1, $page);

        if (! $uf || ! Schema::hasTable(self::MAP_TABLE) || ! Schema::hasColumn(self::MAP_TABLE, 'UF')) {
            return [
                'uf' => $uf,
                'total_uf' => 0,
                'limit' => $perPage,
                'page' => $page,
                'total_pages' => 0,
                'cebas' => [],
            ];
        }

        $query = DB::table(self::MAP_TABLE)->where('UF', $uf);
        $total = (clone $query)->count();
        $rows = $query
            ->orderByRaw($this->safeOrderColumn('ENTIDADE'))
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();

        return [
            'uf' => $uf,
            'total_uf' => $total,
            'limit' => $perPage,
            'page' => $page,
            'total_pages' => (int) ceil($total / $perPage),
            'cebas' => $rows,
        ];
    }

    public function recordsForDownload(?string $uf = null): iterable
    {
        if (! Schema::hasTable(self::MAP_TABLE)) {
            return [];
        }

        $query = DB::table(self::MAP_TABLE);

        if ($uf !== null) {
            $uf = $this->normalizeUf($uf);
            if (! $uf || ! Schema::hasColumn(self::MAP_TABLE, 'UF')) {
                return [];
            }
            $query->where('UF', $uf);
        }

        if (Schema::hasColumn(self::MAP_TABLE, 'UF')) {
            $query->orderBy('UF');
        }

        if (Schema::hasColumn(self::MAP_TABLE, 'ENTIDADE')) {
            $query->orderBy('ENTIDADE');
        }

        return $query->cursor();
    }

    public function downloadColumns(): array
    {
        return Schema::hasTable(self::MAP_TABLE)
            ? Schema::getColumnListing(self::MAP_TABLE)
            : [];
    }

    public function normalizeUf(string $uf): ?string
    {
        $uf = strtoupper(trim($uf));

        return preg_match(self::UF_REGEX, $uf) ? $uf : null;
    }

    private function safeOrderColumn(string $column): string
    {
        return Schema::hasColumn(self::MAP_TABLE, $column) ? DB::getQueryGrammar()->wrap($column) : '1';
    }
}
