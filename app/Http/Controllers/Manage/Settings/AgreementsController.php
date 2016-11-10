<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Settings;

use \CodeDay\Clear\Models;

class AgreementsController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        return \View::make('settings/agreements/index', ['agreements' => Models\Agreement::all()]);
    }

    public function getCreate()
    {
        return \View::make('settings/agreements/add_edit');
    }

    public function postCreate()
    {
        $agreement = new Models\Agreement;
        $agreement->name = \Input::get('name');
        if (\Input::get('markdown')) {
            $agreement->markdown = \Input::get('markdown');
        } else {
            $agreement->html = \Input::get('html');
        }

        $agreement->save();
        \Session::flash('status_message', 'Agreement created.');
        return \Redirect::to('/settings/agreements');
    }

    public function getEdit()
    {
        return \View::make('settings/agreements/add_edit', ['agreement' => \Route::input('agreement')]);
    }

    public function postEdit()
    {
        $agreement = \Route::input('agreement');

        $agreement->name = \Input::get('name');
        if (\Input::get('markdown')) {
            $agreement->markdown = \Input::get('markdown');
            $agreement->html = null;
        } else {
            $agreement->html = \Input::get('html');
            $agreement->markdown = null;
        }

        $agreement->save();
        \Session::flash('status_message', 'Agreement updated.');
        return \Redirect::to('/settings/agreements');
    }
}