<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Client extends Model
{
    protected $fillable = [
        'company_id', 'type-of-employment_id'
    ];

    protected $hidden = [
        'id', 'created_at', 'updated_at'
    ];

    public function getContributionAttribute(): float
    {
        $typeOfEmployment = $this->typeOfEmployment;
        $contribution = $typeOfEmployment ? $typeOfEmployment->contribution : 0;

        return $contribution;
    }

    public function company(): belongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function typeOfEmployment(): belongsTo
    {
        return $this->belongsTo(TypeOfEmployment::class, 'type-of-employment_id');
    }
}
