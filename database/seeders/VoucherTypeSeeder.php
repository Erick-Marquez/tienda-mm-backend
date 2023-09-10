<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VoucherTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('voucher_types')->insert([
            ['example' => 'N001', 'cod' => null, 'description' => 'Nota de Venta Eléctronica'],
            ['example' => 'F001', 'cod' => '01', 'description' => 'Factura Eléctronica'],
            ['example' => 'B001', 'cod' => '03', 'description' => 'Boleta Eléctronica'],
            ['example' => 'FC01', 'cod' => '07', 'description' => 'Nota de Crédito que modifica una Factura'],
            ['example' => 'BC01', 'cod' => '07', 'description' => 'Nota de Crédito que modifica una Boleta'],
            ['example' => 'FD01', 'cod' => '08', 'description' => 'Nota de Débito que modifica una Factura'],
            ['example' => 'BD01', 'cod' => '08', 'description' => 'Nota de Débito que modifica una Boleta']
        ]);
    }
}
