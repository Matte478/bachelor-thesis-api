<?php

namespace App\Http\Requests\API\TypeOfEmployment;

use Illuminate\Foundation\Http\FormRequest;

class StoreTypeOfEmployment extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'contribution' => ['required', 'numeric'],
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'Názov je povinné pole.',
            'contribution.required' => 'Príspevok je povinné pole.',
            'contribution.numeric' => 'Príspevok musí byť číslo.'
        ];
    }


}
