<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    /**
     * Export users into .xls
     */
    public function export()
    {
        return Excel::download(new UsersExport(), 'users.xls');
    }
}
