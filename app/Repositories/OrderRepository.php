<?php


namespace App\Repositories;


use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OrderRepository
{

    /**
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection|QueryBuilder|QueryBuilder[]
     */
    public function getEmployeeOrders(int $userId)
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
     * @param int $userId
     * @param string $type
     * @return mixed|null
     */
    public function getClientOrders(int $userId, string $type = 'days')
    {
        $client = User::find($userId);

        $query = QueryBuilder::for(Order::class)
            ->allowedFilters([
                AllowedFilter::scope('employee'),
                AllowedFilter::scope('date_from'),
                AllowedFilter::scope('date_to'),
                AllowedFilter::exact('date')
            ]);

        if( !$this->isJoined($query, 'users') ) {
            $query->join('users', 'users.id', 'user_id');
        };

        $query->defaultSort('date', 'name')
            ->allowedSorts('id', 'date', 'name', 'price')
            ->where('company_id', $client->company_id)
            ->select('name', 'email', 'meal_id', 'meal', 'date', 'price', 'discount_price');

        $orders = $query->get();

        $grouped = $this->groupClientQueryResult($orders, $type);

        if($type == 'months')
            $result = $this->formatResult($grouped, 'client');
        else
            $result = $grouped;

        return $result;
    }

    /**
     * @param int $restaurantId
     * @param string $type
     * @return mixed
     */
    public function getContractorOrders(int $restaurantId, string $type = 'days')
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
            ->select('meal_id', 'meal', 'company_id', 'company', 'date', 'price', 'status');

        $orders = $query->get();

        $grouped = $this->groupContractorQueryResult($orders, $type);
        $result = $this->formatResult($grouped, 'contractor');

        return $result;
    }

    /**
     * @param $orders
     * @param string $type
     * @return mixed
     */
    private function groupContractorQueryResult($orders, $type = 'days')
    {
        $result = null;

        switch ($type) {
            case 'months':
                $result = $orders->groupBy([
                    'date' => function($d) {
                        return Carbon::parse($d->date)->format('yy-m');
                    },
                    'company' => 'company',
                    'meal_id' => 'meal_id'
                ]);
                break;
            case 'days':
            default:
                $result = $orders->groupBy(['date', 'company', 'meal_id']);
                break;
        }

        return $result;
    }

    private function groupClientQueryResult($orders, $type = 'days')
    {
        $result = null;

        switch ($type) {
            case 'months':
                $result = $orders->groupBy([
                    'date' => function($d) {
                        return Carbon::parse($d->date)->format('yy-m');
                    },
                    'name' => 'name',
                    'meal_id' => 'meal_id'
                ]);
                break;
            case 'days':
            default:
                $result = $orders->groupBy(['date']);
                break;
        }

        return $result;
    }

    /**
     * @param $array
     * @return mixed
     */
    private function formatResult($array, $type)
    {
        // map days or months
        return $array->map(function($days) use ($type) {
            // map companies or employees name
            return $days->map(function($companies) use ($type) {
                $status = null;
                // map orders
                $meals = $companies->map(function($orders) use (&$status) {
                    $count = collect($orders)->count();
                    $order = $orders[0];
                    $order['count'] = $count;

                    if(!$status)
                        $status = $order['status'];

                    unset($order['status']);

                    return $order;
                });

                $price = $this->calculateOrderPrice($meals);

                if($type == 'client')
                    $arr['discount_price'] = $price['discount_price'];
                else if($type == 'contractor')
                    $arr['status'] = $status;

                $arr['price'] = $price['price'];
                $arr['meals'] = $meals;

                return $arr;
            });
        });
    }


    /**
     * @param object $meals
     * @return array
     */
    private function calculateOrderPrice(object $meals): array
    {
        $price = 0;
        $discount = 0;

        foreach($meals as $meal) {
            $price += ($meal->count * $meal->price);
            $discount += ($meal->count * $meal->discount_price);
        }

        return [
            'price' => number_format($price, 2),
            'discount_price' => number_format($discount, 2)
        ];
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