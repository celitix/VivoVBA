<?php

namespace App\Http\Controllers;

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

            return response()->json(["token" => $token, "message" => "Login Successfully"], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
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
                "isLogin" => true,
                "password" => $request->password
            ]);

            return response()->json(["message" => "User Created Successfully"], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
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

            return response()->json(["message" => "User Created Successfully"], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function getUserToken(string $id)
    {
        try {
            $token = Token::where('user_id', $id)->first();
            return response()->json(["token" => $token->token, "message" => "Token Found Successfully"], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
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
                return response()->json(["message" => "User Deleted Successfully"], 200);
            }

            return response()->json(["message" => "User Not Found"], 404);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
        }

    }
}
