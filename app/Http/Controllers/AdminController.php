<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\TimeImport;
use App\Exports\TimeExport;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = User::all();
        dd($user);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAdminRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Admin $admin)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAdminRequest $request, Admin $admin)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Admin $admin)
    {
        //
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => ["required", "string", "max:100", "email"],
            "password" => ["required", "string", "max:100"],
        ]);

        if ($validator->fails()) {

            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        $credentials = $request->only('email', 'password');
        if (!$token = auth('apiAdmin')->attempt($credentials)) {
            return response()->json(['message' => 'Email or password incorrect'], 401);
        }

        $user = auth('apiAdmin')->user();
        if (!$user->email) {
            return response()->json(['message' => 'User not verified'], 403);
        }
        return $this->respondWithToken($token);
    }
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in' => auth('apiAdmin')->factory()->getTTL() * 60,
            'user'         => auth('apiAdmin')->user(),
        ]);
    }

    public function delayWorker(Request $request){



        $import = new TimeImport;
        Excel::import($import, $request->file('excel_file'));

        $start = $request->start;
        $end   = $request->end;

        $filtered = collect($import->items)->filter(function ($value) use ($start, $end) {
            return strtotime($value) >= strtotime($start) &&
                strtotime($value) <= strtotime($end);
        });
        return response()->json($filtered);


        return Excel::download(new TimeExport($filtered), 'filtered.xlsx');
    }
}
