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
use App\Services\AiAnalysisService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ClickController extends Controller implements HasMiddleware
{
    protected ?string $hrUrl = null;

    public function __construct(private DefaultAdminService $defaultAdminService)
    {
        $this->hrUrl = (string) (config('services.hr_mehnat.api_url') ?: env('HR_MEHNAT_API', ''));
    }

    /**
     * Display a listing of the resource.
     */

    public static function middleware(): array
    {
        return [
            new Middleware('throttle:click_destroy', only: ['destroy']),
        ];
    }
    public function index()
    {
        $userId = auth('api')->id();
        $clickVacancies = Click::where('user_id', $userId)->pluck('vacancy_id');
        $userVacancies = Vacancy::with('demands.dir_demand', 'occupation', 'region', 'oClick')
            ->whereIn('id', $clickVacancies)
            ->orderBy('open_at', 'desc')->get();
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
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Bunday foydalanuvchi mavjud emas.',
            ], 400);
        }
        if (!$userInfo->photo_user) {
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
        $ClickCheck = Click::where('user_id', $user)->where('vacancy_id', $vacancy->id)->first();
        if ($ClickCheck) {
            return response()->json([
                'status' => false,
                'message' => 'Siz ushbu arizaga so\'rov qoldirgansiz.',
            ], 400);
        }

        $demands = Demand::where('vacancy_id', $vacancy->id)->pluck('dir_demand_id');
        $docUser = DocUser::where('user_id', $user)->where('check', 1)->pluck('dir_demand_id');
        $missingDemands = $demands->diff($docUser);
        if ($missingDemands->isNotEmpty()) {
            $errors = DirDemand::whereIn('id', $missingDemands)
                ->pluck('title');
            return response()->json([
                'status' => false,
                'message' => 'Quyidagi hujjatlar tasdiqlanmagan yoki mavjud emas',
                'errors' => $errors,
            ], 422);
        } else {
            $click_id = Click::create([
                'user_id' => $user,
                'vacancy_id' => $request->vacancy_id,
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
                    'click_id' => $click_id,
                    'user_id' => $docUser->user_id,
                    'dir_demand_id' => $docUser->dir_demand_id,
                    'path' => $docUser->path,
                    'adder_demands_id' => $docUser->adder_demands_id,
                    'doc_info' => $docUser->doc_info,
                    'description' => $docUser->description,
                    'ip_address' => $docUser->ip_address,
                ]);
            }

            $url = $this->hrUrl . '/vacancy/oclick-save/' . $click_id;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1);
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
        $vacancies = Vacancy::where('id', $id)->with('demands.dir_demand', 'occupation', 'region')->first();
        if ($vacancies->close_at < now() && $vacancies->publication == null) {
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

        if (!$click) {
            return response()->json([
                'message' => 'Ushbu vakansiyaga ariza topilmadi'
            ], 404);
        }

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

    public function adminUserClicks(Request $request, AiAnalysisService $aiAnalysisService)
    {
        $admin = auth('apiAdmin')->user();
        $vacancy_id = $request->vacancy_id;

        if (!$vacancy_id) {
            return response()->json([
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'total' => 0,
                'per_page' => (int) $request->input('per_page', 50),
            ]);
        }

        $perPage = (int) $request->input('per_page', 50);
        if ($perPage <= 0 || $perPage > 200) {
            $perPage = 50;
        }

        $query = Click::where('vacancy_id', $vacancy_id);

        // Qidiruv filtrlari: Ism, telefon, ID
        $name = trim((string) ($request->input('name') ?: $request->input('search')));
        if (!empty($name)) {
            $query->whereHas('user', function ($q) use ($name) {
                $q->where('name', 'like', "%{$name}%");
            });
        }

        $phone = trim((string) $request->input('phone'));
        if (!empty($phone)) {
            $query->whereHas('user', function ($q) use ($phone) {
                $q->where('phone', 'like', "%{$phone}%");
            });
        }

        $id = trim((string) $request->input('id'));
        if (!empty($id)) {
            $query->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('user_id', $id);
            });
        }

        // Status filtri ('null' / 'accept' / 'cancel')
        if ($request->has('sent') && $request->input('sent') !== '' && $request->input('sent') !== 'all') {
            $sent = $request->input('sent');
            if ($sent === 'null') {
                $query->whereNull('sent');
            } else {
                $query->where('sent', $sent);
            }
        }

        // AI Balli bo'yicha saralash yoki filtr
        $sortByScore = $request->input('sortByScore');
        if (in_array($sortByScore, ['desc', 'above_50'], true)) {
            $sortedOclickIds = $aiAnalysisService->getSortedOclickIds($vacancy_id, $sortByScore);

            if (!empty($sortedOclickIds)) {
                $query->whereIn('id', $sortedOclickIds);
                $idsString = implode(',', $sortedOclickIds);
                $query->orderByRaw("FIELD(id, {$idsString})");
            } else {
                $query->whereRaw('1 = 0');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $query->with([
            'user:id,name,pinfl,phone,photo_user',
            'doc_histories:id,click_id,user_id,path,dir_demand_id,adder_demands_id',
            'doc_histories.demand:id,title',
            'userDocs',
            'createdByAdmin:id,name,surname,middle_name,job_title',
            'logs' => function ($q) {
                $q->with('admin:id,name,surname,middle_name,job_title,email')
                    ->orderBy('created_at', 'desc');
            },
        ]);

        $paginated = $query->paginate($perPage);

        // Faqat shu sahifadagi arizalar uchun AI tahlilni biriktirish
        $aiAnalysisService->attachToClicks($paginated->items(), $vacancy_id);

        // Har bir arizaga oxirgi javob vaqtini biriktirish
        foreach ($paginated->items() as $click) {
            $responseLogs = $click->logs->where('action', 'response_click');
            $latestResponseLog = $responseLogs->first();

            if ($latestResponseLog) {
                $click->last_response_at = $latestResponseLog->created_at?->toISOString() ?? (string) $latestResponseLog->created_at;
                $click->last_response_admin = $latestResponseLog->admin ? [
                    'id' => $latestResponseLog->admin->id,
                    'name' => $latestResponseLog->admin->name,
                    'surname' => $latestResponseLog->admin->surname,
                    'middle_name' => $latestResponseLog->admin->middle_name,
                    'job_title' => $latestResponseLog->admin->job_title,
                ] : null;
            } elseif ($click->sent !== null) {
                $click->last_response_at = $click->updated_at?->toISOString() ?? (string) $click->updated_at;
                $click->last_response_admin = null;
            } else {
                $click->last_response_at = null;
                $click->last_response_admin = null;
            }
        }

        return response()->json($paginated);
    }

    /**
     * Rasmiy asos hujjati bilan admin tomonidan nomzod nomidan ariza kiritish
     */
    public function storeManualClick(Request $request)
    {
        $admin = auth('apiAdmin')->user();
        if (!$admin) {
            return response()->json([
                'status' => false,
                'message' => 'Admin autentifikatsiyadan o‘tmagan.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'vacancy_id' => 'required|integer|exists:vacancies,id',
            'user_id' => 'required|integer|exists:users,id',
            'basis_file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
            'basis_comment' => 'required|string|max:1000',
        ], [
            'vacancy_id.required' => 'Vakansiya tanlanishi shart.',
            'vacancy_id.exists' => 'Bunday vakansiya topilmadi.',
            'user_id.required' => 'Nomzod tanlanishi shart.',
            'user_id.exists' => 'Bunday nomzod tizimda mavjud emas.',
            'basis_file.required' => 'Asos hujjati yuklanishi shart.',
            'basis_file.mimes' => 'Fayl formati PDF, JPG, PNG yoki DOC/DOCX bo‘lishi kerak.',
            'basis_file.max' => 'Fayl hajmi 10MB dan oshmasligi kerak.',
            'basis_comment.required' => 'Asos izohi (xat/buyruq raqami va sanasi) kiritilishi shart.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Maʼlumotlar to‘liq yoki to‘g‘ri kiritilmadi.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $vacancy = Vacancy::find($request->vacancy_id);
        if (!$vacancy) {
            return response()->json([
                'status' => false,
                'message' => 'Vakansiya topilmadi.',
            ], 404);
        }

        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Nomzod topilmadi.',
            ], 404);
        }

        // Tekshirish: nomzod allaqachon ushbu vakansiyaga ariza topshirganmi?
        $existingClick = Click::where('user_id', $user->id)
            ->where('vacancy_id', $vacancy->id)
            ->first();

        if ($existingClick) {
            return response()->json([
                'status' => false,
                'message' => 'Ushbu nomzod mazkur vakansiyaga oldin ariza topshirgan.',
            ], 400);
        }

        // Faylni private storage ga yuklash
        $file = $request->file('basis_file');
        $filename = 'basis_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('uploads/basis_files', $filename, 'private');

        // Ariza (Click) va loglarni tranzaksiya ichida yaratish
        $click = DB::transaction(function () use ($request, $user, $vacancy, $admin, $path) {
            $click = Click::create([
                'user_id' => $user->id,
                'vacancy_id' => $vacancy->id,
                'is_manual' => true,
                'created_by_admin_id' => $admin->id,
                'basis_file' => $path,
                'basis_comment' => $request->basis_comment,
            ]);

            // Nomzodning mavjud tekshirilgan hujjatlari bo'lsa, ularni ham nusxalash
            $demands = Demand::where('vacancy_id', $vacancy->id)->pluck('dir_demand_id');
            $docUsers = DocUser::where('user_id', $user->id)
                ->where('check', 1)
                ->whereIn('dir_demand_id', $demands)
                ->get();

            if ($docUsers->isNotEmpty()) {
                DocUser::where('user_id', $user->id)
                    ->where('check', 1)
                    ->whereIn('dir_demand_id', $demands)
                    ->update([
                        'vacancy_doc_id' => $vacancy->id,
                    ]);

                foreach ($docUsers as $docUser) {
                    DocUserHistory::create([
                        'click_id' => $click->id,
                        'user_id' => $docUser->user_id,
                        'dir_demand_id' => $docUser->dir_demand_id,
                        'path' => $docUser->path,
                        'adder_demands_id' => $docUser->adder_demands_id,
                        'doc_info' => $docUser->doc_info,
                        'description' => $docUser->description,
                        'ip_address' => $request->ip() ?: $docUser->ip_address,
                    ]);
                }
            }

            // Audit jurnali (ClickLog) - old_values bo'sh JSON bo'lishi shart (NOT NULL)
            ClickLog::create([
                'admin_id' => $admin->id,
                'click_id' => $click->id,
                'model' => Click::class,
                'action' => 'manual_click_create',
                'old_values' => [],
                'new_values' => $click->toArray(),
            ]);

            return $click;
        });

        // Agar HR_MEHNAT_API sozlangan bo'lsa, Java backendga xabar berish
        if (!empty($this->hrUrl)) {
            $url = $this->hrUrl . '/vacancy/oclick-save/' . $click->id;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 300);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_exec($ch);
            curl_close($ch);
        }

        return response()->json([
            'status' => true,
            'message' => 'Nomzod asos hujjati bilan vakansiyaga muvaffaqiyatli qo‘shildi.',
            'data' => $click->load(['user:id,name,pinfl,phone,photo_user', 'createdByAdmin:id,name,surname']),
        ], 201);
    }

    public function exportUsers(Request $request, AiAnalysisService $aiAnalysisService)
    {
        if (!$request->has('vacancy_id')) {
            return response()->json(['error' => 'Vacancy ID berilmadi'], 400);
        }
        try {
            $filters = $request->only(['name', 'phone', 'id', 'sent', 'sortByScore', 'search']);
            $export = new \App\Exports\UsersInfoExport((int) $request->vacancy_id, $filters, $aiAnalysisService);
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

    public function responseClick(Request $request, AiAnalysisService $aiAnalysisService)
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
        $click = Click::with(['doc_histories', 'userDocs', 'clickVacancy.demands'])->findOrFail($request->id);

        // Agar arizani rad etish ('cancel') tanlangan bo'lsa va nomzod barcha asosiy talablarga (100%) to'g'ri kelsa:
        if ($request->sent === 'cancel' && $click->clickVacancy) {
            $matchSummary = $aiAnalysisService->checkClickAiMainDemandsMatch($click);
            if (!empty($matchSummary['matches_all_main'])) {
                $comment = trim((string) $request->comment);

                // Standart/shablon xabarlar
                $defaultUz = "Sizning nomzodingiz ariza topshirgan bo‘sh ish o‘rniga qo‘yilgan talablarga mos kelmaganligi aniqlandi, Siz bu lavozim bo‘yicha tanlovda ishtirok etolmaysiz.";
                $defaultRu = "Ваша кандидатура не соответствует требованиям, установленным для данной вакантной должности. Вы не сможете продолжить участие в конкурсе по этому направлению.";

                if (empty($comment) || $comment === $defaultUz || $comment === $defaultRu || mb_strlen($comment) < 15) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Ushbu nomzod sun\'iy intellekt (AI) tahlili bo‘yicha vakansiyaning barcha asosiy talablariga (100%) to‘liq javob beradi. Uni rad etish uchun nega asosiy talablarga javob bersa ham rad etilayotgani to‘g‘risida batafsil asosli izoh kiritish majburiy!'
                    ], 422);
                }
            }
        }

        $oldValues = $click->toArray();

        $click->update([
            'comment' => $request->comment,
            'sent' => $request->sent,
            'admin_id' => $admin
        ]);
        $newValues = $click->toArray();
        ClickLog::create([
            'admin_id' => $admin,
            'click_id' => $click->id,
            'model' => Click::class,
            'action' => 'response_click',
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Qabul qilindi']);

    }
}
