<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_code',
        'internal_code',

        'slug',
        'name',
        'description',

        'sale_price',
        'purchase_price',

        'stock',

        'is_weighable',
        'is_invoiceable',
        'is_active'
    ];

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

}
