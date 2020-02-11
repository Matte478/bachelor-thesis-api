<?php

namespace App\Http\Requests\API\Meal;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMeal extends FormRequest
{
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
            'meal' => ['sometimes', 'string', 'max:255'],
            'price' => ['sometimes', 'numeric', 'between:0,99.99'],
        ];
    }
}
