<?php
namespace CodeDay\Clear\Controllers\Manage\Tools;

use \CodeDay\Clear\Models;

class ManifestController extends \Controller {

    public function getIndex()
    {
        if (Models\Batch\Event::where('batch_id', '=', Models\Batch::Managed()->id)->whereNull('ship_for')->exists()) {
            return \View::make('tools/manifests_missing');
        } else {
            $totals = [];
            foreach (Models\Batch::Managed()->events as $event) {
                foreach ($event->manifest_generated as $supply) {
                    if (!isset($totals[$supply['item']])) {
                        $totals[$supply['item']] = [
                            'item' => $supply['item'],
                            'quantity' => 0
                        ];
                    }

                    $totals[$supply['item']]['quantity'] += $supply['quantity'];
                }
            }

            $sorted_events = Models\Batch\Event
                ::select('batches_events.*')
                ->leftJoin('regions', 'regions.id', '=', 'batches_events.region_id')
                ->where('batch_id', '=', Models\Batch::Managed()->id)
                ->orderBy('regions.ground_days_in_transit', 'DESC')
                ->get();

            return \View::make('tools/manifests', ['totals' => $totals, 'sorted_events' => $sorted_events]);
        }
    }
}