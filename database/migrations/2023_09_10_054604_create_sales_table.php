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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('document_number');

            $table->date('date_issue')->nullable();
            $table->date('date_due')->nullable();

            $table->unsignedDecimal('global_discount', 12,2)->nullable()->default(0);
            $table->unsignedDecimal('item_discount', 12,2)->nullable()->default(0);
            $table->unsignedDecimal('total_discount', 12,2)->nullable()->default(0);

            $table->unsignedDecimal('subtotal', 12,2)->default(0);
            $table->unsignedDecimal('total_igv', 12,2)->default(0);
            $table->unsignedDecimal('total_exonerated', 12,2)->default(0);
            $table->unsignedDecimal('total_unaffected', 12,2)->default(0);
            $table->unsignedDecimal('total_free', 12,2)->default(0);
            $table->unsignedDecimal('total_taxed', 12,2)->default(0);
            $table->unsignedDecimal('total', 12,2)->default(0);

            $table->text('observation')->nullable();
            $table->unsignedDecimal('received_money', 12,2)->nullable();
            $table->unsignedDecimal('change', 12,2)->nullable();

            $table->foreignId('serie_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('user_id')->constrained();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
