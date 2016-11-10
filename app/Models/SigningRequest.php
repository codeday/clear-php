<?php
namespace CodeDay\Clear\Models;

/**
 * Creates and deals with document signature requests.
 *
 * @package StudentRND\HelpOut\Models
 */
class SigningRequest {
    /**
     * ID of the document signing request.
     *
     * @var integer
     */
    public $id;

    /**
     * HTTP client for the document signing provider.
     *
     * @var \GuzzleHttp\Client
     */
    protected static $legalesign;

    protected function __construct($id)
    {
        $this->id = $id;
    }

    private $_info = null;
    /**
     * Gets info about the document.
     *
     * @return Object       Object containing data about the document. For data info, see:
     *                      http://apidocs.legalesign.com/#document/<document_uuid>/
     */
    public function GetInfo()
    {
        if (!isset($this->_info)) {
            $this->_info = json_decode(self::$legalesign->get('document/'.$this->id)->getBody());
        }

        return $this->_info;
    }

    /**
     * Deletes the document.
     */
    public function Delete()
    {
        self::$legalesign->delete('document/'.$this->id);
    }

    private $_pdfUrl = null;

    /**
     * Gets the PDF download URL.
     *
     * @return string       PDF download URL
     * @throws \Exception   Exception if PDF is not ready for download (use HasPdfUrl())
     */
    public function GetPdf()
    {
        if (!isset($this->_pdfUrl)) {
            if ($this->GetInfo()->download_final) {
                $this->_pdfUrl = strval(self::$legalesign->get('pdf/' . $this->id . '/')->getBody());
            } else {
                throw new \Exception("Document not fully executed.");
            }
        }

        return $this->_pdfUrl;
    }

    /**
     * Mirrors the PDF to Amazon S3 and then gets the URL.
     *
     * @return string       PDF URL
     * @throws \Exception
     */
    public function GetMirroredPdf()
    {
        $s3 = \Aws\S3\S3Client::factory([
            'credentials' => [
                'key' => \Config::get('aws.key'),
                'secret' => \Config::get('aws.secret')
            ],
            'version' => '2006-03-01',
            'region' => 'us-west-1'
        ]);
        $result = $s3->putObject(array(
            'Bucket'       => \Config::get('aws.s3.agreementBucket'),
            'Key'          => $this->id.'.pdf',
            'Body'         => $this->GetPdf(),
            'ContentType'  => 'text/plain',
            'ACL'          => 'public-read',
            'Metadata'     => [
                'Content-Type' => 'application/pdf'
            ]
        ));

        return $result['ObjectURL'];
    }

    /**
     * Checks if the PDF is ready for download
     *
     * @return boolean  True if the PDF is executed and ready for download
     */
    public function HasPdf()
    {
        return $this->GetInfo()->download_final;
    }

    /**
     * Sends reminder emails to signers who haven't yet signed.
     */
    public function SendReminder()
    {
        $toRemindIds = [];
        foreach (self::GetInfo()->signers as $signer) {
            $id = $signer[0];
            $status = $signer[6];
            if ($status < 40) {
                $toRemindIds[] = $id;
            }
        }

        foreach ($toRemindIds as $id) {
            self::$legalesign->post($id.'send-reminder/');
        }
    }

    /**
     * Creates and sends an agreement from an HTML string.
     *
     * @param string            $name               Name of the agreement.
     * @param string            $html               HTML representing the agreement.
     * @param Object            $parentSigner       Object containing firstName, lastName, and email of the signer.
     * @param Object            $parentSigner       Object containing firstName, lastName, and email of signer's parent.
     * @return SigningRequest                       The created agreement.
     */
    public static function NewFromHtml($name, $html, $signer, $parentSigner = null)
    {
        $signers = [[
            'firstname' => $signer->firstName,
            'lastname' => $signer->lastName,
            'email' => $signer->email,
            'order' => 0
        ]];
        if (isset($parentSigner)) {
            $signers[] = [
                'firstname' => $parentSigner->firstName,
                'lastname' => $parentSigner->lastName,
                'email' => $parentSigner->email,
                'order' => 1
            ];
        }

        $docResponse = self::$legalesign->post('document/', [
            'json' => [
                'group' => '/api/v1/group/'.\Config::get('legalesign.group').'/',
                'name' => $name,
                'text' => $html,
                'signers' => $signers,
                'append_pdf' => true,
                'auto_archive' => true,
                'do_email' => true,
                'signers_in_order' => true,
                'signature_type' => 4
            ]
        ]);
        $apiEntityUrl = $docResponse->getHeader('Location')[0];
        $id = call_user_func(function($parts){ return $parts[count($parts) - 2]; }, explode('/', $apiEntityUrl));

        return new self($id);
    }

    /**
     * Loads an existing signing request.
     *
     * @param integer           $id     The ID of the signing request.
     * @return SigningRequest           The signing request.
     */
    public static function FromId($id)
    {
        return new self($id);
    }

    public static function Boot()
    {
        self::$legalesign = new \GuzzleHttp\Client([
            'base_uri' => 'https://legalesign.com/api/v1/',
            'headers' => [
                'Authorization' => 'ApiKey '.\Config::get('legalesign.username').':'.\Config::get('legalesign.secret'),
                'Content-Type' => 'application/json'
            ]
        ]);
    }
}
SigningRequest::Boot();