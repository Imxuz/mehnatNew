<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\DocUser;
use App\Models\User;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct(private UserService $userService){}
    public function index()
    {
        $user = auth('api')->user();
        $userDocs = DB::table('dir_demands as d')
            ->leftJoin('doc_users as u', function ($join) use ($user) {
                $join->on('u.dir_demand_id', '=', 'd.id')
                    ->where('u.user_id', '=', $user->id);
            })
            ->select('u.id as id','u.check','d.id as dir_demand_id', 'd.name', 'u.path', 'd.title','d.sort_number')
            ->orderBy('d.sort_number','asc')
            ->get();
        return response()->json($userDocs);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $result = $this->userService->storeUserFiles($user, $request);
        return response()->json([
            'message' => 'User files uploaded successfully',
            'data' => $result
        ]);


    }

    /**
     * Display the specified resource.
     */
    public function show($filepath)
    {
        $user = auth('api')->user();
        $doc = DocUser::where('user_id', $user->id)
            ->where('path', $filepath)
            ->firstOrFail();

        return response()->file(storage_path('app/private/' . $doc->path));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }





}
