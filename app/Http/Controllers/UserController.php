<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\DirDemand;
use App\Models\DocUser;
use App\Models\User;
use App\Http\Requests\UpdateUserRequest;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
        $userId = auth('api')->id();

        $dir_docs_demands = DirDemand::with('adder_demands')
            ->leftJoin('doc_users as u', function ($join) use ($userId) {
                $join->on('u.dir_demand_id', '=', 'dir_demands.id')
                    ->where('u.user_id', '=', $userId);
            })
            ->select(
                'dir_demands.*',
                'u.id as doc_id',
                'u.check',
                'u.path',
                'u.adder_demands_id'
            )
            ->orderBy('dir_demands.sort_number', 'asc')
            ->get();
        return response()->json($dir_docs_demands);
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
        $admin = auth('apiAdmin')->user();
        $filepathUser = '';
        if ($user && $user->id){
            $doc = DocUser::where('user_id', $user->id)
                ->where('path', $filepath)
                ->firstOrFail();
            $filepathUser=$doc->path;
        }elseif ($admin &&$admin->id){
            $filepathUser = $filepath;
        }


        $file = storage_path('app/private/' . $filepathUser);

        // Cache control headers qo'shish
        return response()->file($file, [
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
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


    public function getPassportData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'passport_number' => 'required|string|max:7',
            'passport_series' => 'required|string|max:2',
            'birthday' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        $passport_number = $request->input('passport_number');
        $passport_series = strtoupper($request->input('passport_series'));
        $birthday = $request->input('birthday');
        $user = auth('api')->user();
// Paroldagi * belgisini %2A ga almashtirdik
        $response = Http::timeout(20)
            ->withOptions([
                'proxy' => env('PROXY_URL'),
                'curl'  => [
                    // NTLM va boshqa turdagi autentifikatsiyalarni avtomatik aniqlash
                    CURLOPT_PROXYUSERPWD => env('PROXY_USER') . ":" . env('PROXY_PASS'),
                    CURLOPT_PROXYAUTH    => CURLAUTH_ANY,
                ],
                'verify' => false,
            ])
            ->post('https://api-test.gross.uz/api/v1/gross-provider/get-data', [
                'is_ersp' => false,
                'method' => 'pass-data-birthday',
                'payload' => [
                    'pass_number' => $passport_number,
                    'pass_sery'   => $passport_series,
                    'birthday'    => $birthday,
                ],
            ]);

        if ($response->successful()) {
            $responseBody = $response->json();
            $userData = $responseBody['data'] ?? [];
            $user->update([
                'passport_series' => $passport_series,
                'passport_number' => $passport_number,
                'birthday'        => $birthday,
                'pinfl'           => data_get($userData, 'driver.pinfl'),
                'address'         => data_get($userData, 'address'),
                'name'            => trim(
                    data_get($userData, 'last_name').' '.
                    data_get($userData, 'first_name').' '.
                    data_get($userData, 'middle_name')
                ),
            ]);

            return response()->json([
                'success' => true,
                'data'    => $response->json(),
            ]);
        }

        return response()->json([
            'success' => false,
            'status'  => $response->status(),
            'message' => $response->json() ?: 'API error',
        ], $response->status() ?: 422);
    }








}
