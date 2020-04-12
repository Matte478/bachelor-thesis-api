<?php

namespace App\Http\Requests\API\TypeOfEmployment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateTypeOfEmployment extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('type-of-employment.edit');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'contribution' => ['sometimes', 'numeric'],
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'name.string' => 'Názov je povinné pole.',
            'contribution.numeric' => 'Príspevok musí byť číslo.'
        ];
    }


}
