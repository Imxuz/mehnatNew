<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSpecialOccupationRequest;
use App\Http\Requests\UpdateSpecialOccupationRequest;
use App\Models\Demand;
use App\Http\Requests\StoreDemandRequest;
use App\Http\Requests\UpdateDemandRequest;
use App\Models\Occupation;
use App\Models\SpecialOccupation;
use Illuminate\Http\Request;

class SpecialOccupationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $specials = SpecialOccupation::get();
        return response()->json($specials);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSpecialOccupationRequest $request)
    {
        $admin = auth('apiAdmin')->id();
        SpecialOccupation::create([
            'title' => json_encode([
                'ru' => $request->title_ru,
                'uz' => $request->title_uz,
            ]) ,
            'admin_id' =>  $admin,
            'occupation_id' =>  $request->occupation_id,
            'description' =>  $request->description,

        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($occupation_id)
    {
        $admin = auth('apiAdmin')->user();
        if (!$admin) {
            return response()->json('Sizda ruxsat yo‘q', 401);
        }
        $occupSpec = SpecialOccupation::where('occupation_id', $occupation_id)->get();
        return response()->json($occupSpec);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSpecialOccupationRequest $request, $id)
    {
        $admin = auth('apiAdmin')->id();
        if (!$admin) return response()->json('Xatolik', 401);
        $special = SpecialOccupation::findOrFail($id);
        $special->update([
            'title' => [
                'ru' => $request->title_ru,
                'uz' => $request->title_uz,
            ],
            'admin_id'     => $admin,
            'occupation_id'=> $request->occupation_id,
            'description'  => $request->description,
        ]);
        return response()->json([
            'message' => 'Muvaffaqiyatli yangilandi',
            'data' => $special
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $occupation = SpecialOccupation::find($request->id);
        if ($occupation){
            $occupation->delete();
        }else return response()->json(['message'=>"Bunday sahifa mavjud emas"],404);
    }
}
