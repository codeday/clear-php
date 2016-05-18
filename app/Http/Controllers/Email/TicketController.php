<?php
namespace CodeDay\Clear\Http\Controllers\Email;

use CodeDay\Clear\Models;
use JBDemonte\Barcode;

class TicketController extends \CodeDay\Clear\Http\Controller
{
    public function getIndex()
    {
        $registration = Models\Batch\Event\Registration::where('id', '=', \Input::get('r'))->firstOrFail();
        $pdf = new \TCPDF('P', 'in', 'LETTER', true, 'UTF-8', false);

        $barcodeFile = tempnam(sys_get_temp_dir(), 'clear-barcode').'.png';
        imagepng($this->generateBarcode($registration), $barcodeFile); 

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
        $pdf->SetTitle('CodeDay Tickets');
        $pdf->SetSubject('CodeDay Tickets');
        $pdf->SetKeywords('codeday,ticket');

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        $pdf->SetFont('dejavusans', '', 14, '', true);

        $pdf->AddPage();

        $html = \View::make('emails/ticket', ['registration' => $registration, 'barcode' => $barcodeFile])->render();

        $pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $html);

        $bin = $pdf->Output('codeday-tickets.pdf', 'S');
        unlink($barcodeFile);

        return response($bin)
            ->header('Content-type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="codeday-tickets.pdf"');
    }

    public function getBarcode()
    {
        $registration = Models\Batch\Event\Registration::where('id', '=', \Input::get('r'))->firstOrFail();

        $response = \Response::make('', 200);
        // Images bigger than ~100x100px will cause PHP to flush the output buffer, so we need to send a header now
        // but images smaller than that won't cause any output buffering, so we need to return a response with the
        // proper header so it doesn't get overridden.
        //
        // This wouldn't be a problem if imagepng would return instead of echoing.
        header('Content-type: image/png');
        header('Cache-control: public,max-age=604800,no-transform');
        $response->header('Content-Type', 'image/png');
        $response->header('Cache-control', 'public,max-age=604800,no-transform');

        \imagepng($this->generateBarcode($registration));
        return $response;
    }

    private function generateBarcode(Models\Batch\Event\Registration $registration)
    {
        $im = \imagecreate(300, 100); 
        $black = \imagecolorallocate($im, 0, 0, 0);
        $white  = \imagecolorallocate($im, 255, 255, 255);

        imagefilledrectangle($im, 0, 0, 300, 120, $white);
        Barcode::gd($im, $black, 40, 40, 0, "datamatrix", $registration->id, 4);
        Barcode::gd($im, $black, 190, 40, 0, "code128", $registration->id, 1, 70);
        imagestring($im, 5, 65, 80, trim(chunk_split($registration->id, 3, ' ')), $black);

        return $im;
    }
}
