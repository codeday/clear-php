<?php
namespace CodeDay\Clear\Controllers\Manage\Settings;

use \CodeDay\Clear\Models;

class BatchesController extends \Controller {

    public function getIndex()
    {
        return \View::make('settings/batches/index');
    }

    public function getCreate()
    {
        return \View::make('settings/batches/create');
    }

    public function postCreate()
    {
        $batch = new Models\Batch;
        $batch->starts_at = \Input::get('starts_at');

        $season = null;
        switch (date('F', $batch->starts_at->timestamp)) {
            case 'December':
            case 'January':
            case 'February':
                $season = 'Winter';
                break;
            case 'March':
            case 'April':
            case 'May':
                $season = 'Spring';
            break;
            case 'June':
            case 'July':
            case 'August':
                $season = 'Summer';
            break;
            case 'September':
            case 'October':
            case 'November':
            default:
                $season = 'Fall';
                break;
        }

        $batch->name = $season.' '.$batch->starts_at->year;
        $batch->is_loaded = 0;
        $batch->save();

        \Session::flash('status_message', 'Batch added');

        return \Redirect::to('/settings/batches/'.$batch->id);
    }

    public function postUpdateRegionSettings()
    {
        $batch = \Route::input('batch');
        $ids = \Input::get('id');

        foreach ($ids as $id=>$settings) {
            $event = \CodeDay\Clear\Models\Batch\Event::where('id', '=', $id)
                ->where('batch_id', '=', $batch->id)
                ->first();

            if ($settings['manager_username']) {
                $user = Models\User::fromS5Username($settings['manager_username']);

                if ($user->username) {
                    $event->manager_username = $user->username;
                } else {
                    $event->manager_username = null;
                }
            } else {
                $event->manager_username = null;
            }

            $event->registration_estimate = $settings['registration_estimate'];
            $event->save();
        }

        \Session::flash('status_message', 'Events updated');

        return \Redirect::to('/settings/batches/'.$batch->id);
    }

    public function postUpdateRegion()
    {
        $batch = \Route::input('batch');
        $region = Models\Region::findOrFail(\Input::get('id'));
        $action = \Input::get('action') === 'delete' ? 'delete' : 'add';

        $event = \CodeDay\Clear\Models\Batch\Event::withTrashed()
                    ->where('region_id', '=', $region->id)
                    ->where('batch_id', '=', $batch->id)
                    ->first();

        if ($action === 'delete' && !$event) {
            \App::abort(404);
        }

        if ($action === 'delete') {
            $event->delete();
            return json_encode(['result' => 200]);
        } else {
            if ($event) {
                $event->restore();
            } else {
                $event = new Models\Batch\Event;
                $event->batch_id = $batch->id;
                $event->region_id = $region->id;
                $event->registration_estimate = 100;
                $event->save();
            }

            return json_encode([
                'result' => 200,
                'data' => [
                    'name' => $event->name,
                    'id' => $event->id,
                    'manager_username' => $event->manager_username,
                    'registration_estimate' => $event->registration_estimate
                ]
            ]);
        }
    }

    public function postAddSupplies()
    {
        $batch = \Route::input('batch');

        $supply = new Models\Batch\Supply;
        $supply->batch_id = $batch->id;
        $supply->item = \Input::get('item');
        $supply->type = \Input::get('type');
        $supply->quantity = floatval(\Input::get('quantity'));
        $supply->save();

        \Session::flash('status_message', 'Supply added');

        return \Redirect::to('/settings/batches/'.$batch->id);
    }

    public function postDeleteSupplies()
    {
        $batch = \Route::input('batch');

        $supply = Models\Batch\Supply::find(\Input::get('id'));
        if (!$supply || $supply->batch_id !== $batch->id) {
            \App::abort(404);
        }

        \Session::flash('status_message', 'Supply removed');

        $supply->delete();

        return \Redirect::to('/settings/batches/'.$batch->id);
    }

    public function getEdit()
    {
        $batch = \Route::input('batch');
        return \View::make('settings/batches/edit', ['batch' => $batch]);
    }

    public function postEdit()
    {
        $batch = \Route::input('batch');
        $batch->name = \Input::get('name');
        $batch->starts_at = \Input::get('starts_at');
        $batch->allow_registrations = \Input::get('allow_registrations') ? true : false;
        $batch->save();

        \Session::flash('status_message', 'Batch updated');

        return \Redirect::to('/settings/batches/'.$batch->id);
    }

    public function getDelete()
    {
        $batch = \Route::input('batch');
        return \View::make('settings/batches/delete', ['batch' => $batch]);
    }

    public function postDelete()
    {
        $batch = \Route::input('batch');
        if ($batch->is_loaded) {
            \Session::flash('error', 'Cannot delete active batch');
            return \Redirect::to('/settings/batches');
        }

        \Session::flash('status_message', 'Batch deleted');

        $batch->delete();
        return \Redirect::to('/settings/batches');
    }

    public function getLoad()
    {
        $batch = \Route::input('batch');
        return \View::make('settings/batches/load', ['batch' => $batch]);
    }

    public function postLoad()
    {
        $batch = \Route::input('batch');
        foreach (Models\Batch::all() as $b) {
            if ($b->id == $batch->id) {
                $b->is_loaded = true;
            } else {
                $b->is_loaded = false;
            }
            $b->save();
        }

        \Session::flash('status_message', 'Batch loaded');

        return \Redirect::to('/settings/batches');
    }
} 