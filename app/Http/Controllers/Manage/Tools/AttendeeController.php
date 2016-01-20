<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Tools;

use \CodeDay\Clear\Models;

class AttendeeController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        $search = \Input::get('search');

        $attendees = null;
        if ($search) {
            $searchFields = ['first_name', 'last_name', 'CONCAT(first_name, " ", last_name)', 'email', 'id'];
            $searchQueries = array_map(function($a) {
                return 'UPPER('.$a.') LIKE ?';
            }, $searchFields);

            $searchVariables = array_map(function($a) use ($search) {
                return '%'.strtoupper($search).'%';
            }, $searchFields);

            $where = implode(' OR ', $searchQueries);

            $attendees = Models\Batch\Event\Registration::whereRaw($where, $searchVariables)
                ->orderBy('created_at', 'DESC')
                ->get();
        }

        return \View::make('tools/attendee', [
            'attendees' => $attendees,
            'search' => $search
        ]);
    }
} 