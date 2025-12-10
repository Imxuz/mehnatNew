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
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);


        Excel::import(new OccupationsImport, $request->file('file'));

        return response()->json([
            'message' => 'Occupationlar muvaffaqiyatli import qilindi'
        ]);
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
    public function update(UpdateOccupationRequest $request, Occupation $occupation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Occupation $occupation)
    {
        //
    }
}
