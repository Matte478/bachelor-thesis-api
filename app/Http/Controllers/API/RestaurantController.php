<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RestaurantController extends Controller
{
    public $successStatus = 200;

    public function index()
    {
        $user = Auth()->user();
        $company = Company::find($user->company_id)->first();
        $city = $company->city;

        $restaurants = Restaurant::where('city', 'ilike', $city)->get();

        return response()->json(['data' => $restaurants], $this->successStatus);
    }
}
