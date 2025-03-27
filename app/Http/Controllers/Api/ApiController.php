<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            "name" => "required|string",
            "email"=> "required|email|unique:users,email",
            "password"=> "required|confirmed",
            "profile_image"=> "nullable|image"
        ]);

        // Check image available
        if($request->hasFile("profile_image")) {
            $data["profile_image"] = $request->file("profile_image")->store("users", "public"); 
        }

        User::create($data);

        return response()->json([
            "status"=> true,
            "message"=> "User registered successfully!"
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            "email"=> "required|email",
            "password" => "required"
        ]);

        $user = User::where("email", $request->email)->first();

        if(!empty($user)) {
            if(Hash::check($request->password, $user->password)) {
                $token = $user->createToken("myToken")->accessToken;

                return response()->json([
                    "status"=> true,
                    "message"=> "User logged in successfully!",
                    "token"=> $token
                ]);
            } else {
                return response()->json([
                    "status"=> false,
                    "message"=> "Password didn't match!"
                ]);
            }
        } else {
            return response()->json([
                "status"=> false,
                "message"=> "Email not found!"
            ]);
        }
    }

    public function profile()
    {
        $userdata = auth()->user();

        return response()->json([
            "status"=> true,
            "message"=> "User profile fetched successfully!",
            "data"=> $userdata,
            "profile_image" => asset('storage/'.$userdata['profile_image']),
        ]);
    }

    public function refreshToken()
    {
        auth()->user()->token()->revoke();

        $user = auth()->user();

        $token = $user->createToken('myToken')->accessToken;

        return response()->json([
            'status'=> true,
            'message'=> 'Token re-issued!',
            'data'=> $token
        ]);
    }

    public function logout()
    {
        auth()->user()->token()->revoke();

        return response()->json([
            'status'=> true,
            'message'=> 'User logged out successfully!'
        ]);
    }
}
