<?php
namespace CodeDay\Clear\Controllers\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

class Regions extends ApiController {
    public function getNearby()
    {
        $lat = \Input::get('lat');
        $lng = \Input::get('lng');
        $radius = \Input::get('radius');
        $limit = \Input::get('limit');
        $withCurrentEvent = \Input::get('with_current_event') !== null && \Input::get('with_current_event') !== '0';

        $this->fields[] = 'distance';

        $bindings = [$lat, $lng, $lat];

        $regions = Models\Region::select(
            \DB::raw("regions.*,
                      ( 3959 * acos( cos( radians(?) ) *
                        cos( radians( lat ) )
                        * cos( radians( lng ) - radians(?)
                        ) + sin( radians(?) ) *
                        sin( radians( lat ) ) )
                      ) AS distance"))
            ->orderBy("distance", "ASC");

        if ($radius) {
            $regions = $regions->havingRaw(\DB::raw('distance <= ?'));
            $bindings[] = $radius;
        }

        if ($limit) {
            $regions = $regions->limit($limit);
        }

        if ($withCurrentEvent) {
            $regions = $regions->rightJoin('batches_events', 'batches_events.region_id', '=', 'regions.id')
                ->whereRaw('batches_events.batch_id = "'.Models\Batch::Loaded()->id.'"')
                ->whereNotNull('regions.id')
                ->whereNull('batches_events.deleted_at');
        }

        $regions = $regions->groupBy('regions.id');

        $regions = $regions
            ->setBindings($bindings)
            ->get();

        return json_encode(ModelContracts\Region::Collection($regions, $this->permissions));
    }

    public function getSearch()
    {
        $search_term = \Input::get('term');
        $lat = \Input::get('lat');
        $lng = \Input::get('lng');

        if ($lat && $lng) {
            // TODO
            /* $regions = Models\Region::select(
                \DB::raw("*,
                      ( 3959 * acos( cos( radians(?) ) *
                        cos( radians( lat ) )
                        * cos( radians( lng ) - radians(?)
                        ) + sin( radians(?) ) *
                        sin( radians( lat ) ) )
                      ) AS distance"))
                ->orderBy("distance", "ASC")
                ->havingRaw(\DB::raw('name LIKE "%?%"'))
                ->setBindings([$lat, $lng, $lat, $search_term])
                ->get(); */
            $regions = Models\Region::where('name', 'like', '%'.$search_term.'%')->get();
        } else {
            $regions = Models\Region::where('name', 'like', '%'.$search_term.'%')->get();
        }

        return $this->getContract($regions);
    }

    public function getIndex()
    {
        return json_encode(ModelContracts\Region::Collection(Models\Region::all(), $this->permissions));
    }

    public function getRegion()
    {
        return json_encode(ModelContracts\Region::Model(\Route::input('region'), $this->permissions));
    }
} 