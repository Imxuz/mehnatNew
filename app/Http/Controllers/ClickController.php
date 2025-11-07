<?php

namespace App\Http\Controllers;

use App\Models\Click;
use App\Http\Requests\StoreClickRequest;
use App\Http\Requests\UpdateClickRequest;

class ClickController extends Controller
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
    public function store(StoreClickRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Click $click)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateClickRequest $request, Click $click)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Click $click)
    {
        //
    }
}
