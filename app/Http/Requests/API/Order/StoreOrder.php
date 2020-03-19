<?php

namespace App\Http\Requests\API\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrder extends FormRequest
{
    /**
     * Prepare the data for validation.
     *
     */
    protected function prepareForValidation()
    {
        $orders = array_filter($this->get('orders'), function($item) {
            return strtotime($item['date']) > strtotime('now');
        });

        $this->merge([
            'orders' => $orders
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'orders.*.date' => ['required', 'date_format:Y-m-d'],
            'orders.*.meal' => []
        ];
    }
}
