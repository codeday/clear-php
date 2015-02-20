<?php
namespace CodeDay\Clear\Controllers\Manage\Settings;

use \CodeDay\Clear\Models;

class EmailTemplatesController extends \Controller {

    public function getIndex()
    {
        return \View::make('settings/email_templates/index', ['email_templates' => Models\EmailTemplate::all()]);
    }

    public function postNew()
    {
        $template = new Models\EmailTemplate;
        $template->name = \Input::get('name');
        $template->to = \Input::get('to');
        $template->from = \Input::get('from');
        $template->subject = \Input::get('subject');
        $template->message = \Input::get('message');
        $template->is_marketing = \Input::get('is_marketing');
        $template->save();

        \Session::flash('status_message', 'Template added');

        return \Redirect::to('/settings/email-templates');
    }

    public function postDelete()
    {
        \Session::flash('status_message', 'Template removed');
        \Route::input('email_template')->delete();
        return \Redirect::to('/settings/email-templates');
    }
} 