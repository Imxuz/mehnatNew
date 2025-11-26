<?php

namespace App\Http\Controllers;

use App\Models\AdderDemand;
use App\Http\Requests\StoreAdderDemandRequest;
use App\Http\Requests\UpdateAdderDemandRequest;

class AdderDemandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $adder_demands = AdderDemand::with('dir_demand')->get();
        return response()->json($adder_demands);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdderDemandRequest $request)
    {
        AdderDemand::create($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(AdderDemand $adderDemand)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdderDemandRequest $request, AdderDemand $adderDemand)
    {
        AdderDemand::update($request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdderDemand $adderDemand)
    {
        //
    }
}
