<?php

namespace App\Http\Controllers;

use App\Models\Demand;
use App\Http\Requests\StoreDemandRequest;
use App\Http\Requests\UpdateDemandRequest;

class DemandController extends Controller
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
    public function store(StoreDemandRequest $request)
    {
        //
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
    public function update(UpdateDemandRequest $request, Demand $demand)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Demand $demand)
    {
        //
    }
}
