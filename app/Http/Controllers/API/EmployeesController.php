<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Employee\DestroyEmployee;
use App\Http\Requests\API\Employee\IndexEmployee;
use App\Http\Requests\API\Employee\RegisterEmployee;
use App\Http\Requests\API\Employee\ShowEmployee;
use App\Http\Requests\API\Employee\UpdateEmployee;
use App\Models\Client;
use App\Models\TypeOfEmployment;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Http\JsonResponse;

class EmployeesController extends Controller
{
    public $successStatus = 200;

    /**
     * Get employees
     *
     * @param IndexEmployee $request
     * @return JsonResponse
     */
    public function index(IndexEmployee $request)
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
     * Register client employee API
     *
     * @param RegisterEmployee $request
     * @return JsonResponse
     */
    public function register(RegisterEmployee $request)
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

        $user = User::create($sanitized);
        $result = $this->getClientEmployeeArray($user->id);

        return response()->json(['data' => $result], $this->successStatus);
    }

    /**
     * Get client employee API
     *
     * @param ShowEmployee $request
     * @param $employee
     * @return JsonResponse
     */
    public function show(ShowEmployee $request, $employee)
    {
        $result = $this->getClientEmployeeArray($employee);

        return response()->json(['data' => $result], $this->successStatus);
    }

    /**
     * @param UpdateEmployee $request
     * @param $employee
     * @return JsonResponse
     */
    public function update(UpdateEmployee $request, $employee)
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

    /**
     * @param DestroyEmployee $request
     * @param $employee
     * @return JsonResponse
     */
    public function destroy(DestroyEmployee $request, $employee)
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

    private function getClientEmployeeArray($employee): array
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

        return $result;
    }
}
