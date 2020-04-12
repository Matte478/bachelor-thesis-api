<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\TypeOfEmployment\DestroyTypeOfEmployment;
use App\Http\Requests\API\TypeOfEmployment\IndexTypeOfEmployment;
use App\Http\Requests\API\TypeOfEmployment\ShowTypeOfEmployment;
use App\Http\Requests\API\TypeOfEmployment\StoreTypeOfEmployment;
use App\Http\Requests\API\TypeOfEmployment\UpdateTypeOfEmployment;
use App\Models\TypeOfEmployment;
use Exception;
use Illuminate\Http\JsonResponse;

class TypeOfEmploymentsController extends Controller
{
    public $successStatus = 200;

    /**
     * Return a listing of the resource.
     *
     * @param IndexTypeOfEmployment $request
     * @return JsonResponse
     */
    public function index(IndexTypeOfEmployment $request)
    {
        $request->validated();

        $user = auth()->user();
        $client = app($user->typeable_type)::find($user->typeable_id);
        $typeOfEmployments = $client->company->typeOfEmployments()->orderBy('id')->get();

        return response()->json(['data' => $typeOfEmployments], $this->successStatus);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreTypeOfEmployment $request
     * @return JsonResponse
     */
    public function store(StoreTypeOfEmployment $request)
    {
        $sanitized = $request->validated();

        $user = auth()->user();
        $client = app($user->typeable_type)::find($user->typeable_id);
        $sanitized['company_id'] = $client->company_id;

        $typeOfEmployment = TypeOfEmployment::create($sanitized);

        return response()->json(['data' => $typeOfEmployment], $this->successStatus);
    }

    /**
     * Display the specified resource.
     *
     * @param ShowTypeOfEmployment $request
     * @param TypeOfEmployment $typeOfEmployment
     * @return JsonResponse
     */
    public function show(ShowTypeOfEmployment $request, TypeOfEmployment $typeOfEmployment)
    {
        $sanitized = $request->validated();

        $user = auth()->user();
        $client = app($user->typeable_type)::find($user->typeable_id);
        $companyId = $client->company_id;

        if($typeOfEmployment->company_id != $companyId)
            return response()->json(['error' => 'Unauthorised'], 401);

        return response()->json(['data' => $typeOfEmployment], $this->successStatus);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTypeOfEmployment $request
     * @param TypeOfEmployment $typeOfEmployment
     * @return JsonResponse
     */
    public function update(UpdateTypeOfEmployment $request, TypeOfEmployment $typeOfEmployment)
    {
        $sanitized = $request->validated();

        $user = auth()->user();
        $client = app($user->typeable_type)::find($user->typeable_id);
        $companyId = $client->company_id;

        if($typeOfEmployment->company_id != $companyId)
            return response()->json(['error' => 'Unauthorised'], 401);

        $typeOfEmployment->update($sanitized);

        return response()->json(['data' => $typeOfEmployment], $this->successStatus);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DestroyTypeOfEmployment $request
     * @param TypeOfEmployment $typeOfEmployment
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(DestroyTypeOfEmployment $request, TypeOfEmployment $typeOfEmployment)
    {
        $request->validated();

        $user = auth()->user();
        $client = app($user->typeable_type)::find($user->typeable_id);
        $company = $client->company;

        if($typeOfEmployment->company_id != $company->id)
            return response()->json(['error' => 'Unauthorised'], 401);

        $typeOfEmployment->delete();

        $typeOfEmployments = $company->typeOfEmployments;

        return response()->json(['data' => $typeOfEmployments], $this->successStatus);
    }
}
