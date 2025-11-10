<?php
namespace App\Services;
use App\Models\DirDemand;
use Illuminate\Support\Str;

class UserService
{
    public function storeUserFiles($user, $request)
    {
        $uniqid = Str::uuid();
        $fileFields = [
            'passport',
            'driverLicence',
            'loadDriverLicence',
            'education',
            'workbook',
            'certificate',
            'militaryCertificate'
        ];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field) && $request->file($field)->isValid()) {

                $file = $request->file($field);


                $content = file_get_contents($file->getRealPath());

                if (preg_match('/<script\b[^>]*>(.*?)<\/script>/is', $content)) {
                    throw new \Exception("File $field contains unsafe content.");
                }
                $filename = $field . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs(
                    'uploads/' . $user->id . '/' . $uniqid,
                    $filename,
                    'public'
                );
                $dirDemand = DirDemand::where('name', $field)->first();
            }
        }


        $user['unique_id'] = $uniqid;
        $user->save();

        return $user;
    }

}
