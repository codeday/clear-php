<?php
namespace CodeDay\Clear\Http\Controllers\Manage\Tools;

use \CodeDay\Clear\Models;
use \CodeDay\Clear\Services;
use \Carbon\Carbon;

class BanlistController extends \CodeDay\Clear\Http\Controller {

    public function getIndex()
    {
        if (!Models\User::me()->is_admin) {
            return \Redirect::to('https://www.cognitoforms.com/Srnd1/ConductReport');
        }

        $banlist = Models\Ban
            ::selectRaw('*, expires_at IS NOT NULL as does_expire')
            ->orderBy('does_expire')->orderBy('expires_at', 'DESC');
        if (!Models\User::me()->is_admin) {
            $banlist->where('created_by_username', '=', Models\User::me()->username);
        }
        $banlist = $banlist->get();

        return \View::make('tools/banlist',
            [
                'banlist' => $banlist,
                'first_name' => \Input::get('first_name'),
                'last_name' => \Input::get('last_name'),
                'email' => \Input::get('email')
            ]
        );
    }

    public function postIndex()
    {
        if (!Models\User::me()->is_admin) \App::abort(401);

        $ban = new Models\Ban;
        $ban->first_name = \Input::get('first_name');
        $ban->last_name = \Input::get('last_name');
        $ban->email = \Input::get('email');
        $ban->reason = \Input::get('reason');
        $ban->details = \Input::get('details');
        $ban->created_by_username = Models\User::me()->username;

        switch (\Input::get('duration')) {
            case "current-season":
                $ban->expires_at = Models\Batch::Loaded()->starts_at->addDay();
                break;
            case "next-season":
                $ban->expires_at = Models\Batch::Loaded()->starts_at->addDay()->addMonths(3);
                if ($ban->expires_at->month > 5 && $ban->expires_at->month <= 8) {
                    $ban->expires_at = $ban->expires_at->addMonths(3);
                }
                break;
            case "year":
                $ban->expires_at = Carbon::now()->addYear();
                break;
            case "2year":
                $ban->expires_at = Carbon::now()->addYears(2);
                break;
            case "forever":
                if (!Models\User::me()->is_admin) \App::abort(401);
                $ban->expires_at = null;
                break;
            default:
                \App::abort(403);
        }
        $ban->save();

        \Session::flash('status_message', 'Ban created.');
        return \Redirect::to('/tools/banlist');
    }

    public function postPeriod()
    {
        if (!Models\User::me()->is_admin) \App::abort(401);
        $ban = Models\Ban::where('id', '=', \Input::get('id'))->firstOrFail();

        if (!Models\User::me()->is_admin && $ban->created_by_username != Models\User::me()->username) {
            \App::abort(401);
        }

        switch (\Input::get('period')) {
            case "void":
                $action = "voided";
                $ban->delete();
                break;
            case "now":
                $action = "ended";
                $ban->expires_at = Carbon::now();
                $ban->save();
                break;
            case "extend":
                $action = "extended";
                $ban->expires_at = $ban->expires_at->addYear();
                $ban->save();
                break;
            case "forever";
                $action = "made forever";
                $ban->expires_at = null;
                $ban->save();
                break;
        }

        \Session::flash('status_message', 'Ban updated.');
        return \Redirect::to('/tools/banlist');
    }
}
