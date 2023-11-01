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
        Schema::table('sales', function (Blueprint $table) {
            $table->string('sunat_state', 3)->nullable()->default('P')->after('change');
            $table->string('sunat_code')->nullable()->after('sunat_state');
            $table->string('sunat_notes')->nullable()->after('sunat_code');

            $table->string('sunat_path_xml')->nullable()->after('sunat_notes');
            $table->string('sunat_path_cdr')->nullable()->after('sunat_path_xml');
            $table->string('sunat_filename')->nullable()->after('sunat_path_cdr');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('sunat_code');
            $table->dropColumn('sunat_state');
            $table->dropColumn('sunat_notes');

            $table->dropColumn('sunat_path_xml');
            $table->dropColumn('sunat_path_cdr');
            $table->dropColumn('sunat_filename');
        });
    }
};
