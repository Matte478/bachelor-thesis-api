<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agreement extends Model
{
    protected $fillable = [
        'company_id',
        'restaurant_id',
        'confirmed'
    ];

    public function restaurant() : belongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function company() : belongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
