<?php

namespace App\Export;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Смета в форме «Приложения № 1» сметы Бетанкура: № / наименование / ед. / кол-во / цена работы /
 * цена материала / стоимость работ / стоимость материалов. Суммы — формулами Excel, как у клиента,
 * чтобы файл можно было править; кол-во материала — CEILING от объёма позиции, где это возможно.
 */
final class EstimateXlsx
{
    public static function build(array $r, string $objectName, string $title): string
    {
        $book = new Spreadsheet();
        $sh = $book->getActiveSheet();
        $sh->setTitle('Смета');
        $money = '#,##0.00';

        $sh->setCellValue('F1', 'Приложение № 1 к договору подряда № ____ от «__» ________ 20__ г.');
        $sh->setCellValue('B1', $objectName);
        $sh->setCellValue('B2', 'СМЕТНЫЙ РАСЧЕТ');
        $sh->setCellValue('B3', $title);
        $sh->getStyle('B1:B2')->getFont()->setBold(true)->setSize(13);

        $sh->fromArray(['№ п/п', 'Наименование работ и затрат', 'Ед. изм.', 'Кол-во', 'Цена, руб. с НДС', null, 'Стоимость, руб. с НДС', null], null, 'A5');
        $sh->fromArray([null, null, null, null, 'работа', 'материалы', 'работа', 'материалы'], null, 'A6');
        foreach (['A5:A6', 'B5:B6', 'C5:C6', 'D5:D6', 'E5:F5', 'G5:H5'] as $m) {
            $sh->mergeCells($m);
        }
        $sh->getStyle('A5:H6')->getFont()->setBold(true);
        $sh->getStyle('A5:H6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);

        $row = 7;
        $n = 0;
        $sectionTotals = [];
        foreach ($r['sections'] as $s) {
            $sh->setCellValue("B$row", $s['name']);
            $sh->getStyle("B$row")->getFont()->setBold(true);
            $first = ++$row;
            $group = null;
            foreach ($s['lines'] as $l) {
                if ($l['group'] && $l['group'] !== $group) {
                    $group = $l['group'];
                    $sh->setCellValue("B$row", $group);
                    $sh->getStyle("B$row")->getFont()->setItalic(true);
                    $row++;
                }
                $w = $row;
                $sh->fromArray([++$n, $l['name'], self::unit($l['unit']), (float) $l['qty']], null, "A$w");
                if ($l['price'] !== null) {
                    $sh->setCellValue("E$w", (float) $l['price']);
                }
                $sh->setCellValue("G$w", "=ROUND(D$w*E$w,2)");
                $sh->getStyle("B$w")->getAlignment()->setWrapText(true);
                $row++;
                foreach ($l['materials'] as $m) {
                    $sh->setCellValue("B$row", $m['name']);
                    $sh->setCellValue("C$row", $m['unit']);
                    $sh->setCellValue("D$row", $m['same_basis']
                        ? '=CEILING(D' . $w . '*' . self::num($m['factor']) . ',' . self::num($m['step']) . ')'
                        : (float) $m['qty']);
                    if ($m['price'] !== null) {
                        $sh->setCellValue("F$row", (float) $m['price']);
                    }
                    $sh->setCellValue("H$row", "=ROUND(F$row*D$row,2)");
                    $row++;
                }
            }
            foreach ($s['extras'] as $e) {
                $sh->fromArray([++$n, $e['name'], $e['unit'], (float) $e['qty'], (float) $e['price']], null, "A$row");
                $sh->setCellValue("G$row", "=ROUND(D$row*E$row,2)");
                $row++;
            }
            $last = $row - 1;
            $sh->setCellValue("F$row", "Итого: {$s['name']}");
            $sh->setCellValue("G$row", "=ROUND(SUM(G$first:G$last),2)");
            $sh->setCellValue("H$row", "=ROUND(SUM(H$first:H$last),2)");
            $sh->getStyle("F$row:H$row")->getFont()->setBold(true);
            $sectionTotals[] = $row;
            $row += 2;
        }

        $sum = fn ($col) => '=' . implode('+', array_map(fn ($t) => "$col$t", $sectionTotals ?: [0]));
        $rate = (float) $r['vat_rate'];
        $sh->setCellValue("B$row", 'ИТОГО по расчету');
        $sh->setCellValue("G$row", $sum('G'));
        $sh->setCellValue("H$row", $sum('H'));
        $itogo = $row++;
        $sh->setCellValue("B$row", 'ВСЕГО');
        $sh->setCellValue("G$row", "=G$itogo+H$itogo");
        $vsego = $row++;
        $sh->setCellValue("B$row", 'в т.ч. НДС ' . rtrim(rtrim(number_format($rate * 100, 2, ',', ''), '0'), ',') . '%');
        $sh->setCellValue("G$row", "=ROUND(G$vsego/" . self::num((string) (1 + $rate)) . '*' . self::num((string) $rate) . ',2)');
        $sh->getStyle("B$itogo:H$row")->getFont()->setBold(true);
        $row += 2;
        $sh->setCellValue("B$row", 'Примечание:');
        $sh->getStyle("B$row")->getFont()->setBold(true);
        foreach (config('crm.notes', []) as $i => $note) {
            $sh->setCellValue('B' . (++$row), ($i + 1) . '. ' . $note);
        }
        $row += 2;
        $sh->setCellValue("B$row", 'Составил ___________________________');
        $sh->setCellValue('B' . ($row + 2), 'Проверил ___________________________');
        $sh->getStyle("D7:H$row")->getNumberFormat()->setFormatCode($money);
        $sh->getStyle('A5:H' . ($vsego + 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        foreach (['A' => 6, 'B' => 70, 'C' => 8, 'D' => 12, 'E' => 12, 'F' => 12, 'G' => 16, 'H' => 16] as $col => $width) {
            $sh->getColumnDimension($col)->setWidth($width);
        }

        $path = tempnam(sys_get_temp_dir(), 'est') . '.xlsx';
        (new Xlsx($book))->save($path);

        return $path;
    }

    private static function unit(string $u): string
    {
        return $u === 'м2' ? '1м2' : $u;
    }

    private static function num(string $x): string
    {
        $x = rtrim(rtrim($x, '0'), '.');
        return $x === '' ? '0' : $x;
    }
}
