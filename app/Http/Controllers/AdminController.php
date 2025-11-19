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

    public function delayWorker(Request $request)
    {
        $import = new TimeImport;
        Excel::import($import, $request->file('excel_file'));

        $startDate = strtotime($request->start . " 00:00:00");
        $endDate   = strtotime($request->end   . " 23:59:59");

        // Dynamic vaqtlar
        $ms = $request->morning_start;  // 08:10
        $me = $request->morning_end;    // 09:00

        $es = $request->evening_start;  // 16:00
        $ee = $request->evening_end;    // 17:00

        // Dynamic statuslar
        $dayStatus   = $request->day_status;     // masalan: "Вход"
        $nightStatus = $request->night_status;   // masalan: "Выход"

        $filtered = collect($import->items)->filter(function ($row)
        use ($startDate, $endDate, $dayStatus, $nightStatus, $ms, $me, $es, $ee) {

            // 1. vaqtni timestampga aylantiramiz
            $time = strtotime($row['time']);
            if (!$time) return false;

            // 2. Sana filtri
            if ($time < $startDate || $time > $endDate) {
                return false;
            }

            // 3. Status bo‘yicha tekshirish
            $status = $row['status'];

            // 4. Faqat vaqt (HH:MM)
            $clock = date('H:i', $time);

            // Kunduzgi moslik: status + vaqt diapazoni
            $isDay = ($status === $dayStatus) &&
                ($clock >= $ms && $clock <= $me);

            // Kechki moslik: status + vaqt diapazoni
            $isNight = ($status === $nightStatus) &&
                ($clock >= $es && $clock <= $ee);

            return $isDay || $isNight;
        });

        return response()->json($filtered);

        // Agar excel yuklab berish kerak bo‘lsa:
        // return Excel::download(new TimeExport($filtered), 'filtered.xlsx');
    }

}
