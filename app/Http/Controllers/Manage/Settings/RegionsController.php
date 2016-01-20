<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Settings;

class RegionsController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        return \View::make('settings/regions/index');
    }

    public function getEdit()
    {
        $region = \Route::input('region');
        return \View::make('settings/regions/edit', ['region' => $region]);
    }

    public function postEdit()
    {
        $region = \Route::input('region');
        $region->name = \Input::get('name');
        $region->save();

        \Session::flash('status_message', 'Region updated');

        return \Redirect::to('/settings/regions/'.$region->id);
    }
}
