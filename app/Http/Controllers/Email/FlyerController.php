<?php
namespace CodeDay\Clear\Http\Controllers\Email;

use CodeDay\Clear\Models;

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

        return $this->makeFlyer('poster', Models\Batch::Loaded(), $event, $promotion);
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
