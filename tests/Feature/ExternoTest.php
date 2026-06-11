<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Planilhas\ExternoService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use ZipArchive;

class ExternoTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_externo_routes(): void
    {
        $this->get('/coordenacao/planilhas/externo')->assertRedirect(route('login'));
        $this->get('/coordenacao/planilhas/externo/modelo')->assertRedirect(route('login'));
        $this->get('/coordenacao/planilhas/externo/backup')->assertRedirect(route('login'));
        $this->post('/coordenacao/planilhas/externo/import')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_externo_page(): void
    {
        $this->createAccessTable();

        $this->actingAs(User::factory()->create())
            ->get('/coordenacao/planilhas/externo')
            ->assertOk()
            ->assertSee('Planilha Externo - Upload e download de Dados')
            ->assertSee('Baixar modelo de planilha')
            ->assertSee('Baixar tabela atual')
            ->assertSee('Enviar Arquivo Externo')
            ->assertSee('access');
    }

    public function test_model_download_contains_expected_headers(): void
    {
        $this->createAccessTable();

        $response = $this->actingAs(User::factory()->create())->get('/coordenacao/planilhas/externo/modelo');

        $response->assertOk();
        $this->assertStringContainsString('protocolo', $response->streamedContent());
        $this->assertStringContainsString('documentos_obrigatorios', $response->streamedContent());
        $this->assertStringContainsString('ativo', $response->streamedContent());
    }

    public function test_backup_downloads_current_access_data(): void
    {
        $this->createAccessTable();
        $this->app['db']->table('access')->insert($this->row(['protocolo' => 'BACKUP-001', 'entidade' => 'Entidade Backup']));

        $response = $this->actingAs(User::factory()->create())->get('/coordenacao/planilhas/externo/backup');

        $response->assertOk();
        $this->assertSame('application/vnd.ms-excel; charset=UTF-8', $response->headers->get('content-type'));
        $content = $response->streamedContent();
        $this->assertStringContainsString('BACKUP-001', $content);
        $this->assertStringContainsString('Entidade Backup', $content);
    }

    public function test_valid_import_replaces_existing_access_rows(): void
    {
        $this->createAccessTable();
        $this->app['db']->table('access')->insert($this->row(['protocolo' => 'antigo', 'entidade' => 'Antiga']));

        $file = $this->xlsxUpload([
            ExternoService::HEADERS,
            $this->row(['protocolo' => 'novo', 'entidade' => 'Nova Entidade', 'uf' => 'DF', 'dt_protocolo' => '29/05/2026']),
        ]);

        $this->actingAs(User::factory()->create())
            ->post('/coordenacao/planilhas/externo/import', ['excelFile' => $file])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('access', ['protocolo' => 'antigo']);
        $this->assertDatabaseHas('access', [
            'protocolo' => 'novo',
            'entidade' => 'Nova Entidade',
            'uf' => 'DF',
            'dt_protocolo' => '2026-05-29',
        ]);
    }

    public function test_invalid_header_does_not_delete_existing_access_rows(): void
    {
        $this->createAccessTable();
        $this->app['db']->table('access')->insert($this->row(['protocolo' => 'antigo', 'entidade' => 'Antiga']));

        $headers = ExternoService::HEADERS;
        $headers[0] = 'protocolo_errado';

        $file = $this->xlsxUpload([$headers, $this->row(['protocolo' => 'novo'])]);

        $this->actingAs(User::factory()->create())
            ->post('/coordenacao/planilhas/externo/import', ['excelFile' => $file])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('access', ['protocolo' => 'antigo', 'entidade' => 'Antiga']);
        $this->assertDatabaseMissing('access', ['protocolo' => 'novo']);
    }

    private function createAccessTable(): void
    {
        Schema::dropIfExists('access');
        Schema::create('access', function (Blueprint $table) {
            foreach (ExternoService::HEADERS as $column) {
                $table->text($column)->nullable();
            }
        });
    }

    private function row(array $overrides = []): array
    {
        return array_merge(array_fill_keys(ExternoService::HEADERS, null), $overrides);
    }

    private function xlsxUpload(array $rows): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'externo') . '.xlsx';
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="EXTERNO" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheetXml($rows));
        $zip->close();

        return new UploadedFile($path, 'externo.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    private function sheetXml(array $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
        foreach ($rows as $rowNumber => $row) {
            $xml .= '<row r="' . ($rowNumber + 1) . '">';
            foreach (array_values($row) as $index => $value) {
                $column = $this->columnName($index + 1);
                $xml .= '<c r="' . $column . ($rowNumber + 1) . '" t="inlineStr"><is><t>' . htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</t></is></c>';
            }
            $xml .= '</row>';
        }

        return $xml . '</sheetData></worksheet>';
    }

    private function columnName(int $index): string
    {
        $name = '';
        while ($index > 0) {
            $index--;
            $name = chr(65 + ($index % 26)) . $name;
            $index = intdiv($index, 26);
        }

        return $name;
    }
}
