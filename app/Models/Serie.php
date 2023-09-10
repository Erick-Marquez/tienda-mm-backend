<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Serie extends Model
{
    use HasFactory;

    protected $fillable = [
        'serie',
        'current_number',

        'is_active',

        'voucher_type_id',
    ];

    public function voucherType()
    {
        return $this->belongsTo(VoucherType::class);
    }
}
