<?php


namespace App\Repositories;


use App\Models\Order;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OrderRepository
{

    /**
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection|QueryBuilder|QueryBuilder[]
     */
    public function getClientOrders(int $userId)
    {
        $orders = QueryBuilder::for(Order::class)
            ->allowedFilters([
                AllowedFilter::scope('date_from'),
                AllowedFilter::scope('date_to'),
                AllowedFilter::exact('date')
            ])
            ->defaultSort('date')
            ->allowedSorts('id', 'date', 'price')
            ->where('user_id', $userId)
            ->get();

        return $orders;
    }
    
    /**
     * @param int $restaurantId
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|QueryBuilder|QueryBuilder[]
     */
    public function getContractorOrders(int $restaurantId)
    {
        $query = QueryBuilder::for(Order::class)
            ->allowedFilters([
                AllowedFilter::scope('date_from'),
                AllowedFilter::scope('date_to'),
                AllowedFilter::scope('company'),
                AllowedFilter::exact('date')
            ]);

        if( !$this->isJoined($query, 'companies') ) {
            $query->join('companies', 'companies.id', 'company_id');
        }

        $query->defaultSort('date')
            ->allowedSorts('id', 'date', 'price')
            ->where('restaurant_id', $restaurantId)
            ->select('meal_id', 'company_id', 'company', 'date');

        $orders = $query->get();
        $grouped = $orders->groupBy(['company', 'date', 'meal_id']);

        // map companies
        $result = $grouped->map(function($companies) {
            // map days
            return $companies->map(function($days) {
                // map orders
                return $days->map(function($orders) {
                    $count = collect($orders)->count();
                    $order = $orders[0];
                    $order['count'] = $count;
                    return $order;
                });
            });
        });

        return $result;
    }

    private function isJoined($query, $table)
    {
        $joins = $query->getQuery()->joins;
        if($joins == null) {
            return false;
        }

        foreach ($joins as $join) {
            if ($join->table == $table) {
                return true;
            }
        }

        return false;
    }
}