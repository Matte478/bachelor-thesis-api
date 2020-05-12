<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meal extends Model
{
    protected $fillable = [
        'meal', 'price', 'menu_id'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function menu(): belongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function restaurant(): belongsTo
    {
        return $this->menu->restaurant();
    }
}
