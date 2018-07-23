<?php

namespace App\Console\Commands;

use App\Http\Controllers\UserController;
use Illuminate\Console\Command;
use App\Exports\UsersExport;
use Illuminate\Support\Facades\Mail;
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
        $users = new UserController();
        $users = $users->get();
        $fileName = date('h:i:s_dmY') . '_users.xls';
        if (Excel::store(new UsersExport($users), $fileName)) {
            Mail::send('emails.export-users', $data = [], function ($message) use ($fileName) {
                $message->to($this->argument('email'));
                $message->subject('Appercode Users Export');
                $message->from('etozhemailtest@gmail.com');
                $message->attach(storage_path('app/' . $fileName));
            });
            if (count(Mail::failures()) > 0) {
                $this->info('An error occurred while sending email to:');
                foreach (Mail::failures() as $email) {
                    $this->info($email . '<br />');
                }
            } else {
                $this->info('File successfully sent to ' . $this->argument('email'));
            }
        } else {
            $this->info('An error occurred while saving the file.');
        }
    }
}
