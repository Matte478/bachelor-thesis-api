<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Client extends Model
{
    protected $fillable = [
        'company_id'
    ];

    protected $hidden = [
        'id', 'created_at', 'updated_at'
    ];

    public function company() : belongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
