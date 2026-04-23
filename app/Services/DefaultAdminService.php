<?php
namespace App\Services;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DefaultAdminService
{

    private function admin(){
        $admin = auth('apiAdmin')->user();
        if (!$admin) {
            abort(401, 'Unauthorized');
        }
        return $admin;
    }
    public function checkAuth()
    {
        $this->admin();
    }
    public function errAllVacancyView(){
        $admin = $this->admin();
        if ($admin->hasPermission('view-admin-panel')) {
            abort(403, "Sizda bunday huquq yo'q");
        }
    }

}
