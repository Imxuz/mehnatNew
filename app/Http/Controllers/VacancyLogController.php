<?php

namespace App\Http\Controllers;

use App\Models\VacancyLog;
use App\Http\Requests\StoreVacancyLogRequest;
use App\Http\Requests\UpdateVacancyLogRequest;

class VacancyLogController extends Controller
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
    public function store(StoreVacancyLogRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(VacancyLog $vacancyLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVacancyLogRequest $request, VacancyLog $vacancyLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VacancyLog $vacancyLog)
    {
        //
    }
}
