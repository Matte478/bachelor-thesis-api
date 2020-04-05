<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'company', 'city'
    ];

    public function client(): hasMany
    {
        return $this->hasMany(Client::class);
    }

    public function typeOfEmployments(): hasMany
    {
        return $this->hasMany(TypeOfEmployment::class);
    }

    public function agreement(): belongsTo
    {
        return $this->belongsTo(Agreement::class);
    }

    public function contractor(): Restaurant
    {
//        TODO: refactor
//        $agreement = $this->agreement()->first();
        $agreement = Agreement::where('company_id', $this->id)
                                ->where('confirmed', true)
                                ->first();

        return $agreement->restaurant()->first();
    }
}
