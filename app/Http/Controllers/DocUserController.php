<?php

namespace App\Http\Controllers;

use App\Models\DocUser;
use App\Http\Requests\StoreDocUserRequest;
use App\Http\Requests\UpdateDocUserRequest;

class DocUserController extends Controller
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
    public function store(StoreDocUserRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(DocUser $docUser)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDocUserRequest $request, DocUser $docUser)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DocUser $docUser)
    {
        //
    }
}
