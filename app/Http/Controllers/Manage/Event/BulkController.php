<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

use CodeDay\Clear\Models;
use CodeDay\Clear\Exceptions;
use CodeDay\Clear\Services;

class BulkController extends \CodeDay\Clear\Http\Controller {
    /**
     * Uploads the file
     */
    public function getIndex()
    {
        $url = \Input::get('dl');
        if ($url && self::isSafeUrl($url)) {
            $contents = file_get_contents($url);
            return $this->postIndex($contents);
        }
        return \View::make('event/registrations/bulk/index');
    }

    /**
     * Uploads the CSV and shows a field mapping tool.
     */
    public function postIndex($file = null)
    {
        if (!isset($file)) {
            $file = file_get_contents(\Input::file('file'));
            unlink(\Input::file('file'));
        }

        $csv = $this->strToCsv($file);
        if (trim(strtolower($csv[0][0])) == "first name") array_shift($csv);

        $fields = $this->rotate($csv);


        return \View::make('event/registrations/bulk/fields', ['file' => $file, 'csv' => $csv, 'fields' => $fields]);
    }

    /**
     * Creates the registrations given the field mapping from the last step.
     */
    public function postFinalize()
    {
        $event = \Route::input('event');
        $fields = array_flip(\Input::get('fields'));
        $csv = $this->strToCsv(trim(\Input::get('file')));
        if (trim(strtolower($csv[0][0])) == "first name") array_shift($csv);

        // Only allow setting recognized fields
        $recognizedFields = [
            'first_name', 'last_name', 'email', 'type', 'parent_name', 'parent_email', 'parent_phone',
            'parent_secondary_phone', 'phone', 'request_loaner'
        ];
        $fields = array_filter($fields,
            function($elem) use ($recognizedFields) { return in_array($elem, $recognizedFields); },
            ARRAY_FILTER_USE_KEY
        );

        // Reindex [0...n] => [fields[0]...fields[n]]
        $csv = array_map(function($line) use ($fields) {
            $x = [];
            foreach ($fields as $field=>$i) {
                $x[$field] = trim($line[$i]);
            }
            return $x;
        }, $csv);

        // Create registrations
        $results = Services\Registration::RegisterGroup($csv, $event, Models\User::Me()->is_admin);

        // Display exceptions
        // TODO(@tylermenezes): There should be an actual display for this
        if (count($results->Exceptions) > 0)
            return implode("", array_map(function($x) { return "<li>".htmlentities($x)."</li>"; }, $results->Registrations))
                    .implode("", array_map(function($x) { return "<li>".$x->getMessage()."</li>"; }, $results->Exceptions));

        // No exceptions!
        \Session::flash('status_message', count($results->Registrations).' registrations added.');
        return \Redirect::to('/event/'.$event->id.'/registrations');
    }

    /**
     * Converts a string into a multi-dimensional array of CSV lines/columns.
     */
    private function strToCsv(string $file)
    {
        $csv = array_map('str_getcsv', explode("\n", trim(str_replace("\r\n", "\n", $file))));

        // Remove empty lines
        return array_filter($csv, function($line) {
            return count(array_filter($line, function($elem){ return trim($elem) !== ""; })) > 0;
        });
    }

    /**
     * Switches rows for columns in a multi-dimensional array.
     */
    private function rotate($arr)
    {
        $newArr = [];
        foreach ($arr as $i=>$row) {
            foreach ($row as $j=>$col) {
                $newArr[$j][$i] = $col;
            }
        }

        return $newArr;
    }

    /**
     * Ensures the passed URL is safe to open (e.g. a real, public URL).
     *
     * @param   string  $url    The URL to check.
     * @return  bool            True if the URL is safe, otherwise false.
     */
    private static function isSafeUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) return false;
        $host = parse_url($url, PHP_URL_HOST);

        // Prevent filter:// attacks
        if (!in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'])) return false;

        // Prevent direct IPs
        if (filter_var($host, FILTER_VALIDATE_IP)) return false;

        // Make sure the IP isn't for a private subnet
        $ips = gethostbynamel($host);
        if (!$ips) return false;
        foreach ($ips as $ip) {
            if (!filter_var(
                $ip, 
                FILTER_VALIDATE_IP, 
                FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE |  FILTER_FLAG_NO_RES_RANGE
            )) return false;
        }

        return true;
    }
} 
