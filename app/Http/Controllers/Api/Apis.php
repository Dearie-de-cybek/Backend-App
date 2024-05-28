<?php

namespace App\Http\Controllers\Api;


use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\EventCategory;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;



class Apis extends Controller
{
    //
    public function createUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), [
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required',  Rules\Password::defaults()],
                'confirmpassword' => ['required_with:password', 'same:password', 'min:8'],
                'security_pin' => ['required', 'min:4'],
                'confirm_pin' => ['required_with:security_pin', 'same:security_pin', 'min:4'],
                'phone_number' => [ 'string', 'max:255'], 
                'date_of_birth' => ['date'], 
                'country' => [ 'string', 'max:255'], 
                'state' => [ 'string', 'max:255'],
                'id_front_photo' => [ 'file', 'max:5000'], 
                'id_back_photo' => [ 'file', 'max:5000'],
            ]);

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation Error',
                    'errors' => $validateUser->errors()
                ], 401);
            }


            $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
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

            $user->sendEmailVerificationNotification();

            return response()->json([
                'status' => true,
                'message' => 'Account Created Successfully, Verification link has been sent to your mail',
                'data' => $user,
                'token' => $user->createToken('API TOKEN')->plainTextToken
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Server Error',
                'errors' => $th->getMessage()
            ]);
        }
    }

    
   
    public function loginUser(Request $request)
    {

        //code...
        $validateUser = $request->validate(
            [
                'email' => 'required|email',
                'password' => 'required'
            ]
        );



        if (Auth::attempt($request->only(['email', 'password']))) {
            return response()->json([
                'message' => 'Login Success'
            ]);
        }

        return redirect()->back()->with('message','Invalid username or password');
    }

   
}
