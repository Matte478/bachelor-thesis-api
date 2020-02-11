<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\storeMeal;
use App\Models\Contractor;
use App\Models\Meal;
use Illuminate\Http\Request;

class MenusController extends Controller
{
    public $successStatus = 200;

    public function index()
    {
        $user = auth()->user();
        $typeable = app($user->typeable_type)::find($user->typeable_id);

        if($user->typeable_type != 'App\Models\Contractor')
            return response()->json(['error'=>'Unauthorised'], 401);

        $restaurant = $typeable->restaurant;
        $menu = $restaurant->menu->first();
        $meal = $menu->meal->all();

        return response()->json(['data' => $meal], $this->successStatus);
    }

    public function addMeal(storeMeal $request)
    {
        $sanitized = $request->validated();

        $user = auth()->user();
        $typeable = app($user->typeable_type)::find($user->typeable_id);

        if($user->typeable_type != 'App\Models\Contractor')
            return response()->json(['error'=>'Unauthorised'], 401);

        $menu = $typeable->restaurant->menu->first();
        $sanitized['menu_id'] = $menu->id;

        $meal = Meal::create($sanitized);

        return response()->json(['data'=>$menu->meal->all()], 200);
    }
}
