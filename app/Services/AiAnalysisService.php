<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AiAnalysisService
{
    /**
     * Berilgan vakansiya ID bo'yicha PostgreSQL (db_hr) bazasidan barcha oclick va aidescription ma'lumotlarini olish
     */
    public function getAnalysisForVacancy(int|string $vacancyId): array
    {
        try {
            $vacancyIdStr = (string) $vacancyId;

            // 1. PostgreSQL db_hr bazasidan vacancy_id bo'yicha oclick yozuvlarini olish
            $oclicks = DB::connection('pgsql_hr')
                ->table('oclick')
                ->select(['id', 'oclick_id', 'vacancy_id', 'fio', 'comments'])
                ->where('vacancy_id', $vacancyIdStr)
                ->where(function ($q) {
                    $q->whereNull('is_deleted')->orWhere('is_deleted', false);
                })
                ->get();

            if ($oclicks->isEmpty()) {
                return [
                    'by_oclick_id' => [],
                    'by_fio' => [],
                ];
            }

            $oclickIds = $oclicks->pluck('id')->toArray();

            // 2. MySQL dan ushbu vakansiyaning asosiy ('main') talablarini olish
            $mainDemandDirIds = DB::table('demands')
                ->where('vacancy_id', $vacancyId)
                ->where('demand_type', 'main')
                ->pluck('dir_demand_id')
                ->map(fn($id) => (int)$id)
                ->toArray();
            $totalMainCount = count($mainDemandDirIds);

            // 3. Ushbu oclick'larga tegishli aidescription yozuvlarini olish (kerakli ustunlar bilan)
            $descriptions = DB::connection('pgsql_hr')
                ->table('aidescription')
                ->select(['id', 'oclick_id', 'sort_num', 'rating', 'max_rating', 'title_uz', 'title_ru', 'comment_uz', 'comment_ru'])
                ->whereIn('oclick_id', $oclickIds)
                ->where(function ($q) {
                    $q->whereNull('is_deleted')->orWhere('is_deleted', false);
                })
                ->orderBy('sort_num', 'asc')
                ->orderBy('id', 'asc')
                ->get()
                ->groupBy('oclick_id');

            // 4. Har bir nomzod bo'yicha tahlil va asosiy talablar mosligini hisoblash
            $byOclickId = [];
            $byFio = [];

            foreach ($oclicks as $oclick) {
                $descList = $descriptions->get($oclick->id, collect());

                $score = (int) $descList->sum('rating');
                $maxScore = (int) $descList->sum('max_rating');
                $percentage = $maxScore > 0 ? (int) round(($score / $maxScore) * 100) : 0;

                // AI asosiy talablarga 100% mos kelganligini tekshirish
                $matchesAllMain = false;
                $matchedMainCount = 0;

                if ($totalMainCount > 0 && $descList->isNotEmpty()) {
                    $allMatched = true;
                    foreach ($mainDemandDirIds as $dirId) {
                        $item = $descList->first(function ($d) use ($dirId) {
                            return (int)($d->sort_num ?? 0) === $dirId;
                        });

                        if (!$item) {
                            $allMatched = false;
                            continue;
                        }

                        $rating = (int) $item->rating;
                        $maxRating = (int) $item->max_rating;
                        $isMatch = ($maxRating > 0 && $rating >= $maxRating) || ($rating > 0 && $maxRating == 0);

                        if ($isMatch) {
                            $matchedMainCount++;
                        } else {
                            $allMatched = false;
                        }
                    }

                    $matchesAllMain = $allMatched;
                }

                $formattedDescriptions = $descList->map(function ($d) use ($mainDemandDirIds) {
                    $sortNum = (int) ($d->sort_num ?? 0);
                    $rating = (int) $d->rating;
                    $maxRating = (int) $d->max_rating;
                    $isMatch = ($maxRating > 0 && $rating >= $maxRating) || ($rating > 0 && $maxRating == 0);

                    return [
                        'id' => $d->id,
                        'title_uz' => $d->title_uz,
                        'title_ru' => $d->title_ru,
                        'comment_uz' => $d->comment_uz,
                        'comment_ru' => $d->comment_ru,
                        'rating' => $rating,
                        'max_rating' => $maxRating,
                        'sort_num' => $sortNum,
                        'is_main' => in_array($sortNum, $mainDemandDirIds),
                        'is_matched' => $isMatch,
                    ];
                })->values()->toArray();

                $analysisData = [
                    'oclick_id' => $oclick->oclick_id,
                    'pg_oclick_id' => $oclick->id,
                    'vacancy_id' => $oclick->vacancy_id,
                    'fio' => $oclick->fio,
                    'score' => $score,
                    'max_score' => $maxScore,
                    'percentage' => $percentage,
                    'comments' => $oclick->comments,
                    'descriptions' => $formattedDescriptions,
                    'main_demands_count' => $totalMainCount,
                    'main_demands_matched_count' => $matchedMainCount,
                    'matches_all_main' => $matchesAllMain,
                    'ai_main_matched' => $matchesAllMain,
                ];

                if (!empty($oclick->oclick_id)) {
                    $byOclickId[(string) $oclick->oclick_id] = $analysisData;
                }

                if (!empty($oclick->fio)) {
                    $normFio = $this->normalizeFio($oclick->fio);
                    if ($normFio) {
                        $byFio[$normFio] = $analysisData;
                    }
                }
            }

            return [
                'by_oclick_id' => $byOclickId,
                'by_fio' => $byFio,
            ];

        } catch (\Throwable $e) {
            Log::warning("AiAnalysisService connection failed or error: " . $e->getMessage());
            return [
                'by_oclick_id' => [],
                'by_fio' => [],
            ];
        }
    }

    /**
     * Eloquent Click to'plamiga AI tahlil ma'lumotlarini biriktirish
     */
    public function attachToClicks($clicks, int|string $vacancyId)
    {
        $analysisMap = $this->getAnalysisForVacancy($vacancyId);
        $byOclickId = $analysisMap['by_oclick_id'] ?? [];
        $byFio = $analysisMap['by_fio'] ?? [];

        foreach ($clicks as $click) {
            $clickId = (string) $click->id;
            $userName = $click->user->name ?? '';
            $normName = $this->normalizeFio($userName);

            $matchedAnalysis = null;
            if (isset($byOclickId[$clickId])) {
                $matchedAnalysis = $byOclickId[$clickId];
            } elseif ($normName && isset($byFio[$normName])) {
                $matchedAnalysis = $byFio[$normName];
            }

            $click->ai_analysis = $matchedAnalysis;
            $click->main_demands_summary = $matchedAnalysis ? [
                'matches_all_main' => $matchedAnalysis['matches_all_main'],
                'ai_main_matched' => $matchedAnalysis['ai_main_matched'],
                'main_demands_count' => $matchedAnalysis['main_demands_count'],
                'main_demands_matched_count' => $matchedAnalysis['main_demands_matched_count'],
            ] : null;
        }

        return $clicks;
    }

    /**
     * Berilgan vakansiya bo'yicha AI ballari asosida saralangan yoki filtrlangan oclick_id larni olish
     */
    public function getSortedOclickIds(int|string $vacancyId, string $mode = 'desc'): array
    {
        try {
            $query = DB::connection('pgsql_hr')
                ->table('oclick as o')
                ->leftJoin('aidescription as d', function ($join) {
                    $join->on('d.oclick_id', '=', 'o.id')
                         ->where(function ($q) {
                             $q->whereNull('d.is_deleted')->orWhere('d.is_deleted', false);
                         });
                })
                ->where('o.vacancy_id', (string)$vacancyId)
                ->where(function ($q) {
                    $q->whereNull('o.is_deleted')->orWhere('o.is_deleted', false);
                })
                ->groupBy('o.id', 'o.oclick_id')
                ->selectRaw("o.oclick_id, 
                    CASE WHEN SUM(COALESCE(d.max_rating, 0)) > 0 
                         THEN (SUM(COALESCE(d.rating, 0))::float / SUM(COALESCE(d.max_rating, 0))) 
                         ELSE 0 END as pct,
                    SUM(COALESCE(d.rating, 0)) as total_score");

            if ($mode === 'above_50') {
                $query->havingRaw("(SUM(COALESCE(d.max_rating, 0)) > 0 AND (SUM(COALESCE(d.rating, 0))::float / SUM(COALESCE(d.max_rating, 0))) >= 0.5)");
            }

            $query->orderByDesc('pct')->orderByDesc('total_score');

            return $query->pluck('o.oclick_id')
                ->filter()
                ->map(fn($id) => (int)$id)
                ->values()
                ->toArray();
        } catch (\Throwable $e) {
            Log::warning("AiAnalysisService getSortedOclickIds error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Bitta Click (ariza) bo'yicha AI asosiy talablarga (100%) to'g'ri kelganligini tekshirish
     */
    public function checkClickAiMainDemandsMatch($click): array
    {
        try {
            $vacancyId = $click->vacancy_id ?? null;
            if (!$vacancyId && $click->clickVacancy) {
                $vacancyId = $click->clickVacancy->id;
            }

            if (!$vacancyId) {
                return [
                    'has_ai_analysis' => false,
                    'has_main_demands' => false,
                    'matches_all_main' => false,
                    'ai_main_matched' => false,
                    'total_main' => 0,
                    'matched_main' => 0,
                    'details' => [],
                ];
            }

            // MySQL dagi ushbu vakansiyaning asosiy talablari
            $mainDemandDirIds = DB::table('demands')
                ->where('vacancy_id', $vacancyId)
                ->where('demand_type', 'main')
                ->pluck('dir_demand_id')
                ->map(fn($id) => (int)$id)
                ->toArray();

            $totalMainCount = count($mainDemandDirIds);
            if ($totalMainCount === 0) {
                return [
                    'has_ai_analysis' => false,
                    'has_main_demands' => false,
                    'matches_all_main' => false,
                    'ai_main_matched' => false,
                    'total_main' => 0,
                    'matched_main' => 0,
                    'details' => [],
                ];
            }

            // Postgres db_hr dan oclick ni topish
            $clickIdStr = (string)$click->id;
            $pgOclick = DB::connection('pgsql_hr')
                ->table('oclick')
                ->where('oclick_id', $clickIdStr)
                ->where(function ($q) {
                    $q->whereNull('is_deleted')->orWhere('is_deleted', false);
                })
                ->first();

            if (!$pgOclick && !empty($click->user->name)) {
                $normFio = $this->normalizeFio($click->user->name);
                $pgOclicks = DB::connection('pgsql_hr')
                    ->table('oclick')
                    ->where('vacancy_id', (string)$vacancyId)
                    ->where(function ($q) {
                        $q->whereNull('is_deleted')->orWhere('is_deleted', false);
                    })
                    ->get();

                foreach ($pgOclicks as $oc) {
                    if ($this->normalizeFio($oc->fio) === $normFio) {
                        $pgOclick = $oc;
                        break;
                    }
                }
            }

            if (!$pgOclick) {
                return [
                    'has_ai_analysis' => false,
                    'has_main_demands' => true,
                    'matches_all_main' => false,
                    'ai_main_matched' => false,
                    'total_main' => $totalMainCount,
                    'matched_main' => 0,
                    'details' => [],
                ];
            }

            // Aidescription yozuvlarini olish
            $descList = DB::connection('pgsql_hr')
                ->table('aidescription')
                ->where('oclick_id', $pgOclick->id)
                ->where(function ($q) {
                    $q->whereNull('is_deleted')->orWhere('is_deleted', false);
                })
                ->get();

            if ($descList->isEmpty()) {
                return [
                    'has_ai_analysis' => false,
                    'has_main_demands' => true,
                    'matches_all_main' => false,
                    'ai_main_matched' => false,
                    'total_main' => $totalMainCount,
                    'matched_main' => 0,
                    'details' => [],
                ];
            }

            $allMatched = true;
            $matchedMainCount = 0;
            $details = [];

            foreach ($mainDemandDirIds as $dirId) {
                $item = $descList->first(function ($d) use ($dirId) {
                    return (int)($d->sort_num ?? 0) === $dirId;
                });

                if (!$item) {
                    $allMatched = false;
                    $details[] = [
                        'dir_demand_id' => $dirId,
                        'matched' => false,
                        'reason' => 'missing_in_ai_analysis',
                    ];
                    continue;
                }

                $rating = (int) $item->rating;
                $maxRating = (int) $item->max_rating;
                $isMatch = ($maxRating > 0 && $rating >= $maxRating) || ($rating > 0 && $maxRating == 0);

                if ($isMatch) {
                    $matchedMainCount++;
                } else {
                    $allMatched = false;
                }

                $details[] = [
                    'dir_demand_id' => $dirId,
                    'title_uz' => $item->title_uz,
                    'rating' => $rating,
                    'max_rating' => $maxRating,
                    'matched' => $isMatch,
                    'comment_uz' => $item->comment_uz,
                ];
            }

            return [
                'has_ai_analysis' => true,
                'has_main_demands' => true,
                'matches_all_main' => $allMatched,
                'ai_main_matched' => $allMatched,
                'total_main' => $totalMainCount,
                'matched_main' => $matchedMainCount,
                'details' => $details,
            ];

        } catch (\Throwable $e) {
            Log::warning("checkClickAiMainDemandsMatch error: " . $e->getMessage());
            return [
                'has_ai_analysis' => false,
                'has_main_demands' => false,
                'matches_all_main' => false,
                'ai_main_matched' => false,
                'total_main' => 0,
                'matched_main' => 0,
                'details' => [],
            ];
        }
    }

    /**
     * F.I.Sh ni harflar registri va bo'sh joylardan tozalab solishtirish uchun normallashtirish
     */
    private function normalizeFio(?string $fio): string
    {
        if (!$fio) {
            return '';
        }
        $str = mb_strtolower(trim($fio));
        // O'zbekcha o' / g' belgilarini yagona formatga keltirish
        $str = str_replace(["‘", "’", "`", "'", "ʻ", "ʼ"], "'", $str);
        return preg_replace('/\s+/', ' ', $str);
    }
}
