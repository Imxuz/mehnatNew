<?php

namespace App\Http\Controllers;

use App\Models\Click;
use App\Http\Requests\StoreClickRequest;
use App\Http\Requests\UpdateClickRequest;
use App\Models\Demand;
use App\Models\DirDemand;
use App\Models\DocUser;
use App\Models\Vacancy;
use Carbon\Carbon;

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
        $user = auth('api')->user();
        $vacancy = Vacancy::where('id', $request->vacancy_id)
            ->where('open_at', '<=', Carbon::now())
            ->where('close_at', '>=', Carbon::now())
            ->first();
        if (!$vacancy) {
            return response()->json([
                'status' => false,
                'message' => 'Bu vakansiya hozir faol emas yoki muddati tugagan.',
            ], 400);
        }
        $demands = Demand::where('vacancy_id',$request->vacancy_id)->pluck('dir_demand_id');
        $docUser = DocUser::where('user_id',$user->id)->pluck('dir_demand_id');
        $missingDemands = $demands->diff($docUser);
        if ($missingDemands->isNotEmpty()) {
            $errors = DirDemand::whereIn('id', $missingDemands)
                ->pluck('title');
            return response()->json([
                'error' => $errors,
            ],422);
        }else {
            Click::create([
                'user_id'=>$user->id,
                'vacancy_id'=>$request->vacancy_id,
            ]);
        }



        return response()->json([
            'message' => 'Foydalanuvchida barcha kerakli hujjatlar mavjud.',
        ]);


    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        if (!is_numeric($id) || intval($id) != $id) {
            return response()->json(['error' => 'Invalid ID'], 400);
        }
        $vacancies = Vacancy::where('id', $id)->with('demands.dir_demand')->get();
        return response()->json($vacancies);
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
