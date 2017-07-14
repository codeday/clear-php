<?php
namespace CodeDay\Clear\Http\Controllers\Api;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;

class Regions extends ApiController {
    protected $requiresApplication = false;
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
                ->whereNull('batches_events.deleted_at')
                ->whereNull('batches_events.overflow_for_id');
        }

        $regions = $regions->groupBy('regions.id');

        $regions = $regions
            ->with('events')
            ->setBindings($bindings)
            ->get();


        // Update any overridden properties on regions
        if ($withCurrentEvent) {
            $regions = iterator_to_array($regions);
            foreach ($regions as $region) {
                if ($region->current_event->name_override) {
                    $region->name = $region->current_event->name_override;
                }
                if ($region->current_event->abbr_override) {
                    $region->abbr = $region->current_event->abbr_override;
                }
                if ($region->current_event->webname_override) {
                    $region->_webname = $region->current_event->webname_override;
                }
            }
        }

        return json_encode(ModelContracts\Region::Collection($regions, $this->permissions));
    }

    public function getSearch()
    {
        $search_term = \Input::get('term');
        $withCurrentEvent = \Input::get('with_current_event') !== null && \Input::get('with_current_event') !== '0';
        $regionsQuery = Models\Region::whereRaw('UPPER(regions.name) LIKE ?', ['%'.strtoupper($search_term).'%']);

        if ($withCurrentEvent) {
            $regionsQuery = Models\Region
                ::select('regions.*')
                ->rightJoin('batches_events', 'batches_events.region_id', '=', 'regions.id')
                ->whereRaw('batches_events.batch_id = "'.Models\Batch::Loaded()->id.'"')
                ->whereNull('batches_events.overflow_for_id')
                ->whereNotNull('regions.id')
                ->whereNull('batches_events.deleted_at')
                ->whereRaw('UPPER(regions.name) LIKE ? OR UPPER(batches_events.name_override) LIKE ?', [
                    '%'.strtoupper($search_term).'%',
                    '%'.strtoupper($search_term).'%'
                ]);

            $regions = iterator_to_array($regionsQuery->get());

            // Update any overridden properties on regions
            foreach ($regions as $region) {
                if ($region->current_event->name_override) {
                    $region->name = $region->current_event->name_override;
                }
                if ($region->current_event->abbr_override) {
                    $region->abbr = $region->current_event->abbr_override;
                }
                if ($region->current_event->webname_override) {
                    $region->_webname = $region->current_event->webname_override;
                }
            }
        } else {
            $regions = $regionsQuery->get();
        }

        return json_encode(ModelContracts\Region::Collection($regions, $this->permissions));
    }

    public function getIndex()
    {
        return json_encode(ModelContracts\Region::Collection(Models\Region::with('events')->all(), $this->permissions));
    }

    public function getRegion()
    {
        return json_encode(ModelContracts\Region::Model(\Route::input('region'), $this->permissions));
    }
} 
