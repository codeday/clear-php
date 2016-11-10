<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Debug;

use \CodeDay\Clear\Models;

class DecryptController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        return \View::make('debug/decrypt');
    }

    public function postIndex()
    {
        $blob = \Input::get('blob');

        try {
            $cipher = mcrypt_module_open(MCRYPT_BLOWFISH,'','cbc','');

            $iv = substr($blob, 0, 8);
            $cyphertext = base64_decode(substr($blob, 8));

            mcrypt_generic_init($cipher, \Config::get('web.error_decrypt_key'), $iv);
            $traceback = mdecrypt_generic($cipher,$cyphertext);
            mcrypt_generic_deinit($cipher);
        } catch (\Exception $ex) {
            $traceback = "Could not decrypt.";
        }

        return \View::make('debug/decrypt', ['traceback' => $traceback]);
    }
} 