<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use CodeDay\Clear\Models;

class CreateSupportCalls extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->cleanPhoneNumberColumn();
        $this->cleanPhoneNumberColumn(true);

        \Schema::create('batches_events_supportcalls', function($table) {
            $table->string('call_sid');

            $table->string('caller');
            $table->string('batches_event_id');
            $table->string('answered_by_username')->nullable();
            $table->string('batches_events_registration_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \Schema::drop('batches_events_supportcalls');
    }

    private function cleanPhoneNumberColumn($secondary = false) {
        $attr = 'parent_'.($secondary ? 'secondary_' : '').'phone';
        foreach (
            Models\Batch\Event\Registration
                ::whereNotNull($attr)
                ->get()
            as $registration
        ) {
            $stripped = preg_replace('/\D/', '', $registration->$attr);
            if (strlen($stripped) < 11) {
                $stripped = '1'.$stripped;
            }

            if (strlen($stripped) === 11) {
                $registration->$attr = $stripped;
            } else {
                $registration->$attr = null;
            }
            $registration->save();
        }
    }

}
