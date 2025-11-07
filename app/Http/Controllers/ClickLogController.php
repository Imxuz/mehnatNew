<?php

namespace App\Http\Controllers;

use App\Models\ClickLog;
use App\Http\Requests\StoreClickLogRequest;
use App\Http\Requests\UpdateClickLogRequest;

class ClickLogController extends Controller
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
    public function store(StoreClickLogRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ClickLog $clickLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClickLogRequest $request, ClickLog $clickLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClickLog $clickLog)
    {
        //
    }
}
