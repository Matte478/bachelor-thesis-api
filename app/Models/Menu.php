<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'restaurant_id'
    ];

    public function restaurant() : belongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function meal() : hasMany
    {
        return $this->hasMany(Meal::class);
    }
}
