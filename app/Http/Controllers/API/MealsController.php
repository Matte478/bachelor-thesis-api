<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Meal\DestroyMeal;
use App\Http\Requests\API\Meal\storeMeal;
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
     * @return JsonResponse
     */
    public function index()
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
        $meal = $menu->meal()->orderBy('id')->get();

        return response()->json(['data' => $meal], $this->successStatus);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param storeMeal $request
     * @return JsonResponse
     */
    public function store(storeMeal $request)
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
     * @param Meal $meal
     * @return JsonResponse
     */
    public function show(Meal $meal)
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
