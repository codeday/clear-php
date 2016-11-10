<?php
namespace CodeDay\Clear\Http\Controllers\Manage;

use \CodeDay\Clear\Models;

class SearchController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        $search = \Input::get('q');

        $batches = [];
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

            foreach ($attendees as $attendee) {
                if ($attendee->event == NULL) continue;
                if (!isset($batches[$attendee->event->batch_id])) {
                    $batches[$attendee->event->batch_id] = (object)[
                        'batch' => $attendee->event->batch,
                        'results' => []
                    ];
                }

                $batches[$attendee->event->batch_id]->results[] = $attendee;
            }

            usort($batches, function($a, $b){ return $b->batch->starts_at->timestamp - $a->batch->starts_at->timestamp; });
        }

        return \View::make('search', [
            'batches' => $batches,
            'search' => $search
        ]);
    }
} 
