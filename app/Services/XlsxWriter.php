<?php

namespace App\Services;

use RuntimeException;
use ZipArchive;

class XlsxWriter
{
    /**
     * Gera um arquivo XLSX temporário com headers + registros.
     * O chamador é responsável por deletar o arquivo retornado após o uso.
     *
     * @param string[] $headers
     * @param iterable<object|array<string, mixed>> $records
     */
    public static function generate(array $headers, iterable $records): string
    {
        $worksheetTmp = (string) tempnam(sys_get_temp_dir(), 'drsp_ws_');
        $xlsxTmp = (string) tempnam(sys_get_temp_dir(), 'drsp_xl_');
        $fp = null;

        try {
            $fp = fopen($worksheetTmp, 'w');
            if ($fp === false) {
                throw new RuntimeException('Não foi possível criar arquivo temporário para a planilha.');
            }

            fwrite($fp, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>');
            fwrite($fp, '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>');

            fwrite($fp, '<row r="1">');
            foreach ($headers as $i => $header) {
                $col = self::columnLetter($i + 1);
                fwrite($fp, '<c r="' . $col . '1" t="inlineStr"><is><t>' . self::xmlEsc($header) . '</t></is></c>');
            }
            fwrite($fp, '</row>');

            $rowNum = 2;
            foreach ($records as $record) {
                $row = is_array($record) ? $record : (array) $record;
                fwrite($fp, '<row r="' . $rowNum . '">');
                foreach ($headers as $i => $column) {
                    $value = (string) ($row[$column] ?? '');
                    if ($value !== '') {
                        $col = self::columnLetter($i + 1);
                        fwrite($fp, '<c r="' . $col . $rowNum . '" t="inlineStr"><is><t>' . self::xmlEsc($value) . '</t></is></c>');
                    }
                }
                fwrite($fp, '</row>');
                $rowNum++;
            }

            fwrite($fp, '</sheetData></worksheet>');
            fclose($fp);
            $fp = null;

            $zip = new ZipArchive();
            if ($zip->open($xlsxTmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Não foi possível criar o arquivo XLSX.');
            }

            $zip->addFromString('[Content_Types].xml',
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                . '<Default Extension="xml" ContentType="application/xml"/>'
                . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
                . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
                . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
                . '</Types>'
            );
            $zip->addFromString('_rels/.rels',
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
                . '</Relationships>'
            );
            $zip->addFromString('xl/workbook.xml',
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                . '<sheets><sheet name="Dados" sheetId="1" r:id="rId1"/></sheets>'
                . '</workbook>'
            );
            $zip->addFromString('xl/_rels/workbook.xml.rels',
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
                . '</Relationships>'
            );
            $zip->addFromString('xl/styles.xml',
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                . '<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
                . '<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
                . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
                . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
                . '<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>'
                . '</styleSheet>'
            );
            $zip->addFile($worksheetTmp, 'xl/worksheets/sheet1.xml');
            $zip->close();

            @unlink($worksheetTmp);

            return $xlsxTmp;

        } catch (\Throwable $e) {
            if ($fp !== null && is_resource($fp)) {
                fclose($fp);
            }
            @unlink($worksheetTmp);
            @unlink($xlsxTmp);
            throw $e;
        }
    }

    private static function columnLetter(int $n): string
    {
        $result = '';
        while ($n > 0) {
            $n--;
            $result = chr(65 + ($n % 26)) . $result;
            $n = (int) ($n / 26);
        }
        return $result;
    }

    private static function xmlEsc(string $value): string
    {
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value) ?? $value;
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
