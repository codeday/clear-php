<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Tools;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;

class TidbitsController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        return \View::make('tools/tidbits/index');
    }

    public function getRegion()
    {
        return \View::make('tools/tidbits/region', ['region' => \Route::input('region')]);
    }

    public function getCsv()
    {
        $event = \Route::input('event');
        $content = Services\Registration::GetCsv($event);
        return (new \Illuminate\Http\Response($content, 200))
            ->header('Content-type', 'text/csv')
            ->header('Content-disposition', 'attachment;filename='.$event->webname.'-attendees.csv');
    }

}
