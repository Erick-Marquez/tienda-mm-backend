<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Summary extends Model
{
    use HasFactory;

    protected $fillable = [

        'date_issue',
        'date_of_reference',

        'type',
        'identifier',
        'ticket',

        'sunat_state',
        'sunat_code',
        'sunat_notes',

        'sunat_path_xml',
        'sunat_path_cdr',
        'sunat_filename',

        'user_id'

    ];

    public function sales()
    {
        return $this->belongsToMany(Sales::class, 'summary_details', 'summary_id', 'sale_id');
    }

}
