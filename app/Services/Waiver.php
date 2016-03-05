<?php
namespace CodeDay\Clear\Services;

use \Carbon\Carbon;
use \CodeDay\Clear\Models\Batch\Event;
use \CodeDay\Clear\Services;
use Legalesign;

class Waiver {
    public static function send(Event\Registration $reg)
    {
        $waiver = null;
        if ($reg->parent_email) {
            $toEmail = $reg->parent_email;
            $parts = preg_split('/\s/', $reg->parent_name);
            if (count($parts) == 0) {
                $toFirst = 'Parent of';
                $toLast = $reg->first_name;
            } elseif (count($parts) == 1) {
                $toFirst = $parts[0];
                $toLast = '?';
            } else {
                $toFirst = $parts[0];
                $toLast = $parts[1];
            }
            $waiver = config('legalesign.waiver.parent');
        } elseif ($reg->parent_no_info) {
            $toFirst = $reg->first_name;
            $toLast = $reg->last_name;
            $toEmail = $reg->email;
            $waiver = config('legalesign.waiver.student');
        } else {
            throw new \Exception('Parent info not yet collected.');
        }

        $signer = new Legalesign\Signer;
        $signer->firstName = $toFirst;
        $signer->lastName = $toLast;
        $signer->email = $toEmail;

        if ($reg->event->waiver_id) {
            $waiver = $reg->event->waiver_id;
        }

        $document = Legalesign\Document::create()
            ->name('CodeDay Waiver')
            ->group(config('legalesign.group'))
            ->addSigner($signer)
            ->sendWithTemplatePdf($waiver);

        $reg->waiver_signing_id = $document->id;
        $reg->save();
    }

    public static function resend(Event\Registration $reg)
    {
        if (!$reg->waiver_signing_id) {
            throw new \Exception('No outstanding waiver');
        }

        $document = Legalesign\Document::find($reg->waiver_signing_id);
        foreach ($document->signers as $signer) {
            $signer->remind();
        }
    }

    public static function cancel(Event\Registration $reg)
    {
        if (!$reg->waiver_signing_id) {
            throw new \Exception('No outstanding waiver');
        }

        $document = Legalesign\Document::find($reg->waiver_signing_id);
        $document->delete();

        $reg->waiver_pdf_link = null;
        $reg->waiver_signing_id = null;
        $reg->save();
    }

    public static function sync(Event\Registration $reg)
    {
        if ($reg->waiver_pdf_link || !$reg->waiver_signing_id) return;
        
        $document = Legalesign\Document::find($reg->waiver_signing_id);
        if ($document->downloadReady) {
            $s3 = \Aws\S3\S3Client::factory([
                'credentials' => [
                    'key' => \Config::get('aws.key'),
                    'secret' => \Config::get('aws.secret')
                ],
                'version' => '2006-03-01',
                'region' => 'us-west-1'
            ]);
            $result = $s3->putObject(array(
                'Bucket'       => \Config::get('aws.s3.waiverBucket'),
                'Key'          => $reg->id.'.pdf',
                'Body'         => $document->getPdf(),
                'ContentType'  => 'text/plain',
                'ACL'          => 'public-read',
                'Metadata'     => [
                    'Content-Type' => 'application/pdf'
                ]
            ));

            $reg->waiver_pdf_link = $result['ObjectURL'];
            $reg->save();
        }
    }
}
