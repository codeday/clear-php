<?php
namespace CodeDay\Clear\Controllers\Manage\Tools;

use \CodeDay\Clear\Models;
use \Carbon\Carbon;

class BanlistController extends \Controller {

    public function getIndex()
    {
        return \View::make('tools/banlist',
            ['banlist' =>
                Models\Ban
                    ::selectRaw('*, expires_at IS NOT NULL as does_expire')
                    ->orderBy('does_expire')->orderBy('expires_at', 'DESC')
                    ->get()
            ]
        );
    }

    public function postIndex()
    {
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
        $ban = Models\Ban::where('id', '=', \Input::get('id'))->firstOrFail();

        switch (\Input::get('period')) {
            case "void":
                $ban->delete();
                break;
            case "now":
                $ban->expires_at = Carbon::now();
                $ban->save();
                break;
            case "extend":
                $ban->expires_at = $ban->expires_at->addYear();
                $ban->save();
                break;
            case "forever";
                $ban->expires_at = null;
                $ban->save();
                break;
        }

        \Session::flash('status_message', 'Ban updated.');
        return \Redirect::to('/tools/banlist');
    }
}