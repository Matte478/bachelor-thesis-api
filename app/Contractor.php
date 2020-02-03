<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contractor extends Model
{
    protected $fillable = [
        'restaurant_id'
    ];

    protected $hidden = [
        'id', 'created_at', 'updated_at'
    ];

    public function restaurant() : belongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
