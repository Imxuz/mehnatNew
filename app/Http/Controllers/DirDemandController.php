<?php

namespace App\Http\Controllers;

use App\Models\DirDemand;
use App\Http\Requests\StoreDirDemandRequest;
use App\Http\Requests\UpdateDirDemandRequest;
use App\Services\DefaultAdminService;
use Illuminate\Support\Facades\Auth;

class DirDemandController extends Controller
{

    public function __construct(private DefaultAdminService $defaultAdminService){}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dir_demand = DirDemand::with('adder_demands')->get();
        return response()->json($dir_demand);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDirDemandRequest $request)
    {
        $this->defaultAdminService->errAllVacancyView();
        DirDemand::create($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(DirDemand $dirDemand)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDirDemandRequest $request, DirDemand $dirDemand)
    {
        $this->defaultAdminService->errAllVacancyView();
        $dirDemand->update($request->validated());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DirDemand $dirDemand)
    {
        //
    }
}
