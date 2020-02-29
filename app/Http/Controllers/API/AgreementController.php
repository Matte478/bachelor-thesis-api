<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Agreement\ConfirmAgreement;
use App\Http\Requests\API\Agreement\CreateAgreement;
use App\Http\Requests\API\Agreement\IndexAgreement;
use App\Models\Agreement;
use Illuminate\Http\Request;

class AgreementController extends Controller
{
    public $successStatus = 200;

    /**
     * @param IndexAgreement $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(IndexAgreement $request)
    {
        $user = auth()->user();
        $restaurantId = $user->restaurant_id;

        // TODO filter for confirmed / unconfirmed agreements
        $agreements = Agreement::where('restaurant_id', $restaurantId)->get();

        return response()->json(['data' => $agreements], $this->successStatus);
    }
    
    /**
     * @param CreateAgreement $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(CreateAgreement $request)
    {
        $sanitized = $request->validated();

        $user = auth()->user();
        $companyId = $user->company_id;

        $agreement = Agreement::where('company_id', $companyId)
            ->where('restaurant_id', $sanitized['restaurant_id']);

        if($agreement)
            return response()->json(['error'=>'The agreement already exists'], 401);

        Agreement::create([
            'company_id' => $companyId,
            'restaurant_id' => $sanitized['restaurant_id'],
        ]);

        return response()->json(['success' => 'success'], $this->successStatus);
    }

    /**
     * @param Agreement $agreement
     * @param ConfirmAgreement $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirm(ConfirmAgreement $request, Agreement $agreement)
    {
        $sanitized = $request->validated();

        $user = auth()->user();
        $restaurantId = $user->restaurant_id;

        if($agreement->restaurant_id != $restaurantId)
            return response()->json(['error'=>'Unauthorised'], 401);

        if($agreement->confirmed)
            return response()->json(['error'=>'The agreement has already been confirmed'], 401);

        $agreement->update([
            'confirmed' => true
        ]);

        return response()->json(['success' => 'success'], $this->successStatus);
    }

}
