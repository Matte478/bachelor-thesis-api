<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meal extends Model
{
    protected $fillable = [
        'meal', 'price', 'menu_id'
    ];

    public function menu() : belongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function meal() : hasMany
    {
        return $this->hasMany(Meal::class);
    }
}
