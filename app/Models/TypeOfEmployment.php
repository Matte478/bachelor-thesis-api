<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TypeOfEmployment extends Model
{
    protected $fillable = [
        'name', 'contribution', 'company_id'
    ];

    public $timestamps = false;

    public function company(): belongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
