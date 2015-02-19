<?php
namespace CodeDay\Clear\Controllers\Manage\Tools;

use \CodeDay\Clear\Models;

class SuppliesController extends \Controller {

    public function getIndex()
    {
        return \View::make('tools/supplies');
    }

    public function postAdd()
    {
        $supply = new Models\Batch\Supply;
        $supply->batch_id = Models\Batch::Managed()->id;
        $supply->item = \Input::get('item');
        $supply->type = \Input::get('type');
        $supply->quantity = floatval(\Input::get('quantity'));
        $supply->save();

        \Session::flash('status_message', 'Supply added');

        return \Redirect::to('/tools/supplies');
    }

    public function postDelete()
    {
        $supply = Models\Batch\Supply::find(\Input::get('id'));
        if (!$supply || $supply->batch_id !== Models\Batch::Managed()->id) {
            \App::abort(404);
        }

        \Session::flash('status_message', 'Supply removed');

        $supply->delete();

        return \Redirect::to('/tools/supplies');
    }
} 