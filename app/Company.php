<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'company', 'city'
    ];

    public function client()
    {
        return $this->hasMany(Client::class);
    }
}
