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

    public function getContractorOrders(int $userId)
    {
//        $orders = QueryBuilder::for(Order::class)
//            ->allowedFilters([
//                AllowedFilter::scope('date_from'),
//                AllowedFilter::scope('date_to'),
//                AllowedFilter::exact('date')
//            ])
//            ->with([
//                'user.typeable.company' => function($query) {
//                    $query->select(['id', 'company']);
//                },
//            ])
//            ->defaultSort('date')
//            ->allowedSorts('id', 'date', 'price')
//            ->where('restaurant_id', $userId)
//            ->get();


        $orders = QueryBuilder::for(Order::class)
            ->allowedFilters([
                AllowedFilter::scope('date_from'),
                AllowedFilter::scope('date_to'),
                AllowedFilter::exact('date')
            ])
            ->with([
                'company'
            ])
            ->defaultSort('date')
            ->allowedSorts('id', 'date', 'price')
            ->where('restaurant_id', $userId)
            ->get();

//        $grouped = $orders->groupBy(['user.typeable.company.company', 'meal_id']);

        return $orders->first()->company();
//        return $grouped;
    }
}