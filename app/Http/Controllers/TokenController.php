<?php

namespace App\Http\Controllers;

use App\Mail\NotifyUser;
use App\Models\Token;
use App\Models\TokenResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailables\Address;
use Mail;

class TokenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getTokenResponse(string $token)
    {
        try {

            $token = Token::where("token", $token)->first();
            if (!$token) {
                return response()->json(["message" => "Invalid Token"], 401);
            }

            // $tokenResponse = TokenResponse::query()->where('token_id', $token->id)
            //     ->with('leads')
            //     ->orderBy("created_at", "desc")
            //     ->paginate(10)
            //     // ->get()
            //     ->through(function ($item) {
            //         $item->isCreated = $item->leads ? true : false;
            //         return $item;
            //     });

            $paginator = TokenResponse::query()
                ->where('token_id', $token->id)
                ->with('leads')
                ->orderBy("created_at", "desc")
                ->paginate(10)
                ->through(function ($item) {
                    $item->isCreated = (bool) $item->leads;
                    return $item;
                });

            $data = [
                'data' => $paginator->items(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                    'next_page_url' => $paginator->nextPageUrl(),
                    'prev_page_url' => $paginator->previousPageUrl(),
                ]
            ];



            return response()->json(["tokenResponse" => $data["data"], "meta"=> $data["meta"], "message" => "Token Response Found Successfully"], 200);
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
                'token' => "required|exists:tokens,token",
                'consumer_name' => "required",
                'contact_number' => "required|numeric",
                'email' => "required|email",
                // 'model' => "required|exists:mobile_models,model",
                'query' => "required",
                'type' => "required",
            ]);

            $token = Token::where("token", $request->token)->first();
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

    public function sendEmail(array $data, string $name, string $email)
    {
        try {
            $adminUser = User::where("role", "admin")->get();

            foreach ($adminUser as $user) {
                Mail::to(new Address($user->email))->queue(new NotifyUser($data, $name));
            }
            Mail::to(new Address($email))->queue(new NotifyUser($data, $name));

        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage(), "status" => false], 500);
        }
    }
}
