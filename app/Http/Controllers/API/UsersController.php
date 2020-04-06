<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\User\RegisterClient;
use App\Http\Requests\API\User\RegisterClientEmployee;
use App\Http\Requests\API\User\RegisterContractor;
use App\Models\Client;
use App\Models\Company;
use App\Models\Contractor;
use App\Models\Restaurant;
use App\Http\Controllers\Controller;
use App\Models\TypeOfEmployment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    public $successStatus = 200;


    /**
     * Register client API
     *
     * @param RegisterClient $request
     * @return JsonResponse
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
     * Register client employee API
     *
     * @param RegisterClientEmployee $request
     * @return JsonResponse
     */
    public function registerClientEmployee(RegisterClientEmployee $request)
    {
        $sanitized = $request->validated();

        $user = auth()->user();
        $client = app($user->typeable_type)::find($user->typeable_id);
        $sanitized['company_id'] = $client->company_id;

        if(isset($sanitized['type-of-employment_id'])) {
            $typeOfEmployment = TypeOfEmployment::find($sanitized['type-of-employment_id']);
            if($typeOfEmployment->company_id != $sanitized['company_id']) {
                $sanitized['type-of-employment_id'] = null;
            }
        }

        $client = Client::create($sanitized);

        $sanitized['password'] = bcrypt($sanitized['password']);
        $sanitized['typeable_id'] = $client->id;
        $sanitized['typeable_type'] = get_class($client);

        User::create($sanitized);

        return response()->json(['success'=>'success'], $this->successStatus);
    }

    /**
     * Register contractor API
     *
     * @param RegisterContractor $request
     * @return JsonResponse
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
     * @return JsonResponse
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
     * @return JsonResponse
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
     * @return JsonResponse
     */
    public function details()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this->successStatus);
    }

    private function createClient($data): Client
    {
        $company = Company::create($data);

        return Client::create([
            'company_id' => $company->id,
        ]);
    }

    private function createContractor($data): Contractor
    {
        $restaurant = Restaurant::create($data);

        return Contractor::create([
            'restaurant_id' => $restaurant->id,
        ]);
    }
}
