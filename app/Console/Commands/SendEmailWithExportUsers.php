<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;

class SendEmailWithExportUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send-exported-users {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send an email with exported users file (xls)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Excel::store(new UsersExport(), 'users.xls');
    }
}
