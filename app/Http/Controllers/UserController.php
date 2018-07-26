<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use App\User;
use Illuminate\Support\Facades\Config;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    /**
     * Export users into .xls file.
     */
    public function export()
    {
        $user = new User();
        $sessionId = Config::get('app.appercode_session_token');
        if (empty($sessionId)) {
            $user->auth();
        }
        $users = $user->get();
        return Excel::download(new UsersExport($users), 'users.xls');
    }
}
