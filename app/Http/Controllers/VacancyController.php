<?php

namespace App\Http\Controllers;

use App\Models\Demand;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Region;
use App\Models\Vacancy;
use App\Http\Requests\StoreVacancyRequest;
use App\Http\Requests\UpdateVacancyRequest;
use App\Models\VacancyLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VacancyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $admin = auth('apiAdmin')->user();
        $regionAdmin = Region::find($admin->region_id);
        $query = Vacancy::with('demands.dir_demand', 'occupation', 'divRegion','admin')->withCount('clicks')
            ->orderBy('created_at', 'desc');
        if ($admin->hasPermission('sub-region-vacancy')) {
            $query->where('region_id', $admin->region_id);

        } elseif ($admin->hasPermission('region-vacancy')) {
            $query->whereHas('divRegion', function ($q) use ($regionAdmin,$admin) {
                $q->where(function ($sub) use ($regionAdmin,$admin) {
                    if (!empty($regionAdmin->div_region)) {
                        $sub->orWhere('div_region', $regionAdmin->div_region);
                    }
                    if (!empty($regionAdmin->division)) {
                        $sub->orWhere('division', $regionAdmin->division);
                    }
                    $sub->orWhere('admin_id', $admin->id)
                        ->orWhere('division', $regionAdmin->id)
                        ->orWhere('div_region', $regionAdmin->id);
                });
            });
        }elseif(!($admin->hasPermission('all-vacancy-view'))) {
            return response()->json(['error'=>"Sizda bunday huquq yo'q"],403);
        }
        $status = $request->status;
        if ($status){
            if ($status === 'active') {
                $query->where('close_at', '>=', now()) ->whereNotNull('publication');
            } elseif ($status === 'archive') {
                $query->where('close_at', '<=', now())->whereNotNull('publication');
            } elseif ($status === 'published') {
                $query->whereNotNull('publication'); }
            elseif ($status === 'unpublished') {
                $query->whereNull('publication');}
        }
        $query->when($request->admin_id, function ($q, $adminId) {
            $q->where('admin_id', $adminId);
        });

        $perPage = $request->get('per_page', 2);
        return response()->json($query->paginate($perPage));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVacancyRequest $request)
    {
        $admin = auth('apiAdmin')->user();
        DB::beginTransaction();
        try {
            $vacancy = Vacancy::create([
                'region_id'      => $request->region_id,
                'admin_id'       => $admin->id,
                'occupation_id'  => $request->occupation_id,
                'position'  => $request->position,
                'helpline'  => $request->helpline,
                'description' => json_encode([
                    'ru' => $request->description_ru,
                    'uz' => $request->description_uz,
                ], JSON_UNESCAPED_UNICODE),
                'specials'      =>  $request->specials,
            ]);

            $demands = $request->input('demands', []);

            foreach ($demands as $demand) {
                if (!empty($demand['dir_demand_id']) || !empty($demand['score']) || !empty($demand['adder_text'])) {
                    Demand::create([
                        'dir_demand_id' => $demand['dir_demand_id'] ?? null,
                        'adder_text'    => $demand['adder_text'] ?? null,
                        'vacancy_id'    => $vacancy->id,
                        'score'         => $demand['score'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Vakansiya muvaffaqiyatli yaratildi!',
                'vacancy_id' => $vacancy->id,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Xatolik yuz berdi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        if (!is_numeric($id) || intval($id) != $id) {
            return response()->json(['error' => 'Invalid ID'], 400);
        }
        $vacancies = Vacancy::where('id', $id)
            ->with('demands.dir_demand')
            ->get();

        return response()->json($vacancies);
    }

    /**
     * Update the specified resource in storage.
     */
        public function update(UpdateVacancyRequest $request, Vacancy $vacancy)
    {
        $admin = auth('apiAdmin')->user();
        if (!$admin->hasPermission('publication-vacancy-change')) {
            abort(403, 'Sizda bu amalni bajarish huquqi yo‘q');
        }
        DB::beginTransaction();
        try {
            $oldVacancy = $vacancy->toArray();
            $oldDemands = Demand::where('vacancy_id', $vacancy->id)->get()->toArray();

            $vacancy->update([
                'region_id'      => $request->region_id,
                'admin_id'       => $admin->id,
                'occupation_id'  => $request->occupation_id,
                'position'  => $request->position,
                'helpline'  => $request->helpline,
                'specials'      =>  $request->specials,
                'description' => json_encode([
                    'ru' => $request->description_ru,
                    'uz' => $request->description_uz,
                ], JSON_UNESCAPED_UNICODE),


            ]);
            Demand::where('vacancy_id', $vacancy->id)->delete();
            $newDemands = [];
            foreach ($request->input('demands', []) as $demand) {
                if (!empty($demand['dir_demand_id']) || !empty($demand['score']) || !empty($demand['adder_text'])) {
                    $new = Demand::create([
                        'dir_demand_id' => $demand['dir_demand_id'] ?? null,
                        'adder_text'    => $demand['adder_text'] ?? null,
                        'vacancy_id'    => $vacancy->id,
                        'score'         => $demand['score'] ?? null,
                    ]);
                    $newDemands[] = $new->toArray();
                }
            }
            VacancyLog::create([
                'admin_id'  => $admin->id,
                'model'     => Vacancy::class,
                'action'    => 'update',
                'old_values' => [
                    'vacancy' => $oldVacancy,
                    'demands' => $oldDemands,
                ],
                'new_values' => [
                    'vacancy' => $vacancy->fresh()->toArray(),
                    'demands' => $newDemands,
                ],
            ]);
            DB::commit();
            return response()->json([
                'message' => 'Vakansiya muvaffaqiyatli yangilandi!',
                'vacancy_id' => $vacancy->id,
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Xatolik: ' . $e->getMessage(),
            ], 500);
        }
    }




    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vacancy $vacancy)
    {
        $admin = auth('apiAdmin')->user();
        if (!$admin->hasPermission('delete-vacancy')) {
            abort(403, 'Sizda bu amalni bajarish huquqi yo‘q');
        }
        $oldVacancy = $vacancy->toArray();
        $oldDemands = $vacancy->demands()->get()->toArray();
        VacancyLog::create([
            'admin_id'   => $admin->id,
            'model'      => Vacancy::class,
            'action'     => 'delete',
            'old_values' => [
                'vacancy' => $oldVacancy,
                'demands' => $oldDemands,
            ],
            'new_values' => [
                'admin_id'   => $admin->id,
            ],
        ]);
        $vacancy->delete();
        return response()->json([
            'message' => 'Vakansiya muvaffaqiyatli o‘chirildi'
        ]);
    }

    public function publication(Request $request)
    {
        $admin = auth('apiAdmin')->user();
        if (!$admin) {
            return response()->json(['error' => 'Admin not authenticated'], 401);
        }
        $vacancy = Vacancy::find($request->id);
        if (!$vacancy) {
            return response()->json(['error' => 'Vacancy not found'], 404);
        }
        if ($vacancy->publication) {
            return response()->json(['error' => 'Ushbu vakansiya publicatsiya qilingan'], 400);
        }
        $oldVacancy = $vacancy->toArray();
        $oldDemands = $vacancy->demands()->get()->toArray();
        $vacancy->publication = $admin->id;
        $vacancy->open_at = now();
        $vacancy->close_at = now()->addDay(10)->setTime(17, 0, 0);
        $vacancy->save();
        VacancyLog::create([
            'admin_id'   => $admin->id,
            'model'      => Vacancy::class,
            'action'     => 'publish',
            'old_values' => [
                'vacancy' => $oldVacancy,
                'demands' => $oldDemands,
            ],
            'new_values' => [
                'publication' => $vacancy->publication,
                'open_at'     => $vacancy->open_at,
                'close_at'    => $vacancy->close_at,
                'admin_id'   => $admin->id,
            ],
        ]);

        return response()->json([
            'message' => 'Vakansiya muvaffaqiyatli chop etildi',
            'data'    => $vacancy
        ]);
    }

    public function viewCount(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:vacancies,id'
        ]);
        try {
            $vacancy = Vacancy::findOrFail($request->id);
            $vacancy->increment('view_count');
            return response()->json([
                'success' => true,
                'new_count' => $vacancy->view_count
            ], 200);

        } catch (\Exception $e) {
//            return response()->json([
//                'success' => false,
//                'message' => 'Xatolik yuz berdi'
//            ], 500);
        }
    }
}
