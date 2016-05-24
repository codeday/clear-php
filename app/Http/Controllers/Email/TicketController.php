<?php
namespace CodeDay\Clear\Http\Controllers\Email;

use CodeDay\Clear\Models;
use JBDemonte\Barcode;
use Passbook\Pass;
use Passbook\Type\EventTicket;

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

    public function getApple()
    {
        $registration = Models\Batch\Event\Registration::where('id', '=', \Input::get('r'))->firstOrFail();

        $pass = new EventTicket($registration->id, $registration->event->full_name);
        $pass->setBackgroundColor('rgb(203, 121, 114)');
        $pass->setForegroundColor('rgb(255, 255, 255)');
        $pass->setSuppressStripShine(true);

        //$pass->setExpirationDate((new \Carbon\Carbon($registration->event->starts_at))->addDays(2)->timestamp());

        $structure = new Pass\Structure();

        $type = new Pass\Field('type', ucfirst($registration->type));
        $type->setLabel('Ticket');
        $structure->addHeaderField($type);

        $primary = new Pass\Field('event', $registration->event->full_name);
        $primary->setLabel('Event');
        $structure->addPrimaryField($primary);

        $secondary = new Pass\Field('location', $registration->event->venue_name ? $registration->event->venue_name : 'TBA');
        $secondary->setLabel('Location');
        $structure->addSecondaryField($secondary);

        $sDate = new Pass\Field('date', date('M j, Y', $registration->event->starts_at));
        $sDate->setLabel('Starts');
        $sTime = new Pass\Field('time', '11am');
        $sTime->setLabel('Doors Open');
        $price = new Pass\Field('price', '$'.$registration->amount_paid);
        $price->setLabel('Price');
        $structure->addAuxiliaryField($sDate);
        $structure->addAuxiliaryField($sTime);
        $structure->addAuxiliaryField($price);

        $pass->addImage(new Pass\Image(base_path().'/resources/img/pass.png', 'icon'));
        $pass->addImage(new Pass\Image(base_path().'/resources/img/logo.png', 'logo'));
        $pass->addImage(new Pass\Image(base_path().'/resources/img/jump.png', 'thumbnail'));

        // Set pass structure
        $pass->setStructure($structure);

        // Add barcode
        $barcode = new Pass\Barcode(Pass\Barcode::TYPE_QR, $registration->id);
        $pass->setBarcode($barcode);

        $outDir = sys_get_temp_dir().'/'.str_random(10);
        $outFile = $outDir.'/'.$registration->id.'.pkpass';
        $factory = new \Passbook\PassFactory(\Config::get('apple.passid'), \Config::get('apple.teamid'),
            \Config::get('apple.team'), base_path().'/resources/signing/'.\Config::get('apple.passid').'.p12',
            \Config::get('apple.passp12password'), base_path().'/resources/signing/apple_wwdrca.pem');
        $factory->setOutputPath($outDir);
        $factory->package($pass);

        $out = file_get_contents($outFile);

        unlink($outFile);
        rmdir($outDir);


        return response($out)
            ->header('Content-type', 'application/vnd.apple.pkpass')
            ->header('Content-Disposition', 'attachment; filename="codeday-tickets.pkpass"');
    }
}
