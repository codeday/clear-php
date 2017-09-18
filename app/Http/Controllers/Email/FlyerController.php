<?php
namespace CodeDay\Clear\Http\Controllers\Email;

use CodeDay\Clear\Models;
define('FPDF_FONTPATH', base_path().'/resources/fonts');

class FlyerController extends \CodeDay\Clear\Http\Controller
{
    public function getPoster()
    {
        $event = \Route::input('event');

        $promotion = null;
        if ($event) {
            $promotion = Models\Batch\Event\Promotion
                ::where('batches_event_id', '=', $event->id)
                ->where('code', '=', \Input::get('code'))
                ->first();
        }

        $pdf = new \FPDI('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->setSourceFile(base_path().'/resources/pdf/poster.pdf');
        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx, 0, -1, 216);

        // now write some text above the imported page
        $pdf->AddFont('Proxima Nova', 'B', 'ProximaNova-Bold.php');
        $pdf->SetFont('Proxima Nova', 'B');
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFontSize(25);

        // PDF details
        $rightBlockMid = 157;
        $pageMid = 108;

        $centerText = function($xMidline, $y, $text) use ($pdf) {
            $xStart = $xMidline - $pdf->GetStringWidth($text)/2;
            $pdf->Text($xStart, $y, $text);
        };

        $eventStarts = $event ? $event->starts_at : Models\Batch::Loaded()->starts_at->timestamp;
        $eventEnds = $event ? $event->ends_at : Models\Batch::Loaded()->ends_at->timestamp;
        $textDate = date('F j-', $eventStarts).date('j', $eventEnds);
        $centerText($rightBlockMid, 138, $textDate);

        if ($event && $event->venue_name) {
            $textHosted = 'HOSTED AT '.strtoupper($event->venue_name);
            $pdf->SetFontSize(10);
            $centerText($rightBlockMid, 160, $textHosted);
        }

        $textUrl = 'Get your tickets: codeday.org'.($event ? '/'.$event->webname : '');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFontSize(25);
        $centerText(108, 256, $textUrl);

        $textPromo = $promotion ? 'Promo code '.$promotion->code : 'Tickets sell out quickly!';
        $pdf->SetFontSize(16);
        $centerText(108, 265, $textPromo);

        $filename = 'codeday';
        if ($event) {
            $filename .= '-'.$event->webname;
        }
        if ($promotion) {
            $filename .= '-'.strtolower($promotion->code);
        }
        $filename .= '-poster.pdf';


        $bin = $pdf->Output($filename, 'S');

        return response($bin)
            ->header('Content-type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$filename.'"');
    }

    public function getJpg()
    {
        $cacheKey = md5(\Request::fullUrl());
        $img = null;
        if (!\Cache::has($cacheKey)) {
            $pdfTmp = tempnam(sys_get_temp_dir(), 'clear-poster-').'.pdf';
            $jpgTmp = tempnam(sys_get_temp_dir(), 'clear-poster-').'.jpg';
            $posterPdf = $this->getPoster()->content();
            file_put_contents($pdfTmp, $posterPdf);

            $pdf = new \Spatie\PdfToImage\Pdf($pdfTmp);
            $pdfImg = $pdf->getImageData($jpgTmp);
            if (\Input::has('r')) {
                $pdfImg->resampleImage(\Input::get('r', 72), \Input::get('r', 72), \Imagick::FILTER_LANCZOS, 1);
            }
            $pdfImg->setImageFormat('jpeg');

            $img = $pdfImg->getImageBlob();
            unlink($pdfTmp);
            \Cache::put($cacheKey, $img, 60*60*24);
        } else {
            $img = \Cache::get($cacheKey);
        }
        return response($img)
            ->header('Content-type', 'image/png');
    }

    public function getHandout()
    {
        $event = \Route::input('event');

        $promotion = null;
        if ($event) {
            $promotion = Models\Batch\Event\Promotion
                ::where('batches_event_id', '=', $event->id)
                ->where('code', '=', \Input::get('code'))
                ->first();
        }

        $pdf = new \FPDI('P', 'mm', 'Letter');
        $pdf->AddPage();
        $pdf->setSourceFile(base_path().'/resources/pdf/handout.pdf');
        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx, 0, -1, 216);

        // now write some text above the imported page
        $pdf->AddFont('Proxima Nova', 'B', 'ProximaNova-Bold.php');
        $pdf->AddFont('Proxima Nova', '', 'ProximaNova-Regular.php');
        $pdf->SetFont('Proxima Nova', '');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFontSize(12);

        // PDF details
        $pageMid = 108;

        $centerText = function($xMidline, $y, $text) use ($pdf) {
            $xStart = $xMidline - $pdf->GetStringWidth($text)/2;
            $pdf->Text($xStart, $y, $text);
            $pdf->Text($xStart, $y+140, $text);
        };

        $eventStarts = $event ? $event->starts_at : Models\Batch::Loaded()->starts_at->timestamp;
        $eventEnds = $event ? $event->ends_at : Models\Batch::Loaded()->ends_at->timestamp;
        $textDate = date('F j-', $eventStarts).date('j', $eventEnds);
        $pdf->SetFont('Proxima Nova', 'B');
        $centerText($pageMid, 29, $textDate);

        $textUrl = 'For more info and to get your tickets, visit: codeday.org'.($event ? '/'.$event->webname : '');
        $pdf->SetFont('Proxima Nova', 'B');
        $centerText($pageMid, 59, $textUrl);

        $textPromo = $promotion ? 'Use promo code '.$promotion->code.' for '.round($promotion->percent_discount,0).'% off' : 'Tickets sell out quickly!';
        $pdf->SetFont('Proxima Nova', '');
        $centerText($pageMid, 64, $textPromo);

        $filename = 'codeday';
        if ($event) {
            $filename .= '-'.$event->webname;
        }
        if ($promotion) {
            $filename .= '-'.strtolower($promotion->code);
        }
        $filename .= '-handout.pdf';


        $bin = $pdf->Output($filename, 'S');

        return response($bin)
            ->header('Content-type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$filename.'"');
    }
}
