<?php
namespace CodeDay\Clear\Controllers\Api;

use \CodeDay\Clear\Models;

class Regions extends ContractualController {
    protected $fields = [
        'id',
        'name',
        'abbr',
        'location' => [
            'lat',
            'lng'
        ],
        'timezone',
        'events',
        'current_event'
    ];

    public function getNearby()
    {
        $lat = \Input::get('lat');
        $lng = \Input::get('lng');
        $radius = \Input::get('radius') ? \Input::get('radius') : 100;

        $this->fields[] = 'distance';

        $regions = Models\Region::select(
            \DB::raw("*,
                      ( 3959 * acos( cos( radians(?) ) *
                        cos( radians( lat ) )
                        * cos( radians( lng ) - radians(?)
                        ) + sin( radians(?) ) *
                        sin( radians( lat ) ) )
                      ) AS distance"))
            ->havingRaw(\DB::raw('distance <= ?'))
            ->orderBy("distance", "ASC")
            ->setBindings([$lat, $lng, $lat,  $radius])
            ->get();

        return $this->getContract($regions);
    }

    public function getSearch()
    {
        $search_term = \Input::get('term');
        $lat = \Input::get('lat');
        $lng = \Input::get('lng');

        if ($lat && $lng) {

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
        return $this->getContract(Models\Region::all());
    }

    public function getRegion()
    {
        return $this->getContract(\Route::input('region'));
    }
} 