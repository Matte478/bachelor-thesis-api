<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Order\StoreOrder;
use App\Models\Agreement;
use App\Models\Company;
use App\Models\Meal;
use App\Models\Order;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OrderController extends Controller
{
    public $successStatus = 200;

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = auth()->user();

        $orders = QueryBuilder::for(Order::class)
            ->allowedFilters([
                AllowedFilter::scope('date_from'),
                AllowedFilter::scope('date_to'),
            ])
            ->where('user_id', $user->id)
            ->orderBy('date')
            ->get();

        return response()->json(['data' => $orders], $this->successStatus);
    }


    /**
     * @param StoreOrder $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreOrder $request)
    {
        $sanitized = $request->validated();
        $orders = isset($sanitized['orders']) ? $sanitized['orders'] : [];

        $user = auth()->user();
        $company = Company::find($user->company_id);

        foreach($orders as $data)
        {
            if($data['meal_id'] == null) continue;

            $meal = Meal::find($data['meal_id']);
            $restaurant = $meal->restaurant()->first();

            $agreement = Agreement::where('company_id', $company->id)
                ->where('restaurant_id', $restaurant->id)
                ->first();

            if(!$agreement || !$agreement->confirmed)
                continue;

            $fullData = [
                'meal_id' => $meal->id,
                'meal' => $meal->meal,
                'price' => $meal->price,
                'discount_price' => $meal->price,
                'user_id' => $user->id,
                'restaurant_id' => $restaurant->id,
                'date' => $data['date']
            ];

            Order::updateOrCreate(
                ['user_id' => $user->id, 'date' => $data['date']],
                $fullData
            );
        }

        return response()->json(['success' => 'success'], $this->successStatus);
    }
}
