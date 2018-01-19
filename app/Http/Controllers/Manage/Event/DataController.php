<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Event;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\ModelContracts;
use Carbon\Carbon;

class DataController extends \CodeDay\Clear\Http\Controller {
    public function __construct()
    {
        $this->event = \Route::input('event');
    }

    public function getIndex()
    {
        $chartType = \Input::get('chart');
        if (!method_exists($this, $chartType)) abort(404);

        $data = $this->$chartType();
        return json_encode($data);
    }

    protected function AgeDistribution()
    {
        $ages = \DB::table(with(new Models\Batch\Event\Registration)->getTable())
                ->selectRaw('age as name, COUNT(*) as value')
                ->where('batches_event_id', '=', $this->event->id)
                ->where('type', '=', 'student')
                ->whereNotNull('age')
                ->orderBy('age', 'asc')
                ->groupBy('age')
                ->get();

        $ages = $this->transpose($ages);

        for ($i = 10; $i < 26; $i++) {
            $agesFinal[] = [
                'name' => $i,
                'value' => $ages[$i] ?? 0
            ];
        }
        $older = 0;
        for ($i = 26; $i < max(array_keys([$ages])); $i++) $older += $ages[$i];
        $agesFinal[] = [
            'name' => '26+',
            'value' => $older   
        ];

        return $agesFinal;
    }

    protected function WaiverStatus()
    {
        $waiverGroups = "IF(waiver_pdf_link IS NOT NULL, 'signed', IF(waiver_signing_id IS NOT NULL, 'started', 'none'))";
        $res = \DB::table(with(new Models\Batch\Event\Registration)->getTable())
                ->selectRaw("$waiverGroups as name, COUNT(*) as value")
                ->where('batches_event_id', '=', $this->event->id)
                ->where('type', '=', 'student')
                ->groupBy('name')
                ->get();

        $res = $this->transpose($res);

        return [
            [
                'name' => 'none',
                'quantity' => $res['none'] ?? 0.001
            ],
            [
                'name' => 'started',
                'quantity' => $res['started'] ?? 0.001
            ],
            [
                'name' => 'signed',
                'quantity' => $res['signed'] ?? 0.001
            ]
        ];
    }

    protected function RegistrationsOverTime()
    {
        $view = "SELECT DATE(created_at) as date, COUNT(*) as daily FROM batches_events_registrations WHERE type = 'student' AND batches_event_id = '{$this->event->id}' GROUP BY date";
        $res = \DB::table(\DB::raw("($view) as x, (SELECT @run := 0) as y"))
                                  ->selectRaw('x.date as name, (@run := @run + x.daily) as value')
                                  ->get();

        if (count($res) == 0) return [];

        $date = new Carbon($res[0]->name);
        $now = Carbon::now();
        $res = $this->transpose($res);

        $dates = [];
        while ($now->gt($date)) {
            $lastVal = $res[$date->format('Y-m-d')] ?? $lastVal;
            $dates[] = [
                "date" => $date->format('c'),
                "value" => $lastVal
            ];
            $date = $date->addDays(1);
        }


        return ['dataByTopic' => [[
                    'topicName' => 'Students',
                    'topic' => 1,
                    'dates' => $dates
                ]]];

    }

    private function transpose($queryResult)
    {
        $res = [];
        foreach ($queryResult as $row)
        {
            $res[$row->name] = $row->value;
        }

        return $res;
    }
}
