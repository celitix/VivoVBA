<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Models\Token;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function adminLogin(Request $request)
    {
        try {
            $request->validate([
                'mobile' => 'required|exists:users,mobile',
                'password' => 'required',
            ]);

            $user = User::where('mobile', $request->mobile)->first();

            if (!$user->isLogin) {
                return response()->json([
                    'message' => 'Invalid User',
                ], 401);
            }

            if (!password_verify($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Invalid Password',
                ], 401);
            }

            $token = $user->createToken('auth', ['admin'])->plainTextToken;

            return response()->json(["token" => $token, "message" => "Login Successfully", "status" => true], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage(), "status" => false], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'mobile' => 'required|unique:users,mobile',
                'password' => 'required|min:8',
            ]);

            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                "isLogin" => 1,
                "password" => $request->password
            ]);

            return response()->json(["message" => "User Created Successfully", "status" => true], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage(), "status" => false], 500);
        }
    }

    public function createUser(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'mobile' => 'required|unique:users,mobile',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'mobile' => $request->mobile,
            ]);

            $token = User::where('email', $request->email)->first()->createToken('auth')->plainTextToken;

            Token::create([
                'user_id' => $user->id,
                'token' => $token
            ]);

            return response()->json(["message" => "User Created Successfully", "status" => true], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage(), "status" => false], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function getUserToken(string $id)
    {
        try {
            $token = Token::where('user_id', $id)->first();
            return response()->json(["token" => $token->token, "message" => "Token Found Successfully", "status" => true], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage(), "status" => false], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function allUsers(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $user = User::where("id", "!=", $userId)->get();
            return response()->json(["users" => $user, "message" => "Users Found Successfully", "status" => true], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage(), "status" => false], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $isUserExist = User::find($id);

            if ($isUserExist) {
                $isUserExist->delete();
                return response()->json(["message" => "User Deleted Successfully", "status" => true], 200);
            }

            return response()->json(["message" => "User Not Found", "status" => false], 404);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage(), "status" => false], 500);
        }
    }

    public function sendOtp(Request $request)
    {
        try {
            $request->validate([
                'mobile' => 'required|exists:users,mobile',
            ]);

            $otp = rand(100000, 999999);

            if (env("APP_ENV") == "local") {
                $otp = 12345;
            }
            $data = [
                'mobile' => $request->mobile,
                'otp' => $otp,
                'type' => "generated"
            ];

            $otp = Otp::create($data);

            return response()->json(["message" => "OTP Sent Successfully", "status" => true, "otpId" => $otp->id], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage(), "status" => false], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            $request->validate([
                'mobile' => 'required|exists:users,mobile',
                'otp' => 'required|min:4',
            ]);
            return response()->json(["message" => "OTP Sent Successfully", "status" => true], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage(), "status" => false], 500);
        }
    }
}
