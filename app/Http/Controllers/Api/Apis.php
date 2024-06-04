<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Laravel\Passport\HasApiTokens; 

class Apis extends Controller
{
    public function createUser(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 401);
        }

      
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $user->sendEmailVerificationNotification();

        return response()->json([
            'status' => true,
            'message' => 'Account Created Successfully! Please verify your email address.',
            'data' => [
                'id' => $user->id,
                'first_name' => $user->first_name, 
                'last_name' => $user->last_name, 
                'email' => $user->email,
                'password' => $user->password
            ]
        ]);
    }

    public function createPin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'security_pin' => 'required|min:4'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 401);
        }

        $user = Auth::user(); 
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $user->security_pin = Hash::make($request->security_pin);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Security PIN created successfully! Please proceed to upload your documents.',
            'data' => [
                'id' => $user->id,
                'security_pin' => $security_pin
            ]
        ]);
    }

    public function uploadDocuments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_front_photo' => 'required|file|max:5000|image',
            'id_back_photo' => 'required|file|max:5000|image',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 401);
        }

        $user = Auth::user(); // Assuming user is authenticated
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $user->id_front_photo = $request->file('id_front_photo')->store('public');
        $user->id_back_photo = $request->file('id_back_photo')->store('public');
        

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Documents uploaded successfully! Please complete your profile.',
            'data' => [
                'id' => $user->id,
                'id_front_photo' => $user->id_front_photo,
                'id_back_photo' => $user->id_back_photo
            ]
        ]);
    }

    public function updatePersonalInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'string|max:255',
            'date_of_birth' => 'date',
            'country' => 'string|max:255',
            'state' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 401);
        }

        $user = Auth::user(); // Assuming user is authenticated
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $user->update($request->only([
            'phone_number',
            'date_of_birth',
            'country',
            'state',
        ]));

        return response()->json([
            'status' => true,
            'message' => 'Profile information updated successfully! You can now proceed to the app dashboard.',
            'data' => [
                'id' => $user->id,
                'phone_number' => $user->phone_number,
                'date_of_birth' => $user->date_of_birth,
                'country' => $user->country,
                'state' => $user->state,
            ]
        ]);
    }

    public function loginUser(Request $request)
    {
        $validateUser = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($request->only(['email', 'password']))) {
            $user = Auth::user();
            
            // Generate a unique token for the user
            $token = $this->generateToken();
    
            // Store the token in the database
            $user->update(['api_token' => $token]);
    
            // Return the token in the response
            return response()->json([
                'status' => true,
                'message' => 'Login Success',
                'token' => $token
            ]);
        }
    
        return response()->json([
            'status' => false,
            'message' => 'Invalid username or password'
        ], 401);
    }

    protected function generateToken()
{
    return Str::random(60); 
}
}
