<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetail extends Model
{
    use HasFactory;

    
    protected $fillable = [

        'discount',
        'price',
        'unit_value',
        'quantity',

        'purchase_price',

        'total_igv',
        'subtotal',
        'total',

        'sale_id',
        'product_id',

    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
