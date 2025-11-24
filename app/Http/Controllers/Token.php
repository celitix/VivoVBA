<?php

namespace App\Http\Controllers;

use App\Models\TokenResponse;
use Illuminate\Http\Request;

class Token extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getTokenResponse(string $token)
    {
        try {
            $token = \App\Models\Token::where("token", $token)->first();
            if (!$token) {
                return response()->json(["message" => "Invalid Token"], 401);
            }

            $tokenResponse = TokenResponse::where('token_id', $token->id)->get();

            return response()->json(["tokenResponse" => $tokenResponse, "message" => "Token Response Found Successfully"], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'token' => "required",
                'consumer_name' => "required",
                'contact_number' => "required|numeric",
                'email' => "required|email",
                'model' => "required",
                'query' => "required",
                'type' => "required",
            ]);

            $token = \App\Models\Token::where("token", $request->token)->first();
            if (!$token) {
                return response()->json(["message" => "Invalid Token"], 401);
            }

            TokenResponse::create([
                'token_id' => $token->id,
                'consumer_name' => $request->consumer_name,
                'contact_number' => $request->contact_number,
                'email' => $request->email,
                'model' => $request->model,
                'query' => $request->query,
                'type' => $request->type
            ]);

            return response()->json(["message" => "Token Response Created Successfully"], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
        }
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
