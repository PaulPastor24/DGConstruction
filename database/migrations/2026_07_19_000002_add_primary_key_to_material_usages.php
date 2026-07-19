<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The material_usages table was created without a primary key constraint
        // (and the `id` column even contained duplicate values), which prevents
        // row deletion/update from the Supabase dashboard ("table has no primary keys").
        // Recreate `id` as a proper auto-incrementing primary key so every row has
        // a unique identifier. No foreign keys reference material_usages.id, so this
        // is safe.
        if ($this->hasPrimaryKey()) {
            return;
        }

        Schema::table('material_usages', function ($table) {
            $table->dropColumn('id');
        });

        Schema::table('material_usages', function ($table) {
            $table->id()->first();
        });
    }

    public function down(): void
    {
        if ($this->hasPrimaryKey()) {
            Schema::table('material_usages', function ($table) {
                $table->dropPrimary('material_usages_pkey');
            });
        }

        Schema::table('material_usages', function ($table) {
            $table->dropColumn('id');
        });

        Schema::table('material_usages', function ($table) {
            $table->bigInteger('id')->nullable();
        });
    }

    protected function hasPrimaryKey(): bool
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $result = DB::select("PRAGMA table_info(material_usages)");

            foreach ($result as $column) {
                if (! empty($column->pk)) {
                    return true;
                }
            }

            return false;
        }

        $result = DB::select("
            SELECT 1
            FROM information_schema.table_constraints
            WHERE table_name = 'material_usages'
              AND constraint_type = 'PRIMARY KEY'
            LIMIT 1
        ");

        return ! empty($result);
    }
};
