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
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public $successStatus = 200;

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = auth()->user();
        $orders = Order::where('user_id', $user->id)->get();

        return response()->json(['data' => $orders], $this->successStatus);
    }

    /**
     * @param StoreOrder|Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreOrder $request)
    {
        $sanitized = $request->validated();

        $user = auth()->user();
        $company = Company::find($user->company_id);

        foreach($sanitized['orders'] as $data)
        {
            if($data['meal'] == null) continue;

            $meal = Meal::find($data['meal']);
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
