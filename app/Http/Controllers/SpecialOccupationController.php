<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSpecialOccupationRequest;
use App\Http\Requests\UpdateSpecialOccupationRequest;
use App\Models\Demand;
use App\Http\Requests\StoreDemandRequest;
use App\Http\Requests\UpdateDemandRequest;
use App\Models\SpecialOccupation;

class SpecialOccupationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            'is_active' =>  $request->is_active,

        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Demand $demand)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSpecialOccupationRequest $request, Demand $demand)
    {
        $admin = auth('apiAdmin')->id();
        $special = SpecialOccupation::find($request->special_id);
        $special->update([
            'title' => json_encode([
                'ru' => $request->title_ru,
                'uz' => $request->title_uz,
            ]) ,
            'admin_id' =>  $admin,
            'occupation_id' =>  $request->occupation_id,
            'description' =>  $request->description,
            'is_active' =>  $request->is_active,

        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Demand $demand)
    {
        //
    }
}
