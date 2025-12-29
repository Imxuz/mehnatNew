<?php

namespace App\Http\Controllers;

use App\Imports\OccupationsImport;
use App\Models\Occupation;
use App\Http\Requests\StoreOccupationRequest;
use App\Http\Requests\UpdateOccupationRequest;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OccupationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $occupation = Occupation::get();
        return response()->json($occupation);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOccupationRequest $request)
    {
        $admin = auth('apiAdmin')->user();
        if ($admin) return response()->json('Xatolik', 401);

        Occupation::create([
            'occupation' => json_encode([
                'ru' => $request->title_ru,
                'uz' => $request->title_uz,
            ]) ,
            'demand' => json_encode([
                'ru' => $request->demand_ru,
                'uz' => $request->demand_uz,
            ]) ,

        ]);
//        Excel::import(new OccupationsImport, $request->file('file'));
//
//        return response()->json([
//            'message' => 'Occupationlar muvaffaqiyatli import qilindi'
//        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Occupation $occupation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOccupationRequest $request)
    {
        $admin = auth('apiAdmin')->user();
        if ($admin) return response()->json('Xatolik', 401);
        $occupation = Occupation::find($request->id);
        $occupation->update([
            'occupation' => json_encode([
                'ru' => $request->title_ru,
                'uz' => $request->title_uz,
            ]) ,
            'demand' => json_encode([
                'ru' => $request->demand_ru,
                'uz' => $request->demand_uz,
            ]) ,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Occupation $occupation)
    {
        //
    }

    public function searchOccupation(Request $request)
    {
        $query = Occupation::query();
        if ($request->searchOccupation) {
            $query->where('occupation', 'like', '%' . $request->searchOccupation . '%');
        }
        return response()->json($query->with('spcOccupation')->get());
    }
}
