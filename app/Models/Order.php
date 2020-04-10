<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Order extends Model
{
    protected $fillable = [
        'meal_id', 'meal', 'price', 'discount_price', 'date', 'user_id', 'restaurant_id', 'company_id'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }

    public function scopeDateFrom(Builder $query, $date): Builder
    {
        return $query->where('date', '>=', Carbon::parse($date));
    }

    public function scopeDateTo(Builder $query, $date): Builder
    {
        return $query->where('date', '<=', Carbon::parse($date));
    }

    public function scopeCompany(Builder $query, ...$company): Builder
    {
        return $query->join('companies', 'companies.id', 'company_id')
                    ->whereIn('companies.company', $company);
    }

    public function scopeEmployee(Builder $query, ...$employee): Builder
    {
        return $query->join('users', 'users.id', 'user_id')
            ->whereIn('users.name', $employee);
    }
}
