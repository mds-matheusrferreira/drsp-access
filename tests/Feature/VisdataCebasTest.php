<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Planilhas\VisdataCebasService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use ZipArchive;

class VisdataCebasTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_visdata_routes(): void
    {
        $this->get('/coordenacao/planilhas/visdata-cebas')->assertRedirect(route('login'));
        $this->get('/coordenacao/planilhas/visdata-cebas/modelo')->assertRedirect(route('login'));
        $this->get('/coordenacao/planilhas/visdata-cebas/backup')->assertRedirect(route('login'));
        $this->post('/coordenacao/planilhas/visdata-cebas/import')->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_visdata_page(): void
    {
        $this->createCebasSuasTable();

        $this->actingAs(User::factory()->create())
            ->get('/coordenacao/planilhas/visdata-cebas')
            ->assertOk()
            ->assertSee('Planilha Visdata - Upload e download de Dados')
            ->assertSee('Enviar CEBAS')
            ->assertSee('Baixar Modelo de Planilha')
            ->assertSee('Baixar backup atual');
    }

    public function test_backup_downloads_current_cebas_suas_data(): void
    {
        $this->createCebasSuasTable();
        $this->app['db']->table('cebas_suas')->insert($this->row(['protocolo' => '71000.1', 'entidade' => 'Entidade Atual']));

        $response = $this->actingAs(User::factory()->create())->get('/coordenacao/planilhas/visdata-cebas/backup');

        $response->assertOk();
        $this->assertSame('application/vnd.ms-excel; charset=UTF-8', $response->headers->get('content-type'));
        $this->assertStringContainsString('Entidade Atual', $response->streamedContent());
    }

    public function test_model_download_contains_expected_headers(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/coordenacao/planilhas/visdata-cebas/modelo');

        $response->assertOk()->assertDownload('modelo-visdata-cebas.xls');
        $content = $response->streamedContent();
        $this->assertStringContainsString('protocolo', $content);
        $this->assertStringContainsString('dt_referencia', $content);
        $this->assertStringContainsString('ofertas', $content);
    }

    public function test_valid_import_replaces_existing_cebas_suas_rows(): void
    {
        $this->createCebasSuasTable();
        $this->app['db']->table('cebas_suas')->insert($this->row(['protocolo' => 'antigo', 'entidade' => 'Antiga']));

        $file = $this->xlsxUpload([
            VisdataCebasService::HEADERS,
            ['novo', '123', 'Nova Entidade', 'Brasília', 'DF', '1', 'Certificada', '5300108', '999', 'Sim', '2024', '01/01/2024', '31/12/2024', 'R$ 1.234,56', '2026-05-29', 'Serviço'],
        ]);

        $this->actingAs(User::factory()->create())
            ->postJson('/coordenacao/planilhas/visdata-cebas/import', ['excelFile' => $file])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.inserted_rows', 1);

        $this->assertDatabaseMissing('cebas_suas', ['protocolo' => 'antigo']);
        $this->assertDatabaseHas('cebas_suas', [
            'protocolo' => 'novo',
            'entidade' => 'Nova Entidade',
            'uf' => 'DF',
            'dt_referencia' => '2026-05-29',
        ]);
    }

    public function test_invalid_header_does_not_delete_existing_rows(): void
    {
        $this->createCebasSuasTable();
        $this->app['db']->table('cebas_suas')->insert($this->row(['protocolo' => 'antigo', 'entidade' => 'Antiga']));

        $headers = VisdataCebasService::HEADERS;
        $headers[0] = 'protocolo_errado';

        $file = $this->xlsxUpload([$headers, ['novo']]);

        $this->actingAs(User::factory()->create())
            ->postJson('/coordenacao/planilhas/visdata-cebas/import', ['excelFile' => $file])
            ->assertStatus(422)
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('cebas_suas', ['protocolo' => 'antigo', 'entidade' => 'Antiga']);
    }

    private function createCebasSuasTable(): void
    {
        Schema::create('cebas_suas', function (Blueprint $table) {
            foreach (VisdataCebasService::HEADERS as $column) {
                $table->string($column)->nullable();
            }
        });
    }

    private function row(array $overrides = []): array
    {
        return array_merge(array_fill_keys(VisdataCebasService::HEADERS, null), $overrides);
    }

    private function xlsxUpload(array $rows): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'visdata') . '.xlsx';
        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="SITUAÇÃO CNPJ CEBAS (VISDATA)" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheetXml($rows));
        $zip->close();

        return new UploadedFile($path, 'visdata.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }

    private function sheetXml(array $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
        foreach ($rows as $rowNumber => $row) {
            $xml .= '<row r="' . ($rowNumber + 1) . '">';
            foreach ($row as $index => $value) {
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
