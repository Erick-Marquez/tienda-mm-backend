<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdentificationDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'description',
    ];

    public $incrementing = false;

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
