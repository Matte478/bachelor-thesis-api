<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Order\EmployeeOrder;
use App\Http\Requests\API\Order\IndexOrder;
use App\Http\Requests\API\Order\StatusOrder;
use App\Http\Requests\API\Order\StoreOrder;
use App\Models\Agreement;
use App\Models\Company;
use App\Models\Meal;
use App\Models\Order;
use App\Repositories\OrderRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;

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
     * @param IndexOrder $request
     * @param string $type
     * @return JsonResponse
     */
    public function index(IndexOrder $request, $type = 'days')
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
     * @param EmployeeOrder $request
     * @return JsonResponse
     */
    public function employee(EmployeeOrder $request)
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
        $client = app($user->typeable_type)::find($user->typeable_id);
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
                'discount_price' => number_format($meal->price - $client->contribution, 2),
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

    /**
     * @param StatusOrder $request
     * @param $company
     * @param $date
     * @return JsonResponse
     */
    public function status(StatusOrder $request, $company, $date)
    {
        $sanitized = $request->validated();

        $company = Company::where('company', 'ilike', $company)->first();
        $agreement = null;
        $today = Carbon::parse($date)->isToday();

        $user = auth()->user();
        $contractor = app($user->typeable_type)::find($user->typeable_id);

        if($company) {
            $agreement = Agreement::where('company_id', $company->id)
                ->where('restaurant_id', $contractor->restaurant_id)
                ->where('confirmed', true)
                ->first();
        }

        if(!$company || !$agreement || !$today)
            return response()->json(['message' => 'Forbidden'], 403);

        Order::where('company_id', $company->id)
            ->where('date', $date)
            ->update(['status' => $sanitized['status']]);

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
