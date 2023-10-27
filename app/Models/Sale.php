<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [

        'document_number',

        'date_issue',
        'date_due',

        'global_discount',
        'item_discount',
        'total_discount',

        'subtotal',
        'total_igv',
        'total_exonerated',
        'total_unaffected',
        'total_free',
        'total_taxed',
        'total',

        'observation',
        'received_money',
        'change',

        'sunat_state',
        'sunat_code',
        'sunat_notes',

        'sunat_path_xml',
        'sunat_path_cdr',
        'sunat_filename',

        'type_of_payment_id',
        'serie_id',
        'customer_id',
        'user_id'

    ];

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function serie()
    {
        return $this->belongsTo(Serie::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
