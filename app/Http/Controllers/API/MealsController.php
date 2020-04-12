<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Meal\DestroyMeal;
use App\Http\Requests\API\Meal\IndexMeal;
use App\Http\Requests\API\Meal\ShowMeal;
use App\Http\Requests\API\Meal\StoreMeal;
use App\Http\Requests\API\Meal\UpdateMeal;
use App\Models\Meal;
use Exception;
use Illuminate\Http\JsonResponse;

class MealsController extends Controller
{
    public $successStatus = 200;


    /**
     * Return a listing of the resource.
     *
     * @param IndexMeal $request
     * @return JsonResponse
     */
    public function index(IndexMeal $request)
    {
        $user = auth()->user();
        $typeable = app($user->typeable_type)::find($user->typeable_id);

        if($user->typeable_type != 'App\Models\Contractor')
            $restaurant = $typeable->company->contractor();
        else
            $restaurant = $typeable->restaurant()->first();

        if(!$restaurant)
            return response()->json(['error' => 'Unauthorised'], 401);

        $menu = $restaurant->menu->first();
        $meals = $menu->meal()->orderBy('id')->get();

        if($user->type == 'client') {
            $discount = $typeable->contribution;

            foreach ($meals as &$meal) {
                $discoutPrice = number_format($meal->price - $discount, 2);
                $meal->discount_price = $discoutPrice > 0 ? $discoutPrice : 0;
            }
        }

        return response()->json(['data' => $meals], $this->successStatus);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreMeal $request
     * @return JsonResponse
     */
    public function store(StoreMeal $request)
    {
        $sanitized = $request->validated();

        $user = auth()->user();
        $typeable = app($user->typeable_type)::find($user->typeable_id);

        if($user->typeable_type != 'App\Models\Contractor')
            return response()->json(['error'=>'Unauthorised'], 401);

        $menu = $typeable->restaurant->menu->first();
        $sanitized['menu_id'] = $menu->id;

        $meal = Meal::create($sanitized);

        return response()->json(['data' => $meal], $this->successStatus);
    }

    /**
     * Display the specified resource.
     *
     * @param ShowMeal $request
     * @param Meal $meal
     * @return JsonResponse
     */
    public function show(ShowMeal $request, Meal $meal)
    {
        return response()->json(['data' => $meal], $this->successStatus);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateMeal $request
     * @param Meal $meal
     * @return JsonResponse
     */
    public function update(UpdateMeal $request, Meal $meal)
    {
        $sanitized = $request->validated();

        $meal->update($sanitized);

        return response()->json(['data' => $meal], $this->successStatus);
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param DestroyMeal $request
     * @param Meal $meal
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(DestroyMeal $request, Meal $meal)
    {
        $user = auth()->user();
        $typeable = app($user->typeable_type)::find($user->typeable_id);

        if($user->typeable_type != 'App\Models\Contractor')
            return response()->json(['error'=>'Unauthorised'], 401);

        $meal->delete();

        $restaurant = $typeable->restaurant;
        $menu = $restaurant->menu->first();
        $meals = $menu->meal->all();

        return response()->json(['data' => $meals], $this->successStatus);
    }
}
