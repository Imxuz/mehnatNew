<?php
namespace App\Services;
use App\Models\Click;
use App\Models\Demand;
use App\Models\DirDemand;
use App\Models\DocUser;
use App\Models\DocUserHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserService
{
    protected string $hrUrl;

    public function __construct()
    {
        $this->hrUrl = env('HR_MEHNAT_API');
    }

    public function storeUserFiles($user, $request)
    {
        $count=0;
        if (!$request->id) {
            $count = DocUser::where('user_id', $user->id)
                ->where('dir_demand_id', $request->dir_demand_id)
                ->count();
        }else{
        $pathRemover = DocUser::where('id', $request->id)
            ->where('user_id', $user->id)->firstOrFail();
        if ($pathRemover&&$pathRemover->path&&!$pathRemover->vacancy_doc_id){
            Storage::disk('private')->delete($pathRemover->path);

        }
    }
        if ($count>5){
            return response()->json([
                'errors'=>"Sizning bitta maydonda yuklagan documentlariz soni 5 tadan yuqori"
            ],403);
        }

        if (isset($pathRemover)){
            $pathRemover->update([
                'vacancy_doc_id' => null,
                'path' => null,
            ]);
        }


            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $file = $request->file('file');
                $content = file_get_contents($file->getRealPath());
                if (preg_match('/<script\b[^>]*>(.*?)<\/script>/is', $content)) {
                    throw new \Exception("File $file contains unsafe content.");
                }

                $filename = Str::uuid().'.' . $file->getClientOriginalExtension();
                $path = $file->storeAs(
                    'uploads/user_' . $user->id . '/'.'dir_demand_'.$request->dir_demand_id,
                    $filename,
                    'private'
                );


                $doc_id = DocUser::updateOrCreate(
                    [
                        'id' => $request->id,
                    ],
                    [
                        'user_id' => $user->id,
                        'dir_demand_id' => $request->dir_demand_id,
                        'path' => $path,
                        'check'=>null,
                        'adder_demands_id'=>null,
                        'ip_address'=>request()->ip(),
                    ]
                )->id;



                $url = $this->hrUrl.'/mehnat/doc-user/'.$doc_id;
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_exec($ch);
                curl_close($ch);
            }else {
                if ($request->adder_demands_id){
                DocUser::updateOrCreate(
                    [
                        'id' => $request->id,
                    ],
                    [
                        'user_id' => $user->id,

                        'dir_demand_id' => $request->dir_demand_id,
                        'adder_demands_id'=>$request->adder_demands_id,
                        'path'=>null,
                        'ip_address'=>request()->ip(),
                    ]
                );
                }
            }

        $user->save();

        return $user;
    }

}
