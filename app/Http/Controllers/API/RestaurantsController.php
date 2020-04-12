<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Restaurant\IndexRestaurant;
use App\Models\Company;
use App\Models\Restaurant;

class RestaurantsController extends Controller
{
    public $successStatus = 200;

    public function index(IndexRestaurant $restaurant)
    {
        $user = Auth()->user();
        $company = Company::find($user->company_id)->first();
        $city = $company->city;

        $restaurants = Restaurant::where('city', 'ilike', $city)->get();

        return response()->json(['data' => $restaurants], $this->successStatus);
    }
}
