<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

use \CodeDay\Clear\Models;

class BulkController extends \CodeDay\Clear\Http\Controller {
    /**
     * Uploads the file
     */
    public function getIndex()
    {
        return \View::make('event/registrations/bulk/index');
    }

    /**
     * Uploads the CSV and shows a field mapping tool.
     */
    public function postIndex()
    {
        $file = file_get_contents(\Input::file('file'));
        $fields = $this->rotate($this->strToCsv($file));

        unlink(\Input::file('file'));

        return \View::make('event/registrations/bulk/fields', ['file' => $file, 'fields' => $fields]);
    }

    /**
     * Creates the registrations given the field mapping from the last step.
     */
    public function postFinalize()
    {
        $fields = array_flip(\Input::get('fields'));
        $file = $this->strToCsv(trim(\Input::get('file')));
        $event = \Route::input('event');
        foreach ($file as $line) {
            $registration = new Models\Batch\Event\Registration;
            foreach (['first_name', 'last_name', 'email', 'type', 'parent_name', 'parent_email', 'parent_phone',
                      'parent_secondary_phone'] as $field) {
                if (isset($fields[$field])) {
                    $registration->$field = trim($line[intval($fields[$field])]);
                }
            }

            if (!isset($registration->type) || !trim($registration->type)) {
                $registration->type = 'student';
            }

            if (!in_array($registration->type, ['student', 'volunteer']) && !isset($registration->parent_email)) {
                $registration->parent_no_info = true;
            }

            if (Models\User::Me()->is_admin && isset($fields['webname'])) {
                $webname = $line[intval($fields['webname'])];
                // TODO: This isn't very efficient
                try {
                    $event = Models\Batch\Event
                        ::where('batch_id', '=', Models\Batch::Managed()->id)
                        ->where(function($w) use ($webname) {
                            return $w
                                ->where('webname_override', '=', $webname)
                                ->orWhere(function($w2) use ($webname) {
                                    return $w2
                                        ->where('region_id', '=', $webname)
                                        ->whereNull('webname_override');
                                });
                        })
                        ->orderBy('webname_override')
                        ->first();
                    $registration->batches_event_id = $event->id;
                } catch (\Exception $ex) { echo "No webname $webname"; } // TODO: Better error handling
            } else {
                $registration->batches_event_id = $event->id;
            }

            $registration->save();
        }

        \Session::flash('status_message', count($file).' registrations added.');
        return \Redirect::to('/event/'.$event->id.'/registrations');
    }

    /**
     * Converts a string into a multi-dimensional array of CSV lines/columns.
     */
    private function strToCsv($file)
    {
        return array_map('str_getcsv', explode("\n", str_replace("\r\n", "\n", $file)));
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
} 
