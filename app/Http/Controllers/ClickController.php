<?php

namespace App\Http\Controllers;

use App\Exports\UsersInfoExport;
use App\Models\Click;
use App\Http\Requests\StoreClickRequest;
use App\Http\Requests\UpdateClickRequest;
use App\Models\ClickLog;
use App\Models\Demand;
use App\Models\DirDemand;
use App\Models\DocUser;
use App\Models\DocUserHistory;
use App\Models\User;
use App\Models\Vacancy;
use App\Services\DefaultAdminService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ClickController extends Controller implements HasMiddleware
{
    protected string $hrUrl;

    public function __construct(private DefaultAdminService $defaultAdminService)
    {
        $this->hrUrl = env('HR_MEHNAT_API');
    }
    /**
     * Display a listing of the resource.
     */

    public static function middleware(): array
    {
        return [
            new Middleware('throttle:1,120', only: ['destroy']),
        ];
    }
    public function index()
    {
        $userId = auth('api')->id();
        $clickVacancies = Click:: where('user_id', $userId)->pluck('vacancy_id');
        $userVacancies = Vacancy::with('demands.dir_demand','occupation','region','oClick')
            ->whereIn('id',$clickVacancies)
            ->orderBy('open_at','desc')->get();
        return response()->json($userVacancies);


    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClickRequest $request)
    {
        /** @var User|null $userInfo */
        $user = auth('api')->id();
        $userInfo = auth('api')->user();
        if (!$user){
            return response()->json([
                'status' => false,
                'message' => 'Bunday foydalanuvchi mavjud emas.',
            ], 400);
        }
        if (!$userInfo->photo_user){
            return response()->json([
                'status' => false,
                'message' => 'Sizning shaxsiy rasmingiz mavjud emas. Profil orqali uni yuklash zarur.',
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
        $ClickCheck = Click::where('user_id', $user)->where('vacancy_id',$vacancy->id)->first();
        if ($ClickCheck) {
            return response()->json([
                'status' => false,
                'message' => 'Siz ushbu arizaga so\'rov qoldirgansiz.',
            ], 400);
        }

        $demands = Demand::where('vacancy_id',$vacancy->id)->pluck('dir_demand_id');
        $docUser = DocUser::where('user_id',$user)->where('check', 1)->pluck('dir_demand_id');
        $missingDemands = $demands->diff($docUser);
        if ($missingDemands->isNotEmpty()) {
            $errors = DirDemand::whereIn('id', $missingDemands)
                ->pluck('title');
            return response()->json([
                'status' => false,
                'message' => 'Quyidagi hujjatlar tasdiqlanmagan yoki mavjud emas',
                'errors' => $errors,
            ],422);
        }else {
            $click_id = Click::create([
                'user_id'=>$user,
                'vacancy_id'=>$request->vacancy_id,
            ])->id;

            DocUser::where('user_id', $user)
                ->where('check', 1)
                ->whereIn('dir_demand_id', $demands)
                ->update([
                    'vacancy_doc_id' => $vacancy->id,
                ]);

            $docUsers = DocUser::where('user_id', $user)->where('check', 1)->whereIn('dir_demand_id', $demands)->get();
            foreach ($docUsers as $docUser) {
                DocUserHistory::create([
                    'click_id'    => $click_id,
                    'user_id' => $docUser->user_id,
                    'dir_demand_id' => $docUser->dir_demand_id,
                    'path' => $docUser->path,
                    'adder_demands_id' => $docUser->adder_demands_id,
                    'doc_info' => $docUser->doc_info,
                    'description' => $docUser->description,
                    'ip_address'=>$docUser->ip_address,
                ]);
            }

            $url = $this->hrUrl.'/vacancy/oclick-save/'.$click_id;
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
            'status' => true,
            'message' => 'Ariza muvaffaqiyatli yuborildi.',
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
    public function destroy(Request $request)
    {
        $request->validate([
            'vacancy_id' => 'required|integer',
        ]);

        $vacancyOpen = Vacancy::where('open_at', '<', now())
            ->where(function ($query) {
                $query->whereNull('close_at')
                    ->orWhere('close_at', '>', now());
            })
            ->where('id', $request->vacancy_id)
            ->first();

        if (!$vacancyOpen) {
            return response()->json([
                'message' => 'Ushbu vakansiya faol holatda emas'
            ], 400);
        }

        $click = Click::where('user_id', auth('api')->id())
            ->where('vacancy_id', $request->vacancy_id)
            ->first();
        if ($click->sent) {
            return response()->json([
                'message' => 'Ushbu vakansiya arizangiz ko\'rib chiqilgan'
            ], 400);
        }
        $deletedClick = Click::onlyTrashed()
            ->where('user_id', auth('api')->id())
            ->where('vacancy_id', $request->vacancy_id)
            ->latest()
            ->first();
        if ($deletedClick) {
            $softDeleted = DocUserHistory::onlyTrashed()
                ->where('click_id', $deletedClick->id)
                ->get();
            foreach ($softDeleted as $softDelete) {
                if (!$softDelete->path) {
                    continue;
                }
                $hasDoc = DocUser::where('user_id', $softDelete->user_id)
                    ->where('dir_demand_id', $softDelete->dir_demand_id)
                    ->where('path', $softDelete->path)
                    ->exists();
                if (!$hasDoc) {
                    Storage::disk('private')->delete($softDelete->path);
                }
            }
        }

        if ($click) {
            $docUsers = DocUserHistory::where('click_id', $click->id)->get();

            foreach ($docUsers as $docUser) {
                if (!$docUser->path) {
                    continue;
                }

                $hasDoc = DocUser::where('user_id', $docUser->user_id)
                    ->where('dir_demand_id', $docUser->dir_demand_id)
                    ->where('path', $docUser->path)
                    ->exists();

                if (!$hasDoc) {
                    Storage::disk('private')->delete($docUser->path);
                }
            }

            DocUserHistory::where('click_id', $click->id)->delete();

            $click->delete();
        }


        return response()->json([
            'message' => 'Click deleted successfully'
        ]);
    }

    public function adminUserClicks(Request $request)
    {
        $admin = auth('apiAdmin')->user();
        $vacancy_id = $request->vacancy_id;
        if ($vacancy_id){
            $userClickGet = Click::where('vacancy_id', $vacancy_id)
                ->with([
                    'user:id,name,pinfl,phone,photo_user',
                    'doc_histories:id,click_id,user_id,path,dir_demand_id',
                    'doc_histories.demand:id,title',
                    'userDocs',
                ])
                ->get();

        }
        return response()->json($userClickGet);


    }

    public function exportUsers(Request $request)
    {
        if (!$request->has('vacancy_id')) {
            return response()->json(['error' => 'Vacancy ID berilmadi'], 400);
        }
        try {
            $export = new \App\Exports\UsersInfoExport($request->vacancy_id);
            return \Maatwebsite\Excel\Facades\Excel::download(
                $export,
                'users.xlsx'
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Xatolik yuz berdi',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function responseClick(Request $request)
    {
        $this->defaultAdminService->errAllVacancyView();

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:clicks,id',
            'comment' => 'required|string',
            'sent' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $admin = auth('apiAdmin')->id();
        $click = Click::findOrFail($request->id);
        $oldValues = $click;

        $click->update([
            'comment' => $request->comment,
            'sent' => $request->sent,
            'admin_id' => $admin
        ]);
        $newValues = $click;
        ClickLog::create([
            'admin_id'  => $admin,
            'model'     => Click::class,
            'action'    => 'response_click',
            'old_values'=> json_encode($oldValues, JSON_UNESCAPED_UNICODE),
            'new_values'=> json_encode($newValues, JSON_UNESCAPED_UNICODE),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Qabul qilindi']);

    }
}
