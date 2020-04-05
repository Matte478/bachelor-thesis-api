<?php

namespace App\Http\Controllers\API;

use App\Models\Client;
use App\Models\Company;
use App\Models\Contractor;
use App\Http\Requests\API\RegisterClient;
use App\Http\Requests\API\RegisterContractor;
use App\Models\Restaurant;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    public $successStatus = 200;


    /**
     * Register client API
     *
     * @param RegisterClient $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerClient(RegisterClient $request)
    {
        $sanitized = $request->validated();

        $client = $this->createClient($sanitized);

        $sanitized['password'] = bcrypt($sanitized['password']);
        $sanitized['typeable_id'] = $client->id;
        $sanitized['typeable_type'] = get_class($client);

        $user = User::create($sanitized);

        $success['token'] =  $user->createToken('obedovac')->accessToken;
        $success['name'] =  $user->name;

        return response()->json(['success'=>$success], $this->successStatus);
    }

    /**
     * Register contractor API
     *
     * @param RegisterContractor $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerContractor(RegisterContractor $request)
    {
        $sanitized = $request->validated();

        $contractor = $this->createContractor($sanitized);

        $sanitized['password'] = bcrypt($sanitized['password']);
        $sanitized['typeable_id'] = $contractor->id;
        $sanitized['typeable_type'] = get_class($contractor);

        $user = User::create($sanitized);

        $success['token_type'] = 'Bearer';
        $success['token'] = $user->createToken('obedovac')->accessToken;
        $success['name'] =  $user->name;

        return response()->json(['success'=>$success], $this->successStatus);
    }
    
    /**
     * Login API
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        if(Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            $user = Auth::user();
            $success['token_type'] = 'Bearer';
            $success['token'] = $user->createToken('obedovac')->accessToken;
            $success['user'] = $user;
            return response()->json(['success' => $success], $this->successStatus);
        }
        else {
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }

    /**
     * Logout API
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $user = auth()->user();

        $user->tokens->each(function($token, $key) {
           $token->delete();
        });

        return response()->json(['success' => 'Logged out'], $this->successStatus);
    }

    /**
     * Details API
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function details()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this->successStatus);
    }

    private function createClient($data) : Client
    {
        $company = Company::create($data);

        return Client::create([
            'company_id' => $company->id,
        ]);
    }

    private function createContractor($data) : Contractor
    {
        $restaurant = Restaurant::create($data);

        return Contractor::create([
            'restaurant_id' => $restaurant->id,
        ]);
    }
}
