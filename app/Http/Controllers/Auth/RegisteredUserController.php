<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): Response
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'security_pin' =>['required', 'confirmed'], 
            'phone_number' => [ 'string', 'max:255'], 
            'date_of_birth' => [ 'date'], 
            'country' => [ 'string', 'max:255'], 
            'state' => [ 'string', 'max:255'],
            'id_front_photo' => [ 'file', 'max:5000'], 
            'id_back_photo' => [ 'file', 'max:5000'],
        ]);

        $user = User::create([
            'first_name' => $request->name,
            'last_name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'security_pin' => Hash::make($request->security_pin), 
            'phone_number' => $request->phone_number, 
            'date_of_birth' => $request->date_of_birth, 
            'country' => $request->country, 
            'state' => $request->state, 
            'id_front_photo' => $request->id_front_photo, 
            'id_back_photo' => $request->id_back_photo,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return response()->noContent();
    }
}
