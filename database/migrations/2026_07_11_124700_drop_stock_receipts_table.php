<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Drop the legacy `stock_receipts` table if it exists.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('stock_receipts');
    }

    /**
     * Reverse the migrations.
     * Recreate a minimal `stock_receipts` table if rollback is needed.
     * Modify as needed to match prior structure if required.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('stock_receipts')) {
            Schema::create('stock_receipts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('material_id')->nullable();
                $table->decimal('quantity', 16, 4)->nullable();
                $table->string('supplier')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }
};
