<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Models\Token;
use App\Models\User;
use Carbon\Carbon;
use Http;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'mobile' => 'required|exists:users,mobile',
                'password' => 'required',
            ]);

            $user = User::where('mobile', $request->get('mobile'))->first();

            if (!$user) {
                return response()->json([
                    'message' => 'Invalid User',
                ], 401);
            }

            if (!password_verify($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Invalid Password',
                ], 401);
            }

            $token = $user->createToken('auth', [$user->role])->plainTextToken;

            return response()->json(["token" => $token, "message" => "Login Successfully", "status" => true, "role" => $user->role], 200);
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
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'mobile' => $request->get('mobile'),
                "isLogin" => 1,
                "password" => $request->get('password'),
                "role" => "admin"
            ]);

            return response()->json(["message" => "Admin Created Successfully", "status" => true], 200);
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
                'password' => 'required|min:8',
            ]);

            $user = User::create([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'mobile' => $request->get('mobile'),
                "password" => $request->get('password'),
                "role" => "user"
            ]);

            $token = $user->createToken('tracking')->plainTextToken;

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
            $user = User::where("id", "!=", $userId)->where("role", "user")->get();
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

            $otp = rand(10000, 99999);

            if (env("APP_ENV") == "local") {
                $otp = 12345;
            }
            $data = [
                'mobile' => $request->mobile,
                'otp' => $otp,
                'type' => "generated"
            ];

            $otp = Otp::create($data);

            $res = $this->sendOtpToMbno($data);

            if (!$res) {
                return response()->json(["message" => "OTP Not Sent. Please Try Again", "status" => false], 500);
            }

            return response()->json(["message" => "OTP Sent Successfully", "status" => true, "otpId" => $otp->id], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage(), "status" => false], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            $now = Carbon::now();
            $request->validate([
                'otp' => 'required|min:4',
                'otpId' => 'required|exists:otps,id',
                "mobile" => "required|exists:users,mobile"
            ]);

            $otp = Otp::find($request->otpId);

            if ($otp->mobile != $request->mobile) {
                return response()->json(["message" => "Invalid OTP", "status" => false], 500);
            }
            if (!$otp) {
                return response()->json(["message" => "Invalid OTP", "status" => false], 500);
            }

            if (!password_verify($request->otp, $otp->otp)) {
                return response()->json(["message" => "Invalid OTP", "status" => false], 500);
            }

            if ($otp->type != "generated") {
                return response()->json(["message" => "Invalid OTP", "status" => false], 500);
            }

            $minute10 = $now->copy()->subMinutes(10);

            if ($otp->created_at < $minute10) {
                $otp->update([
                    "type" => "expired"
                ]);
                return response()->json(["message" => "OTP Expired", "status" => false], 500);
            }

            $otp->update([
                "type" => "verified"
            ]);
            return response()->json(["message" => "OTP Verified Successfully", "status" => true], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage(), "status" => false], 500);
        }
    }

    // private function sendOtpToMbno($data)
    // {
    //     $url = `https://www.proactivesms.in/sendsms.jsp?user=vivosms&password=ebf73aaad3XX&senderid=YNGJYA&mobiles={$data['mobile']}&sms=Dear User, Your One Time Password is {$data['otp']}. By Yingjia Communication Pvt Ltd&tempid=1207175713278649924`;
    //     Http::get($url);

    //     if (Http::fail()) {
    //         return false;
    //     }

    //     return true;
    // }

    private function sendOtpToMbno($data)
    {
        $message = urlencode("Dear User, Your One Time Password is {$data['otp']}. By Yingjia Communication Pvt Ltd");

        $url = "https://www.proactivesms.in/sendsms.jsp?user=vivosms&password=ebf73aaad3XX&senderid=YNGJYA&mobiles={$data['mobile']}&sms={$message}&tempid=1207175713278649924";

        $response = Http::get($url);

        if ($response->failed()) {
            return false;
        }

        return true;
    }

}
