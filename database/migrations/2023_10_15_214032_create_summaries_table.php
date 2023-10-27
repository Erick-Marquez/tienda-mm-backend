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
        Schema::create('summaries', function (Blueprint $table) {
            $table->id();

            $table->date('date_issue')->nullable();
            $table->date('date_of_reference')->nullable();

            $table->string('type')->nullable();
            $table->string('identifier')->nullable();
            $table->string('ticket')->nullable();

            $table->string('sunat_state', 3)->nullable()->default('P');
            $table->string('sunat_code')->nullable();
            $table->string('sunat_notes')->nullable();

            $table->string('sunat_path_xml')->nullable();
            $table->string('sunat_path_cdr')->nullable();
            $table->string('sunat_filename')->nullable();

            $table->foreignId('user_id')->constrained();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('summaries');
    }
};
