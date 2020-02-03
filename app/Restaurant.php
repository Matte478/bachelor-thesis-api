<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    protected $fillable = [
        'restaurant', 'city'
    ];

    public function contractor() : hasMany
    {
        return $this->hasMany(Contractor::class);
    }
}
