<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogueItem extends Model
{
    protected $fillable = [
        'name',
        'typical_amount',
        'logo_url',
        'category',
    ];

    protected $casts = [
        'typical_amount' => 'decimal:2',
    ];
}
