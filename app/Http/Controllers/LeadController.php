<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function get(int $id)
    {
        try {
            $lead = Lead::where("token_responses_id", $id)->first();
            return response()->json(["lead" => $lead, "message" => "Lead Found Successfully", "status" => true], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage(), "status" => false], 500);
        }
    }


    public function create(Request $request)
    {
        try {
            $request->validate([
                "token_id" => "required|exists:token_responses,id",
                "is_converted" => "required|boolean",
            ]);

            $isAlreadyExist = Lead::where("token_responses_id", $request->get("token_id"))->first();
            if ($isAlreadyExist) {
                return response()->json(["message" => "Lead already exist", "status" => false], 500);
            }


            if ($request->get("is_converted") && !$request->get("imei")) {
                return response()->json(["message" => "IMEI is required", "status" => false], 500);
            }


            if ($request->get("imei") && strlen($request->get("imei")) !== 15) {
                return response()->json(["message" => "IMEI is not valid", "status" => false], 401);
            }

            $data = [
                "token_responses_id" => $request->get("token_id"),
                "is_converted" => $request->get("is_converted"),
                "imei" => $request->get("imei"),
                "remarks" => $request->get("remarks") ?? ""
            ];

            $lead = Lead::create($data);

            if (!$lead) {
                return response()->json(["message" => "Failed to create lead", "status" => false], 500);
            }

            return response()->json(["message" => "Lead Created Successfully", "status" => true], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage(), "status" => false], 500);
        }
    }
}
