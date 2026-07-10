<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            if (!Schema::hasColumn('materials', 'category')) {
                $table->string('category')->nullable()->after('name');
            }
            if (!Schema::hasColumn('materials', 'current_stock')) {
                $table->decimal('current_stock', 12, 2)->default(0)->after('unit');
            }
            if (!Schema::hasColumn('materials', 'minimum_stock_level')) {
                $table->decimal('minimum_stock_level', 12, 2)->default(0)->after('current_stock');
            }
            if (!Schema::hasColumn('materials', 'supplier')) {
                $table->string('supplier')->nullable()->after('minimum_stock_level');
            }
            if (!Schema::hasColumn('materials', 'description')) {
                $table->text('description')->nullable()->after('supplier');
            }
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            if (Schema::hasColumn('materials', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('materials', 'supplier')) {
                $table->dropColumn('supplier');
            }
            if (Schema::hasColumn('materials', 'minimum_stock_level')) {
                $table->dropColumn('minimum_stock_level');
            }
            if (Schema::hasColumn('materials', 'current_stock')) {
                $table->dropColumn('current_stock');
            }
            if (Schema::hasColumn('materials', 'category')) {
                $table->dropColumn('category');
            }
        });
    }
};
