<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Order\StoreOrder;
use App\Models\Agreement;
use App\Models\Company;
use App\Models\Meal;
use App\Models\Order;
use App\Repositories\OrderRepository;
use Illuminate\Http\JsonResponse;

class OrdersController extends Controller
{
    protected $orderRepository;
    public $successStatus = 200;

    /**
     * OrderController constructor.
     * @param OrderRepository $orderRepository
     */
    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }


    /**
     * @param string $type
     * @return JsonResponse
     */
    public function index($type = 'days')
    {
        $user = auth()->user();
        $result = null;

        if($type != 'days' && $type != 'months')
            $type = 'days';

        switch($user->type) {
            case 'client':
                $result = $this->clientIndex($user, $type);
                break;
            case 'contractor':
                $result = $this->contractorIndex($user, $type);
                break;
        }

        return response()->json(['data' => $result], $this->successStatus);
    }

    /**
     * @return JsonResponse
     */
    public function employee()
    {
        $user = auth()->user();
        $orders = $this->orderRepository->getEmployeeOrders($user->id);

        return response()->json(['data' => $orders], $this->successStatus);
    }

    /**
     * @param StoreOrder $request
     * @return JsonResponse
     */
    public function store(StoreOrder $request)
    {
        $sanitized = $request->validated();
        $orders = isset($sanitized['orders']) ? $sanitized['orders'] : [];

        $user = auth()->user();
        $company = Company::find($user->company_id);

        foreach($orders as $data)
        {
            if($data['meal_id'] == null) continue;

            $meal = Meal::find($data['meal_id']);
            $restaurant = $meal->restaurant()->first();

            $agreement = Agreement::where('company_id', $company->id)
                ->where('restaurant_id', $restaurant->id)
                ->first();

            if(!$agreement || !$agreement->confirmed)
                continue;

            $fullData = [
                'meal_id' => $meal->id,
                'meal' => $meal->meal,
                'price' => $meal->price,
                'discount_price' => $meal->price,
                'user_id' => $user->id,
                'restaurant_id' => $restaurant->id,
                'company_id' => $company->id,
                'date' => $data['date']
            ];

            Order::updateOrCreate(
                ['user_id' => $user->id, 'date' => $data['date']],
                $fullData
            );
        }

        return response()->json(['success' => 'success'], $this->successStatus);
    }

    private function clientIndex($user, $type)
    {
        $orders = $this->orderRepository->getClientOrders($user->id, $type);

        return $orders;
    }

    private function contractorIndex($user, $type)
    {
        $contractor = app($user->typeable_type)::find($user->typeable_id);
        $orders = $this->orderRepository->getContractorOrders($contractor->restaurant_id, $type);

        return $orders;
    }
}
