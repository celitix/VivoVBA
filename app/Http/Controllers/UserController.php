<?php

namespace App\Http\Controllers;

use App\Exports\DynamicExport;
use App\Models\Lead;
use App\Models\MobileModel;
use App\Models\Otp;
use App\Models\Token;
use App\Models\TokenResponse;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Http;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
            $user = User::where("id", "!=", $userId)->where("role", "user")->orderBy("created_at", "desc")->get();
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

    public function getUserResponse()
    {
        try {
            $user = auth()->user()->id;
            $token = Token::where("user_id", $user)->first();

            $paginator = TokenResponse::where("token_id", $token->id)->with('leads')->with("model")->orderBy("created_at", "desc")->paginate(10)->through(function ($item) {
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

            return response()->json(["data" => $data["data"], "meta" => $data["meta"], "message" => "Data Found Successfully", "status" => true], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage(), "status" => false], 500);
        }
    }


    public function report()
    {
        try {
            $authUser = auth()->user();
            $users = User::where("id", $authUser->id)->get();

            if ($authUser->role == "admin") {
                $users = User::where("role", "user")->with([
                    'token.responses.leads',
                    'token.responses.model'
                ])->get();
            }

            $result = [];

            foreach ($users as $user) {

                $responses = $user->tokenResponses;
                $leads = $user->leads;


                $totalResponses = $responses->count();
                $totalLeads = $responses->flatMap->lead->count();
                $totalConversions = $responses->flatMap->lead->where('is_converted', true)->count();


                $responsesPerModel = $responses
                    ->groupBy(fn($r) => $r->model?->model)
                    ->map(fn($r) => $r->count())
                    ->map(fn($count, $model) => [
                        'model' => $model,
                        'total_responses' => $count
                    ])
                    ->values();

                $leadsPerModel = $responses->groupBy(fn($r) => $r->model?->model)
                    ->map(fn($group) => $group->flatMap->lead)
                    ->map(fn($group, $model) => [
                        'model' => $model,
                        'total_leads' => $group->count(),
                    ])
                    ->values();

                $conversionsPerModel = $responses
                    ->groupBy(fn($r) => $r->model?->model)
                    ->map(function ($group) {
                        return $group->flatMap->lead->where('is_converted', true)->count();
                    })
                    ->map(fn($count, $model) => [
                        'model' => $model,
                        'total_conversions' => $count
                    ])
                    ->values();

                $responsesPerSource = $responses
                    ->groupBy('query')  //here source is saved in query
                    ->map(fn($r) => $r->count())
                    ->map(fn($count, $source) => [
                        'source' => $source,
                        'total_responses' => $count
                    ])
                    ->values();


                $responsePerModel = MobileModel::all()->map(function ($model) use ($responses) {
                    return [
                        "model" => $model->model,
                        "total_responses" => $responses->where("model_id", $model->id)->count(),
                        "total_leads" => $responses->where("model_id", $model->id)->flatMap->lead->count(),
                        "total_conversions" => $responses->where("model_id", $model->id)->flatMap->lead->where('is_converted', true)->count(),
                    ];
                });
                $leadsPerResponse = $totalResponses > 0
                    ? round($totalLeads / $totalResponses, 2)
                    : 0;

                $newUsers = $user->where('created_at', '>', Carbon::now()->subDays(7))->count();
                $increasePercentage = $newUsers > 0 ? round(($newUsers / $user->count()) * 100, 2) : 0;

                $newUserToday = $user->whereDate('created_at', Carbon::today())->count();

                $topModel = TokenResponse::select('model_id', DB::raw('COUNT(*) as total'))
                    ->groupBy('model_id')
                    ->orderByDesc('total')
                    ->with('model')
                    ->first();

                $recentLeadsCount = Lead::where('created_at', '>', Carbon::now()->subDays(2))->count();

                $totalResponse;
                if ($authUser->role == "admin") {
                    $totalResponse = TokenResponse::all()->count();
                }



                $result[] = [
                    "user_id" => $user->id,
                    "user_name" => $user->name,
                    "total_responses" => $totalResponses,
                    "total_leads" => $totalLeads,
                    "total_conversions" => $totalConversions,
                    "leads_per_response" => $leadsPerResponse,
                    "responses_per_model" => $responsesPerModel,
                    "leads_per_model" => $leadsPerModel,
                    "conversions_per_model" => $conversionsPerModel,
                    "responsesPerSource" => $responsesPerSource,
                    "responses" => $responsePerModel,

                ];
            }

            return response()->json([
                "message" => "Tracking data loaded successfully",
                "status" => true,
                "data" => $result,
                "meta" => [
                    "new_users_percentage" => $increasePercentage,
                    "newUserToday" => $newUserToday,
                    "topModel" => $topModel->model->model,
                    "recentLeadsCount" => $recentLeadsCount,
                    "totalResponse" => $totalResponse ?? null
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
                "status" => false
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {

            $request->validate([
                "id" => "required|exists:users,id",
                'name' => 'required',
                'email' => 'required|email|unique:users,email,' . $request->get('id'),
                'mobile' => 'required|unique:users,mobile,' . $request->get('id'),
                // 'password' => 'required|min:8',
            ]);

            $user = User::find($request->id);

            $user->update($request->all());

            return response()->json([
                "message" => "User updated successfully",
                "status" => true,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
                "status" => false
            ], 500);
        }
    }

    public function password(Request $request)
    {
        try {

            $request->validate([
                "id" => "required|exists:users,id",
                'password' => 'required|min:8',
            ]);

            $user = User::find($request->get('id'));

            $user->update([
                'password' => $request->get('password')
            ]);

            return response()->json([
                "message" => "User updated successfully",
                "status" => true,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
                "status" => false
            ], 500);
        }
    }

    public function allUserData(Request $request)
    {
        try {
            $fromDate = request()->query("fromDate");
            $toDate = request()->query("toDate");
            $vbaId = request()->query("vbaId");

            $from = $fromDate ? Carbon::parse($fromDate)->startOfDay() : null;
            $to = $toDate ? Carbon::parse($toDate)->endOfDay() : null;

            $data = TokenResponse::with(["token.user", "leads", "model"])
                ->when($from && $to, function ($query) use ($from, $to) {
                    $query->whereBetween('created_at', [$from, $to]);
                })
                ->when($vbaId, function ($query) use ($vbaId) {
                    $query->whereHas('token', function ($q) use ($vbaId) {
                        $q->where('user_id', $vbaId);
                    });
                })
                ->orderBy("created_at", "desc")
                ->paginate(10)
                ->through(function ($item) {
                    $item->isCreated = (bool) $item->leads;
                    return $item;
                });

            // $data = TokenResponse::with(["token.user", "lead", "model"])->where(function ($query) use ($fromDate, $toDate) {
            //     if ($fromDate && $toDate) {
            //         $fromDate = Carbon::parse($fromDate)->startOfDay();
            //         $toDate = Carbon::parse($toDate)->endOfDay();
            //         $query->whereBetween('created_at', [$fromDate, $toDate]);
            //     }
            // })->paginate(10)
            //     ->through(function ($item) {
            //         $item->isCreated = (bool) $item->lead;
            //         return $item;
            //     });

            // $responses = $data->tokenResponses();


            return response()->json([
                "message" => "User data loaded successfully",
                "status" => true,
                "data" => $data->items(),
                "fromDate" => $fromDate,
                "toDate" => $toDate,
                // "responses" => $responses,
                'meta' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'from' => $data->firstItem(),
                    'to' => $data->lastItem(),
                    'next_page_url' => $data->nextPageUrl(),
                    'prev_page_url' => $data->previousPageUrl(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
                "status" => false
            ], 500);
        }
    }

    public function allUser()
    {
        try {
            $user = User::where("role", "user")->select("id", "name")->get();
            return response()->json([
                "message" => "User data loaded successfully",
                "status" => true,
                "data" => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "message" => $e->getMessage(),
                "status" => false
            ], 500);
        }
    }

    // public function exportData(Request $request)
    // {
    //     try {
    //         $data = TokenResponse::query()
    //             ->with('leads')
    //             ->with("token")
    //             ->with("user")
    //             ->orderBy("created_at", "desc")
    //             ->get()->map(function ($item) {
    //                 $item->isCreated = (bool) $item->leads;
    //                 return $item;
    //             });

    //         return Excel::download(new DynamicExport($data), 'export.xlsx');
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             "message" => $e->getMessage(),
    //             "status" => false
    //         ], 500);
    //     }
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
