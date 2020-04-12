<?php

namespace App\Http\Requests\API\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateEmployee extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Gate::allows('employee.edit');
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
            'email' => ['sometimes', 'email', 'string', 'email', 'max:255', 'unique:users,email,' . $this->route('employee')],
            'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
            'type-of-employment_id' => ['nullable', 'integer'],
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'name.string' => 'Meno je povinné pole.',
            'email.string' => 'Email je povinné pole.',
            'email.email' => 'E-mail musí byť platnou e-mailovou adresou.',
            'email.unique' => 'E-mail už je obsadený.',
            'password.required' => 'Heslo je povinné pole.',
            'password.min' => 'Heslo musí mať najmenej 8 znakov.',
            'password.confirmed' => 'Potvrdenie hesla sa nezhoduje.',
        ];
    }
}
