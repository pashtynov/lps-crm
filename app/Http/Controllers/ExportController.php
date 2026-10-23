<?php

namespace App\Http\Controllers;

use App\Calc\ObjectEstimate;
use App\Export\EstimateXlsx;
use App\Models\CrmObject;
use App\Models\Estimate;
use Mpdf\Mpdf;

class ExportController extends Controller
{
    public function xlsx(CrmObject $object)
    {
        return $this->sendXlsx(ObjectEstimate::calculate($object), $object, 'Расчёт от ' . now()->format('d.m.Y'));
    }

    public function pdf(CrmObject $object)
    {
        return $this->sendPdf(ObjectEstimate::calculate($object), $object, 'Расчёт от ' . now()->format('d.m.Y'));
    }

    public function savedXlsx(Estimate $estimate)
    {
        return $this->sendXlsx($estimate->result, $estimate->object, $estimate->title);
    }

    public function savedPdf(Estimate $estimate)
    {
        return $this->sendPdf($estimate->result, $estimate->object, $estimate->title);
    }

    private function sendXlsx(array $r, CrmObject $object, string $title)
    {
        $path = EstimateXlsx::build($r, $object->name, $title);

        return response()->download($path, $this->filename($object, 'xlsx'))->deleteFileAfterSend();
    }

    private function sendPdf(array $r, CrmObject $object, string $title)
    {
        $mpdf = new Mpdf(['format' => 'A4-L', 'margin_left' => 10, 'margin_right' => 10, 'margin_top' => 12,
            'margin_bottom' => 12, 'default_font' => 'dejavusans', 'tempDir' => storage_path('app/mpdf')]);
        $mpdf->WriteHTML(view('exports.estimate', ['r' => $r, 'object' => $object, 'title' => $title])->render());

        return response($mpdf->Output('', 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"estimate.pdf\"; filename*=UTF-8''" . rawurlencode($this->filename($object, 'pdf')),
        ]);
    }

    private function filename(CrmObject $object, string $ext): string
    {
        $slug = trim(preg_replace('/[^\p{L}\p{N}]+/u', '_', $object->name), '_');

        return "Смета_{$slug}_" . now()->format('Y-m-d') . ".$ext";
    }
}
