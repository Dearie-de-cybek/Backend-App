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
    // TODO: #1 Upon successful login, your app would generate an authorization token (e.g., JWT) and send it back to the user's device (typically stored in local storage).
    public function createUser(Request $request)
    {
       // Validation
       $validator = Validator::make($request->all(), [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8',
        'confirmpassword'  => 'required_with:password|same:password|min:8'
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

    public function verifyEmail(Request $request, $token)
    {
        if (!Hash::checkSignature($token, $request->user()->email_for_verification)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid verification token'
            ], 401);
        }

        $request->user()->markEmailAsVerified();

        return response()->json([
            'status' => true,
            'message' => 'Email verified successfully! You can now proceed with the next steps.'
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

        $user = Auth::user(); // Assuming user is authenticated
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
            'id_front_photo' => 'required|file|max:5000|image', // Add image validation rules
            'id_back_photo' => 'required|file|max:5000|image', // Add image validation rules
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 401);
        }

        $user = Auth::user(); // Assuming user is authenticated

        // Implement logic to save uploaded files (e.g., store paths in user model)
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
            'phone_number' => 'string|max:255', // Optional validation for phone number
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
