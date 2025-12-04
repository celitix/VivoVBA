<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function get(int $id)
    {
        try {
            $lead = Lead::where("token_id", $id)->first();
            return response()->json(["lead" => $lead, "message" => "Lead Found Successfully", "status" => true], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage(), "status" => false], 500);
        }
    }


    public function create(Request $request)
    {
        try {
            $request->validate([
                "token_id" => "required|exists:tokens,id",
                "is_converted" => "required|boolean",
            ]);

            if ($request->get("is_converted") && !$request->get("imei")) {
                return response()->json(["message" => "IMEI is required", "status" => false], 500);
            }
            $data = [
                "token_id" => $request->get("token_id"),
                "is_converted" => $request->get("is_converted"),
                "imei" => $request->get("imei"),
                "remarks" => $request->get("remarks") ?? ""
            ];

            $lead = Lead::create($data);

            if (!$lead) {
                return response()->json(["message" => "Failed to create lead", "status" => false], 500);
            }
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage(), "status" => false], 500);
        }
    }
}
