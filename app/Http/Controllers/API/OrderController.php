<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Order\StoreOrder;
use App\Http\Requests\API\Order\UpdateOrder;
use App\Models\Agreement;
use App\Models\Company;
use App\Models\Meal;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public $successStatus = 200;


    /**
     * @param StoreOrder $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreOrder $request)
    {
        $sanitized = $request->validated();

        $user = auth()->user();
        $company = Company::find($user->company_id);

        $meal = Meal::find($sanitized['meal_id']);
        $restaurant = $meal->restaurant()->first();

        $agreement = Agreement::where('company_id', $company->id)
                                ->where('restaurant_id', $restaurant->id)
                                ->first();

        if(!$agreement || !$agreement->confirmed)
            return response()->json(['error' => 'Unauthorised'], 401);

        $sanitized['meal'] = $meal->meal;
        $sanitized['price'] = $meal->price;
        $sanitized['discount_price'] = $meal->price;
        $sanitized['user_id'] = $user->id;
        $sanitized['restaurant_id'] = $restaurant->id;

        Order::create($sanitized);

        return response()->json(['success' => 'success'], $this->successStatus);
    }

    public function update(UpdateOrder $request)
    {
        $sanitized = $request->validated();

        $user = auth()->user();
        $company = Company::find($user->company_id);

        $meal = Meal::find($sanitized['meal_id']);
        $restaurant = $meal->restaurant()->first();

        $agreement = Agreement::where('company_id', $company->id)
            ->where('restaurant_id', $restaurant->id)
            ->first();

        if(!$agreement || !$agreement->confirmed)
            return response()->json(['error' => 'Unauthorised'], 401);

        $sanitized['meal'] = $meal->meal;
        $sanitized['price'] = $meal->price;
        $sanitized['discount_price'] = $meal->price;
        $sanitized['user_id'] = $user->id;
        $sanitized['restaurant_id'] = $restaurant->id;

        Order::create($sanitized);

        return response()->json(['success' => 'success'], $this->successStatus);
    }
}
