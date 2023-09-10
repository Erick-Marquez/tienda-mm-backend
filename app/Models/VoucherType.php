<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherType extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'description',
    ];

    public $incrementing = false;

    public function series()
    {
        return $this->hasMany(Serie::class);
    }
}
