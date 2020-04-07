<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\API\User\RegisterClient;
use App\Http\Requests\API\User\RegisterClientEmployee;
use App\Http\Requests\API\User\RegisterContractor;
use App\Http\Requests\API\User\UpdateClientEmployee;
use App\Models\Client;
use App\Models\Company;
use App\Models\Contractor;
use App\Models\Restaurant;
use App\Http\Controllers\Controller;
use App\Models\TypeOfEmployment;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

        return response()->json(['success' => 'success'], $this->successStatus);
    }

    /**
     * Get client employee API
     *
     * @param $employee
     * @return JsonResponse
     */
    public function getClientEmployee($employee)
    {
        $user = User::find($employee);
        $client = app($user->typeable_type)::find($user->typeable_id);

        $result = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ];

        $typeOfEmployment = $client->typeOfEmployment;

        if($typeOfEmployment) {
            $result['type-of-employment_id'] = $typeOfEmployment->id;
            $result['type-of-employment'] = $typeOfEmployment->name;
        }

        return response()->json(['data' => $result], $this->successStatus);
    }

    /**
     * Update client employee API
     *
     * @param UpdateClientEmployee $request
     * @param $employee
     * @return JsonResponse
     */
    public function updateClientEmployee(UpdateClientEmployee $request, $employee)
    {
        $sanitized = $request->validated();

        $logIn = auth()->user();

        if(isset($sanitized['password']))
            $sanitized['password'] = bcrypt($sanitized['password']);

        if(isset($sanitized['type-of-employment_id'])) {
            $typeOfEmployment = TypeOfEmployment::find($sanitized['type-of-employment_id']);
            if($typeOfEmployment->company_id != $logIn->company_id) {
                $sanitized['type-of-employment_id'] = null;
            }
        }

        $eventDispatcher = User::getEventDispatcher();
        $eventDispatcher->forget('eloquent.retrieved: App\Models\User');

        $user = User::find($employee);
        $user->update($sanitized);

        $employee = app($user->typeable_type)::find($user->typeable_id);
        $employee->update($sanitized);

        User::observe(UserObserver::class);

        return response()->json(['success' => $user], $this->successStatus);
    }

    public function destroyClientEmployee($employee)
    {
        $logIn = auth()->user();

        $employee = User::find($employee);

        if($logIn->company_id != $employee->company_id)
            return response()->json(['error' => 'Unauthorised'], 401);

        $typeable = app($employee->typeable_type)::find($employee->typeable_id);

        $employee->delete();
        $typeable->delete();

        return response()->json(['success' => 'success'], $this->successStatus);
    }

    /**
     * Get employees
     *
     * @return JsonResponse
     */
    public function employees()
    {
        $user = auth()->user();
        $typeable = app($user->typeable_type)::find($user->typeable_id);

        $employees = [];
        switch ($user->type) {
            case 'client':
                $employees = $this->getClientEmployees($typeable);
                break;
        }

        return response()->json(['data' => $employees], $this->successStatus);
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
            return response()->json(['error' => 'Unauthorised'], 401);
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

    private function getClientEmployees($client): Object
    {
        return Client::where('clients.company_id', $client->company_id)
            ->join('users', 'users.typeable_id', 'clients.id')
            ->leftJoin('type_of_employments', 'type_of_employments.id', 'clients.type-of-employment_id')
            ->where('typeable_type', 'like', '%Client')
            ->orderBy('users.id', 'asc')
            ->select(
                'users.name as name',
                'users.email as email',
                'type_of_employments.id as type-of-employment_id',
                'type_of_employments.name as type-of-employment',
                'users.id as user_id'
            )
            ->get();
    }
}
