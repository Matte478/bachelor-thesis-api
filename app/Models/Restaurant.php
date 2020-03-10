<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    protected $fillable = [
        'restaurant', 'city'
    ];

    public function contractor(): hasMany
    {
        return $this->hasMany(Contractor::class);
    }

    public function menu(): hasMany
    {
        return $this->hasMany(Menu::class);
    }

    public function agreement(): hasMany
    {
        return $this->hasMany(Agreement::class);
    }
}
