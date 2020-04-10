<?php

namespace App\Http\Requests\API\Meal;

use Illuminate\Foundation\Http\FormRequest;

class StoreMeal extends FormRequest
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
            'meal' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'between:0,99.99'],
        ];
    }
}
