<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\User\Login;
use App\Http\Requests\API\User\RegisterClient;
use App\Http\Requests\API\User\RegisterContractor;
use App\Models\Client;
use App\Models\Company;
use App\Models\Contractor;
use App\Models\Menu;
use App\Models\Restaurant;
use App\Http\Controllers\Controller;
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

        return response()->json(['success' => $success], $this->successStatus);
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

        return response()->json(['success' => $success], $this->successStatus);
    }

    /**
     * Login API
     *
     * @param Login $request
     * @return JsonResponse
     */
    public function login(Login $request)
    {
        $sanitized = $request->validated();

        if(Auth::attempt([
            'email' => $sanitized['email'],
            'password' => $sanitized['password']
        ])) {
            $user = Auth::user();
            $success['token_type'] = 'Bearer';
            $success['token'] = $user->createToken('obedovac')->accessToken;
            $success['user'] = $user;
            return response()->json(['success' => $success], $this->successStatus);
        }
        else {
            $errors = [
                'credentials' => ['Nesprávne prihlasovacie údaje']
            ];
            return response()->json(['errors' => $errors], 401);
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
        Menu::create([
            'restaurant_id' => $restaurant->id
        ]);

        return Contractor::create([
            'restaurant_id' => $restaurant->id,
        ]);
    }
}
