<?php

namespace App\Http\Controllers\Auth;

use App\Models\Client;
use App\Models\Company;
use App\Models\Contractor;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        // TODO validate type of user
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'type' => ['required', 'regex:(client|contractor)']
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $request = new Request($data);
        $typeable = null;

        if($data['type'] == 'client') {
            $typeable = $this->createClient($request);
        } else {
            $typeable = $this->createContractor($request);
        }

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'typeable_id' => $typeable->id,
            'typeable_type' => get_class($typeable),
        ]);
    }

    private function createClient(Request $request)
    {
        $validatedData = $request->validate([
            'company' => 'required|unique:companies',
            'city' => 'required',
        ]);

        $company = Company::create($validatedData);

        return Client::create([
            'company_id' => $company->id,
        ]);
    }

    private function createContractor(Request $request)
    {
        $validatedData = $request->validate([
            'restaurant' => 'required|unique:restaurants',
            'city' => 'required',
        ]);

        $restaurant = Restaurant::create($validatedData);

        return Contractor::create([
            'restaurant_id' => $restaurant->id,
        ]);
    }
}
