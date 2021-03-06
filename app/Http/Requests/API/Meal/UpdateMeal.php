<?php

namespace App\Http\Requests\API\Meal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateMeal extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('meal.edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'meal' => ['sometimes', 'string', 'max:255'],
            'price' => ['sometimes', 'numeric', 'between:0,99.99'],
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'meal.string' => 'Názov jedla je povinné pole.',
            'price.numeric' => 'Cena musí byť číslo.'
        ];
    }


}
