<?php
namespace CodeDay\Clear\Http\Controllers;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

class IncomingWaiverController extends \CodeDay\Clear\Http\Controller {
    public function postIndex()
    {
        if (\Input::get('secret') !== \config::get('cognitoforms.secret')) \abort(401);

        $waiver = json_decode(file_get_contents('php://input'));
        if (!isset($waiver) || (!isset($waiver->Entry->Document1))) \abort(400);

        $reg = Models\Batch\Event\Registration::where('id', '=', $waiver->Ticket)->firstOrFail();

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
            'Body'         => file_get_contents($waiver->Entry->Document1),
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
