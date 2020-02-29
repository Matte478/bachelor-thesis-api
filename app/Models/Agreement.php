<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agreement extends Model
{
    protected $fillable = [
        'company_id',
        'restaurant_id',
        'confirmed'
    ];

}
