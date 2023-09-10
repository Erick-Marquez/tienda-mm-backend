<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_details', function (Blueprint $table) {
            $table->id();

            $table->unsignedDecimal('discount', 12,2)->nullable()->default(0);
            $table->unsignedDecimal('price', 12,2);
            $table->unsignedDecimal('unit_value', 12,2);
            $table->bigInteger('quantity');

            $table->unsignedDecimal('purchase_price', 12,2)->nullable()->default(0);

            $table->unsignedDecimal('total_igv', 12,2)->default(0);
            $table->unsignedDecimal('subtotal', 12,2)->nullable();
            $table->unsignedDecimal('total', 12,2)->nullable();

            $table->foreignId('sale_id')->constrained();
            $table->foreignId('product_id')->constrained();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_details');
    }
};
