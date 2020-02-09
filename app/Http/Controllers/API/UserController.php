<?php

namespace App\Http\Controllers\API;

use App\Client;
use App\Company;
use App\Contractor;
use App\Restaurant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Validator;

class UserController extends Controller
{
    public $successStatus = 200;


    /**
     * Login api
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(){
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
            $user = Auth::user();
            $success['token'] =  $user->createToken('obedovac')->accessToken;
            return response()->json(['success' => $success], $this->successStatus);
        }
        else{
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }

    /**
     * Register api
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'type' => ['required', 'regex:(client|contractor)']
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }

        $data = $request->all();
        $typeable = null;

        if($data['type'] == 'client') {
            $typeable = $this->createClient($request);
        } else {
            $typeable = $this->createContractor($request);
        }

        // TODO refactor
        if(!$typeable instanceof Client && !$typeable instanceof Contractor) {
            return $typeable;
        }

        $data['password'] = bcrypt($data['password']);
        $data['typeable_id'] = $typeable->id;
        $data['typeable_type'] = get_class($typeable);
        $user = User::create($data);

        $success['token'] =  $user->createToken('obedovac')->accessToken;
        $success['name'] =  $user->name;

        return response()->json(['success'=>$success], $this->successStatus);
    }


    /**
     * Details api
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function details()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this-> successStatus);
    }

    private function createClient(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company' => 'required|unique:companies',
            'city' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }

        $data = $request->all();
        $company = Company::create($data);

        return Client::create([
            'company_id' => $company->id,
        ]);
    }

    private function createContractor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'restaurant' => 'required|unique:restaurants',
            'city' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }

        $data = $request->all();
        $restaurant = Restaurant::create($data);

        return Contractor::create([
            'restaurant_id' => $restaurant->id,
        ]);
    }
}
