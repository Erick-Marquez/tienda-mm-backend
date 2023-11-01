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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();

            $table->string('document_type')->nullable();
            $table->string('serie')->nullable();
            $table->bigInteger('document_number')->nullable();

            $table->date('date_issue')->nullable();

            $table->unsignedDecimal('total', 12,2)->default(0);

            
            $table->text('observation')->nullable();
            $table->boolean('is_credit')->default(false);


            $table->foreignId('user_id')->constrained();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
