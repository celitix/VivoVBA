<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function adminLogin(Request $request)
    {
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
    }


  public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'required|unique:users,mobile',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            // "isLogin" => true,
            // "password" => $request->password
        ]);

        return response()->json($user);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }
}
