<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('timeline_milestones')) {
            if (DB::getDriverName() === 'sqlite') {
                return;
            }

            if (Schema::hasColumn('timeline_milestones', 'planned_date') && !Schema::hasColumn('timeline_milestones', 'start_date')) {
                DB::statement("ALTER TABLE `timeline_milestones` CHANGE `planned_date` `start_date` DATE");
            }

            if (Schema::hasColumn('timeline_milestones', 'actual_date') && !Schema::hasColumn('timeline_milestones', 'end_date')) {
                DB::statement("ALTER TABLE `timeline_milestones` CHANGE `actual_date` `end_date` DATE");
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('timeline_milestones')) {
            if (DB::getDriverName() === 'sqlite') {
                return;
            }

            if (Schema::hasColumn('timeline_milestones', 'start_date') && !Schema::hasColumn('timeline_milestones', 'planned_date')) {
                DB::statement("ALTER TABLE `timeline_milestones` CHANGE `start_date` `planned_date` DATE");
            }

            if (Schema::hasColumn('timeline_milestones', 'end_date') && !Schema::hasColumn('timeline_milestones', 'actual_date')) {
                DB::statement("ALTER TABLE `timeline_milestones` CHANGE `end_date` `actual_date` DATE");
            }
        }
    }
};
