<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
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

        if($user->typeable_type == 'App\Models\Contractor') {
            $restaurant = $typeable->restaurant;
            $menu = $restaurant->menu->first();
            $meal = $menu->meal->all();

            return response()->json(['data' => $meal], $this->successStatus);
        } else if($user->typeable_type == 'App\Models\Client') {
            //
        }
    }
}
