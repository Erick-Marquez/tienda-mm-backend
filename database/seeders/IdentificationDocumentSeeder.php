<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IdentificationDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('identification_documents')->insert([
            ['id' => '1', 'description' => 'DNI'],
            ['id' => '6', 'description' => 'RUC'],
            ['id' => '-', 'description' => 'OTROS']
        ]);
    }
}
