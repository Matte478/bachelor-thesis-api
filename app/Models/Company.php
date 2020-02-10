<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'company', 'city'
    ];

    public function client() : hasMany
    {
        return $this->hasMany(Client::class);
    }
}
