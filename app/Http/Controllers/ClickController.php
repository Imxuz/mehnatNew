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
use Illuminate\Http\Request;

class ClickController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = auth('api')->id();
        $clickVacancies = Click:: where('user_id', $userId)->pluck('vacancy_id');
        $userVacancies = Vacancy::with('demands.dir_demand','occupation','region','oClick')
            ->whereIn('id',$clickVacancies)
            ->orderBy('created_at','desc')->get();
        return response()->json($userVacancies);


    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClickRequest $request)
    {
        $user = auth('api')->user();
        if (!$user->id){
            return response()->json([
                'status' => false,
                'message' => 'Bunday foydalanuvchi mavjud emas.',
            ], 400);
        }
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
        $ClickCheck = Click::where('user_id', $user->id)->where('vacancy_id',$request->vacancy_id)->first();
        if ($ClickCheck) {
            return response()->json([
                'status' => false,
                'message' => 'Siz ushbu arizaga so\'rov qoldirgansiz.',
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
            $click_id = Click::create([
                'user_id'=>$user->id,
                'vacancy_id'=>$request->vacancy_id,
            ])->id;
            $url = 'https://ai-hr.ngmk.uz/api/vacancy/oclick-save/'.$click_id;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_exec($ch);
            curl_close($ch);
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
        $vacancies = Vacancy::where('id', $id)->with('demands.dir_demand','occupation','region')->first();
        if ($vacancies->close_at<now()&&$vacancies->publication==null){
            return response()->json(['error' => 'Bunday vakansiya xozirda mavjud emas'], 404);
        }
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

    public function adminUserClicks(Request $request)
    {
        $admin = auth('apiAdmin')->user();
        $vacancy_id = $request->vacancy_id;
        if ($vacancy_id){
            $userClicks = Click:: where('vacancy_id', $vacancy_id) ->with([
                'user:id,name,pinfl,phone',
                'user.docUser:id,user_id,path,dir_demand_id',
                'user.docUser.demand:id,title'

            ])->get();

        }
        return response()->json($userClicks);


    }
}
