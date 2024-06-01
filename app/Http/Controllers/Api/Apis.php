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
            'password' => 'required|string|min:8',
            'confirmpassword' => 'required_with:password|same:password|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 401);
        }

        // Create user, send verification email, and return success message
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'verification_token' => Str::random(40)
        ]);

        $user->sendEmailVerificationNotification();

        return response()->json([
            'status' => true,
            'message' => 'Account Created Successfully! Please verify your email address.'
        ]);
    }

    public function createPin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'security_pin' => 'required|min:4',
            'confirm_pin' => 'required_with:security_pin|same:security_pin|min:4',
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
            'message' => 'Security PIN created successfully! Please proceed to upload your documents.'
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

        $user->id_front_photo = $request->file('id_front_photo')->store('uploads'); // Example
        $user->id_back_photo = $request->file('id_back_photo')->store('uploads'); // Example

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Documents uploaded successfully! Please complete your profile.'
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
            'message' => 'Profile information updated successfully! You can now proceed to the app dashboard.'
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
