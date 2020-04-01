<?php


namespace App\Repositories;


use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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
     * @return \Illuminate\Database\Eloquent\Collection|Collection|QueryBuilder|QueryBuilder[]
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
            ->select('meal_id', 'meal', 'company_id', 'company', 'date', 'price');

        $orders = $query->get();

        $grouped = $this->groupQueryResult($orders, 'day');
        $result = $this->formatResult($grouped);

        return $result;
    }

    /**
     * @param $orders
     * @param string $type
     * @return mixed
     */
    private function groupQueryResult($orders, $type = 'month')
    {
        $result = null;

        switch ($type) {
            case 'month':
                $result = $orders->groupBy([
                    'date' => function($d) {
                        return Carbon::parse($d->date)->format('yy-m');
                    },
                    'company' => 'company',
                    'meal_id' => 'meal_id'
                ]);
                break;
            case 'day':
                $result = $orders->groupBy(['date', 'company', 'meal_id']);
                break;
        }

        return $result;
    }

    /**
     * @param $array
     * @return mixed
     */
    private function formatResult($array)
    {
        // map days
        return $array->map(function($days) {
            // map companies
            return $days->map(function($companies) {
                // map orders
                $meals = $companies->map(function($orders) {
                    $count = collect($orders)->count();
                    $order = $orders[0];
                    $order['count'] = $count;
                    return $order;
                });
                $price = $this->calculateOrderPrice($meals);
                $arr = [
                    'price' => $price,
                    'meals' => $meals
                ];
                return $arr;
            });
        });
    }

    /**
     * @param $meals
     * @return float
     */
    private function calculateOrderPrice($meals): float
    {
        $price = 0;

        foreach($meals as $meal) {
            $price += ($meal->count * $meal->price);
        }

        return number_format($price, 2);
    }

    /**
     * @param $query
     * @param $table
     * @return bool
     */
    private function isJoined($query, $table): bool
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