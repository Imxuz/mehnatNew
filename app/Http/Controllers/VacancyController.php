<?php

namespace App\Http\Controllers;

use App\Models\Demand;
use App\Models\Vacancy;
use App\Http\Requests\StoreVacancyRequest;
use App\Http\Requests\UpdateVacancyRequest;
use Illuminate\Support\Facades\DB;

class VacancyController extends Controller
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
                'publication'    => $request->publication ?? false,
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
    public function show(Vacancy $vacancy)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVacancyRequest $request, Vacancy $vacancy)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vacancy $vacancy)
    {
        //
    }
}
