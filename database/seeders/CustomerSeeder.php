<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::create([
            'name' => 'Clientes varios',
            'document' => '00000000',
            'phone' => '999999999',
            'email' => 'clientes.varios@mamamia.com',
            'identification_document_id' => '1'
        ]);
    }
}
