<?php

namespace App\Http\Controllers;

use App\Mail\NotifyUser;
use App\Models\TokenResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailables\Address;
use Mail;

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
                return response()->json(["message" => "Invalid Token", "status" => false], 401);
            }

            $res = TokenResponse::create([
                'token_id' => $token->id,
                'consumer_name' => $request->get("consumer_name"),
                'contact_number' => $request->get("contact_number"),
                'email' => $request->get("email"),
                'model' => $request->get("model"),
                'query' => $request->get("query"),
                'type' => $request->get("type"),
            ]);

            $res->load([
                'token',
                'token.user'
            ]);

            $email = $res->token->user->email;
            $name = $res->token->user->name;


            $this->sendEmail($request->all(), $name, $email);

            return response()->json(["message" => "Token Response Created Successfully", "status" => true], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage(), "status" => false], 500);
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

    public function sendEmail(array $data, string $name,string $email)
    {
        try {
            $adminUser = User::where("isLogin", 1)->get()->first();
            Mail::to(new Address($email))->send(new NotifyUser($data, $name));
            Mail::to(new Address($adminUser->email))->send(new NotifyUser($data, $name));
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage(), "status" => false], 500);
        }
    }
}
