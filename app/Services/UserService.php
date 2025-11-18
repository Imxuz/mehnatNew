<?php
namespace App\Services;
use App\Models\Demand;
use App\Models\DirDemand;
use App\Models\DocUser;
use Illuminate\Support\Str;

class UserService
{
    public function storeUserFiles($user, $request)
    {

        $uniqid =$user->unique_id==null? Str::uuid():$user->unique_id;
        $count=0;
        if (!$request->id) {
            $count = DocUser::where('user_id', $user->id)
                ->where('dir_demand_id', $request->dir_demand_id)
                ->count();
        }
        if ($count>5){
            return response()->json([
                'errors'=>"Sizning bitta maydonda yuklagan documentlariz soni 5 tadan yuqori"
            ],403);
        }
            if ($request->hasFile('file') && $request->file('file')->isValid()) {
                $file = $request->file('file');
                $content = file_get_contents($file->getRealPath());
                if (preg_match('/<script\b[^>]*>(.*?)<\/script>/is', $content)) {
                    throw new \Exception("File $file contains unsafe content.");
                }
                $filename = $uniqid .'_'.$count.'.' . $file->getClientOriginalExtension();
                $path = $file->storeAs(
                    'uploads/user_' . $user->id . '/'.'dir_demand_'.$request->dir_demand_id,
                    $filename,
                    'private'
                );
                DocUser::updateOrCreate(
                    [
                        'id' => $request->id,
                    ],
                    [
                        'user_id' => $user->id,
                        'dir_demand_id' => $request->dir_demand_id,
                        'path' => $path,
                    ]
                );
            }



        $user['unique_id'] = $uniqid;
        $user->save();

        return $user;
    }

}
