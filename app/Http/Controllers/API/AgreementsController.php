<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Agreement\ConfirmAgreement;
use App\Http\Requests\API\Agreement\CreateAgreement;
use App\Http\Requests\API\Agreement\IndexAgreement;
use App\Models\Agreement;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AgreementsController extends Controller
{
    public $successStatus = 200;

    /**
     * @param IndexAgreement $request
     * @return JsonResponse
     */
    public function index(IndexAgreement $request)
    {
        $user = auth()->user();
        $result = null;

        switch($user->type) {
            case 'client':
                $result = $this->clientIndex($user);
                break;
            case 'contractor':
                $result = $this->contractorIndex($user);
                break;
        }

        return response()->json(['data' => $result], $this->successStatus);
    }
    
    /**
     * @param CreateAgreement $request
     * @return JsonResponse
     */
    public function create(CreateAgreement $request)
    {
        $sanitized = $request->validated();

        $user = auth()->user();
        $companyId = $user->company_id;

        $agreement = Agreement::where('company_id', $companyId)
            ->where('restaurant_id', $sanitized['restaurant_id'])
            ->first();

        if($agreement)
            return response()->json(['error'=>'The agreement already exists'], 409);

        Agreement::create([
            'company_id' => $companyId,
            'restaurant_id' => $sanitized['restaurant_id'],
        ]);

        return response()->json(['success' => 'success'], $this->successStatus);
    }

    /**
     * @param Agreement $agreement
     * @param ConfirmAgreement $request
     * @return JsonResponse
     */
    public function confirm(ConfirmAgreement $request, Agreement $agreement)
    {
        $sanitized = $request->validated();

        $user = auth()->user();
        $restaurantId = $user->restaurant_id;

        if($agreement->restaurant_id != $restaurantId)
            return response()->json(['error'=>'Unauthorised'], 401);

        if($agreement->confirmed)
            return response()->json(['error' => 'The agreement has already been confirmed'], 409);

        $agreement->update([
            'confirmed' => true
        ]);

        return response()->json(['success' => 'success'], $this->successStatus);
    }

    private function clientIndex($user)
    {
        $companyId = $user->company_id;
        $agreement = Agreement::with('restaurant')
            ->where('company_id', $companyId)
            ->first();

        return $agreement;
    }

    private function contractorIndex($user)
    {
        $agreements = QueryBuilder::for(Agreement::class)
            ->allowedFilters([
                AllowedFilter::exact('confirmed')
            ])
            ->with('company')
            ->where('restaurant_id', $user->restaurant_id)
            ->get();

        return $agreements;
    }

}
