<?php

namespace App\Http\Controllers;

use App\Models\Click;
use App\Models\DocUser;
use App\Models\ExamUsers;
use App\Models\Occupation;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Models\CheckTestProcess;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class CheckTestProcessController extends Controller
{
    public function store(Request $request){
        $admin = auth('apiAdmin')->user();
        if (!$admin) {
            abort(403, 'Sizda bu amalni bajarish huquqi yo‘q');
        }
        $validator = Validator::make($request->all(),[
            'vacancy_id' => 'required|integer|unique:check_test_processes,vacancy_id',
            'test_date' => 'required|date|after_or_equal:today',
            'duration' => 'required|integer|min:1',
            'duration_unit' => 'required|in:hours,days',
            'total_hours' => 'required|integer|min:1'
        ]);

        if ($validator->fails()){
            return response()->json([
                'status'  => 'error',
                'errors'  => $validator->errors()
            ], 422);
        }
        $validated = $validator->validated();


        $vacancy = Vacancy::where('id', $request->vacancy_id)->where('close_at', '<',$request->test_date)->first();
        if (!$vacancy) {
            return response()->json([
                'message' => 'Vakansiya hali muddati tugamangan yoki vakansiya topilmadi !'
            ], 422);
        };

        $exam_has = ExamUsers::where('vacancy_id', $vacancy->id)->exists();
        if ($exam_has) {
            return response()->json([
                'message' => 'Bunaqa test allaqachon yaratilgan!',
                'exam_has' => $exam_has,
            ], 422);
        };
        $occupation = Occupation::select('occupation')->where('id', $vacancy->occupation_id)->first();

        CheckTestProcess::create([
            'admin_id'    => $admin['id'],
            'vacancy_id'    => $validated['vacancy_id'],
            'test_date'     => $validated['test_date'],
            'duration'      => $validated['duration'],
            'duration_unit' => $validated['duration_unit'],
            'total_hours' => $validated['total_hours'],
        ]);


//        $occupationData = is_array($occupation->occupation)
//            ? $occupation->occupation
//            : json_decode($occupation->occupation, true);

//        $titles = [
//            !empty($occupationData['ru'])
//                ? $occupationData['ru'] . " (Русский)"
//                : "Язык не указан (Русский)",
//            !empty($occupationData['uz'])
//                ? $occupationData['uz'] . " (Uzbekcha)"
//                : "Til kiritilmagan (Uzbekcha)",
//            !empty($occupationData['uz'])
//                ? $this->latinToCyrillic($occupationData['uz']) . " (Кирилча)"
//                : "Тил киритилмаган (Кирилча)",
//        ];




        $vacancyId = $validated['vacancy_id'];

        $exam_users = Click::where('vacancy_id', $validated['vacancy_id'])->where('sent', 'accept')->pluck('user_id');
        $existingCodes = ExamUsers::where('vacancy_id', $vacancyId)->pluck('random_code')->toBase();

        $insertData = [];

        foreach ($exam_users as $userId) {
            do {
                $code = strtoupper(Str::random(4));
            } while ($existingCodes->contains($code));

            $existingCodes->push($code);

            $insertData[] = [
                'user_id'     => $userId,
                'vacancy_id'  => $vacancyId,
                'random_code' => $code,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }
        if (!empty($insertData)) {
            ExamUsers::upsert(
                $insertData,
                ['vacancy_id', 'user_id'],
                ['random_code', 'updated_at']
            );
        }
        $baseUrl = rtrim(env('TESTONLINE_API_URL', 'https://testonline.ngmk.uz/api'), '/');
        $testonlineUrl = "{$baseUrl}/mehnat/test/{$vacancyId}";
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->post($testonlineUrl, []);
        if ($response->successful()) {
            $data = $response->json();
        } else {
            $data = $response->json();
        }


        return response()->json([
            'success' => true,
            'message' => 'Test muvaffaqiyatli yaratildi.',
            'testonline'   => $data,
        ], 200);





//        // Calculate duration in minutes
//        $durationInMinutes = $validated['duration'];
//        if ($validated['duration_unit'] === 'hours') {
//            $durationInMinutes = $validated['duration'] * 60;
//        } elseif ($validated['duration_unit'] === 'days') {
//            $durationInMinutes = $validated['duration'] * 24 * 60;
//        }
//
//        $testDate = Carbon::parse($validated['test_date']);
//        $testonlineUrl = env('TESTONLINE_API_URL', 'https://testonline.ngmk.uz/api') . '/mehnat/import-test';
//
//        foreach ($titles as $title) {
//            $testDetails = [
//                'mehnat_test_id' => $process->id,
//                'title' => $title,
//                'duration' => $durationInMinutes,
//                'numberQuestions' => 30, // Default questions
//                'passNumber' => 15, // Default pass mark
//                'division_id' => $vacancy->region_id, // Map region_id to division
//                'admin_id' => $admin['id'],
//                'status' => 'open',
//                'from_time' => $testDate->toDateTimeString(),
//                'to_time' => $testDate->copy()->addHours($validated['total_hours'])->toDateTimeString(),
//            ];
//
//            // Build multipart request for this test
//            $pendingRequest = Http::asMultipart()->withOptions(['verify' => false]);
//
//            $usersData = [];
//            $index = 0;
//            foreach ($exam_users as $userId) {
//                $getUser = User::find($userId);
//                if (!$getUser) continue;
//
//                $passportUser = DocUser::where('user_id', $getUser->id)->where('dir_demand_id', 8)->first();
//
//                $usersData[$index] = [
//                    'mehnat_user_id' => $getUser->id,
//                    'name' => $getUser->name,
//                    'passport_series' => $getUser->passport_series,
//                    'passport_number' => $getUser->passport_number,
//                    'pinfl' => $getUser->pinfl,
//                    'phone' => $getUser->phone,
//                    'email' => $getUser->email,
//                ];
//
//                if ($passportUser && $passportUser->path) {
//                    $passportPath = storage_path('app/private/' . $passportUser->path);
//                    if (file_exists($passportPath)) {
//                        $pendingRequest->attach(
//                            "users[{$index}][doc_passport]",
//                            file_get_contents($passportPath),
//                            basename($passportPath)
//                        );
//                    }
//                }
//
//                if ($getUser->photo_user) {
//                    $photoPath = storage_path('app/private/' . $getUser->photo_user);
//                    if (file_exists($photoPath)) {
//                        $pendingRequest->attach(
//                            "users[{$index}][doc_photo_user]",
//                            file_get_contents($photoPath),
//                            basename($photoPath)
//                        );
//                    }
//                }
//
//                $index++;
            }

            // Post request to testonline
//            try {
//                $response = $pendingRequest->post($testonlineUrl, [
//                    'test_details' => $testDetails,
//                    'users' => $usersData
//                ]);
//
//                if (!$response->successful()) {
//                    \Illuminate\Support\Facades\Log::error("Testonline import failed for title '{$title}': " . $response->body());
//                }
//            } catch (\Exception $e) {
//                \Illuminate\Support\Facades\Log::error("Testonline import exception for title '{$title}': " . $e->getMessage());
//            }




//    function latinToCyrillic($text) {
//        $text = str_replace(["‘", "’", "`", "ʻ"], "'", $text);
//
//        $cyrillic = [
//            'SH' => 'Ш', 'Sh' => 'Ш', 'sh' => 'ш',
//            'CH' => 'Ч', 'Ch' => 'Ч', 'ch' => 'ч',
//            'YO' => 'Ё', 'Yo' => 'Ё', 'yo' => 'ё',
//            'YU' => 'Ю', 'Yu' => 'Ю', 'yu' => 'ю',
//            'YA' => 'Я', 'Ya' => 'Я', 'ya' => 'я',
//            'TS' => 'Ц', 'Ts' => 'Ц', 'ts' => 'ц',
//            "O'" => 'Ў', "o'" => 'ў',
//            "G'" => 'Ғ', "g'" => 'ғ',
//            "E'" => 'Э', "e'" => 'э',
//
//            'A' => 'А', 'a' => 'а',
//            'B' => 'Б', 'b' => 'б',
//            'V' => 'В', 'v' => 'в',
//            'G' => 'Г', 'g' => 'г',
//            'D' => 'Д', 'd' => 'д',
//            'E' => 'Е', 'e' => 'е',
//            'J' => 'Ж', 'j' => 'ж',
//            'Z' => 'З', 'z' => 'з',
//            'I' => 'И', 'i' => 'и',
//            'Y' => 'Й', 'y' => 'й',
//            'K' => 'К', 'k' => 'к',
//            'L' => 'Л', 'l' => 'л',
//            'M' => 'М', 'm' => 'м',
//            'N' => 'Н', 'n' => 'н',
//            'O' => 'О', 'o' => 'о',
//            'P' => 'П', 'p' => 'п',
//            'R' => 'Р', 'r' => 'р',
//            'S' => 'С', 's' => 'с',
//            'T' => 'Т', 't' => 'т',
//            'U' => 'У', 'u' => 'у',
//            'F' => 'Ф', 'f' => 'ф',
//            'X' => 'Х', 'x' => 'х',
//            'H' => 'Ҳ', 'h' => 'ҳ',
//            "'" => 'ъ'
//        ];
//
//        return str_replace(array_keys($cyrillic), array_values($cyrillic), $text);
//    }
}
