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

    public function getHandout()
    {
        $event = \Route::input('event');
        if (!isset($event)) {
            \App::abort(404);
        }
        $promotion = Models\Batch\Event\Promotion
            ::where('batches_event_id', '=', $event->id)
            ->where('code', '=', \Input::get('code'))
            ->first();

        return $this->makeFlyer('handout', Models\Batch::Loaded(), $event, $promotion);
    }

    private function makeFlyer($view, Models\Batch $batch, Models\Batch\Event $event = null, Models\Batch\Event\Promotion $code = null)
    {
        $pdf = new \TCPDF('P', 'in', 'LETTER', true, 'UTF-8', false);

        //set margins
        $pdf->SetMargins(0.5, 0.5, 0.5);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(0);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);

        //set auto page breaks
        $pdf->SetAutoPageBreak(false);

        //set image scale factor
        $pdf->setImageScale(1);

        // set document information
        $pdf->SetCreator('Clear');
        $pdf->SetAuthor('StudentRND');
        $pdf->SetTitle('CodeDay');
        $pdf->SetSubject('CodeDay');
        $pdf->SetKeywords('codeday');

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        $pdf->SetFont('dejavusans', '', 14, '', true);

        $pdf->AddPage();

        $html = \View::make('flyers/'.$view, ['batch' => $batch, 'event' => $event, 'code' => $code])->render();

        $pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $html);

        $bin = $pdf->Output($view.'.pdf', 'S');

        $filename = 'codeday';
        if ($event) {
            $filename .= '-'.$event->webname;
        }
        if ($code) {
            $filename .= '-'.strtolower($code->code);
        }
        $filename .= '-'.$view;

        return response($bin)
            ->header('Content-type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$filename.'.pdf"');
    }
}
