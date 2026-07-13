<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM('planning','ongoing','completed','on_hold','archived') DEFAULT 'planning'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM('planning','ongoing','completed','on_hold') DEFAULT 'planning'");
    }
};
