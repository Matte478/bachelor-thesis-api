<?php

namespace App\Http\Requests\API\Meal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreMeal extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('meal.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'meal' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'between:0,99.99'],
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'meal.required' => 'Názov jedla je povinné pole.',
            'price.required' => 'Cena je povinné pole.',
            'price.numeric' => 'Cena musí byť číslo.'
        ];
    }


}
