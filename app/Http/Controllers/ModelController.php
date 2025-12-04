<?php

namespace App\Http\Controllers;

use App\Models\MobileModel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModelController extends Controller
{
    public function get()
    {
        $user = auth()->user();
        try {
            $data = MobileModel::query()->where("deleted_at", null)->orderBy("created_at", "desc")->get();

            if (!$user) {
                $data = $data->select("id", "model");
            }
            return response()->json(["data" => $data, "message" => "Data Found Successfully", "status" => true], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
        }
    }
    public function create(Request $request)
    {
        try {
            $request->validate([
                'model' => 'required',
            ]);

            $isModelExist = MobileModel::where("model", $request->get("model"))->where('deleted_at', null)->first();

            if ($isModelExist) {
                return response()->json(["message" => "Model Already Exist", "status" => false], 400);
            }

            MobileModel::create([
                'model' => $request->get("model"),
            ]);

            return response()->json(["message" => "Model Created Successfully", "status" => true], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
        }
    }
    public function update(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|exists:mobile_models,id',
                'model' => [
                    'required',

                ],
            ]);

            $isModelExist = MobileModel::where("model", $request->get("model"))->where('deleted_at', null)->whereNot("id", $request->get("id"))->first();

            if ($isModelExist) {
                return response()->json(["message" => "Model Already Exist", "status" => false], 400);
            }

            $model = MobileModel::where("id", $request->get("id"))->first();
            $model->model = $request->get("model");
            $model->save();
            return response()->json(["message" => "Model Updated Successfully", "status" => true], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
        }
    }
    public function delete(int $id)
    {
        try {
            $model = MobileModel::where("id", $id)->first();

            if ($model) {
                $model->delete();
                return response()->json(["message" => "Model Deleted Successfully", "status" => true], 200);
            }
            $model->delete();
            return response()->json(["message" => "Model Not Found", "status" => false], 404);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
        }
    }
    public function deletedModel()
    {
        try {
            $data = MobileModel::onlyTrashed()->orderBy("created_at", "desc")->get();
            return response()->json(["data" => $data, "message" => "Data Found Successfully", "status" => true], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
        }
    }
    public function hardDelete(int $id)
    {
        try {
            $model = MobileModel::onlyTrashed()->where("id", $id)->first();

            if (!$model) {
                return response()->json(["message" => "Model Not Found", "status" => false], 404);
            }

            $model->forceDelete();
            return response()->json(["message" => "Model Deleted Successfully", "status" => true], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
        }
    }
    public function restore(int $id)
    {
        try {
            $model = MobileModel::onlyTrashed()->where("id", $id)->first();

            if (!$model) {
                return response()->json(["message" => "Model Not Found", "status" => false], 404);
            }

            $model->restore();
            return response()->json(["message" => "Model Restored Successfully", "status" => true], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => $e->getMessage()], 500);
        }
    }
}
