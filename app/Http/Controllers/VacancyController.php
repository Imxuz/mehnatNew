<?php

namespace App\Http\Controllers;

use App\Models\Demand;
use App\Models\Vacancy;
use App\Http\Requests\StoreVacancyRequest;
use App\Http\Requests\UpdateVacancyRequest;
use App\Models\VacancyLog;
use Illuminate\Support\Facades\DB;

class VacancyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(){
        $vacancies = Vacancy::with('demands.dir_demand','occupation','region')->get();
        return response()->json($vacancies);
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
                'open_at'        => $request->open_at,
                'close_at'       => $request->close_at,
            ]);

            $demands = $request->input('demands', []);

            foreach ($demands as $demand) {
                if (!empty($demand['dir_demand_id']) || !empty($demand['score']) || !empty($demand['adder_text'])) {
                    Demand::create([
                        'dir_demand_id' => $demand['dir_demand_id'] ?? null,
                        'adder_text'    => json_encode($demand['adder_text'], JSON_UNESCAPED_UNICODE) ?? null,
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

        DB::beginTransaction();

        try {
            $oldVacancy = $vacancy->toArray();
            $oldDemands = Demand::where('vacancy_id', $vacancy->id)->get()->toArray();

            $vacancy->update([
                'region_id'      => $request->region_id,
                'admin_id'       => $admin->id,
                'occupation_id'  => $request->occupation_id,
                'open_at'        => $request->open_at,
                'close_at'       => $request->close_at,
            ]);
            Demand::where('vacancy_id', $vacancy->id)->delete();
            $newDemands = [];
            foreach ($request->input('demands', []) as $demand) {
                if (!empty($demand['dir_demand_id']) || !empty($demand['score']) || !empty($demand['adder_text'])) {
                    $new = Demand::create([
                        'dir_demand_id' => $demand['dir_demand_id'] ?? null,
                        'adder_text'    => json_encode($demand['adder_text'], JSON_UNESCAPED_UNICODE) ?? null,
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
        //
    }
    public function publication(Vacancy $vacancy)
    {
        $admin = auth('apiAdmin')->user();
        $vacancy->update(['publish_admin_id'=>$admin->id]);
    }
}
