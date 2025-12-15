<?php

namespace App\Http\Controllers;

use App\Models\Click;
use App\Models\Demand;
use App\Models\DirDemand;
use App\Models\DocUser;
use App\Models\Vacancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserVacancyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(){
        $vacancies = Vacancy::with('demands.dir_demand','occupation','region','oClick')->orderBy('created_at','desc')
            ->where('publication','!=', null)->where('close_at',">",now())->get();
        return response()->json($vacancies);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {


    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function userVacancy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vacancy_id' => ['required', 'integer'],
        ]);
        $user = auth('api')->user();
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $vacancies = Vacancy::where('id', $request->vacancy_id)->with('demands.dir_demand')->first();
        $demand = Demand::where('vacancy_id',$request->vacancy_id)->pluck('dir_demand_id');
        $docUser = DocUser::where('user_id', $user->id)->pluck('dir_demand_id');
        $missingdemands = $demand->diff($docUser);
        $missing = DirDemand::select('id','title', 'name')->whereIn('id', $missingdemands)->get();
        if (! $missing) return response()->json([
            'vacancy'=>$vacancies,
            'missing'=>$missing,
        ]);
    }


}
