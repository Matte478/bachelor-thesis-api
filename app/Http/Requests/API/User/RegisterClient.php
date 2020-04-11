<?php

namespace App\Http\Requests\API\User;

use Illuminate\Foundation\Http\FormRequest;

class RegisterClient extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'company' => ['required', 'string', 'unique:companies'],
            'city' => ['required', 'string'],
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'Meno je povinné pole.',
            'email.required' => 'Email je povinné pole.',
            'email.email' => 'E-mail musí byť platnou e-mailovou adresou.',
            'email.unique' => 'E-mail už je obsadený.',
            'password.required' => 'Heslo je povinné pole.',
            'password.min' => 'Heslo musí mať najmenej 8 znakov.',
            'password.confirmed' => 'Potvrdenie hesla sa nezhoduje.',
            'company.required' => 'Názov spoločnosti je povinné pole.',
            'company.unique' => 'Názov spoločnosti už je obsadený.',
            'city.required' => 'Mesto je povinné pole.',
        ];
    }


}
