<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Http\Requests\StoreRegionRequest;
use App\Http\Requests\UpdateRegionRequest;

class RegionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Region::select('id', 'name', 'title', 'sub_region_id')
            ->whereNull('sub_region_id')
            ->with('children');
        return response()->json($query->get());
//        if (auth('apiAdmin')->check()) {
//            $admin = auth('apiAdmin')->user();
//            if ($admin->hasPermission('region-vacancy')) {
//                $query->where('id', $admin->region_id);
//
//            }
//            if ($admin->hasPermission('sub-region-vacancy')) {
////                $query->WhereHas('children', function ($c) use ($admin) {
////                    $c->where('id', $admin->region_id);
////                })->with(['children' => function ($c) use ($admin) {
////                    $c->where('id', $admin->region_id); }]);
//                $query->where(function ($q) use ($admin) {
//                    $q->where('id', $admin->region_id)
//                        ->orWhereHas('children', function ($c) use ($admin) {
//                            $c->where('id', $admin->region_id);
//                        });
//                });
//            }
//        }
//
//        return response()->json($query->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRegionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Region $region)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRegionRequest $request, Region $region)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Region $region)
    {
        //
    }


    public function adminDivision()
    {
        $query = Region::select('id', 'name', 'title', 'division', 'div_region')
            ->whereNotNull('division')
            ->whereNull('div_region')
            ->with(['divChildren' => function($q) {
                $q->select('id', 'name', 'title', 'division', 'div_region');
            }]);
        if (auth('apiAdmin')->check()) {
            $admin = auth('apiAdmin')->user();

            if ($admin->hasPermission('region-vacancy')) {
                $query->where(function ($q) use ($admin) {
                    $q->where('id', $admin->region_id)
                        ->orWhereHas('divChildren', function ($c) use ($admin) {
                            $c->where('id', $admin->region_id);
                        });
                });
            }
            if ($admin->hasPermission('sub-region-vacancy')) {
                $query->where(function ($q) use ($admin) {
                    $q->where('id', $admin->region_id)
                        ->orWhereHas('divChildren', function ($c) use ($admin) {
                            $c->where('id', $admin->region_id);
                        });
                });
            }
        }
        $regions = $query->get()->map(function ($region) {
            $parentData = [
                'id' => $region->id,
                'name' => $region->name,
                'title' => $region->title,
                'division' => $region->division,
                'div_region' => $region->div_region,
                'is_main' => true
            ];
            $childrenArray = $region->divChildren ? $region->divChildren->toArray() : [];
            array_unshift($childrenArray, $parentData);
            return [
                'id' => $region->id,
                'name' => $region->name,
                'title' => $region->title,
                'division' => $region->division,
                'div_region' => $region->div_region,
                'children' => $childrenArray // Front-end uchun kalit so'zni 'children' qoldiraverdim
            ];
        });

        return response()->json($regions);
    }
}
