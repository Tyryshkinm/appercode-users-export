<?php

namespace App\Http\Controllers;

use App\Exports\UsersExport;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Maatwebsite\Excel\Facades\Excel;
use GuzzleHttp\Client;

class UserController extends Controller
{
    /**
     * Authentication.
     *
     * @return null
     */
    public function auth()
    {
        // get appercode session token
        $client = new Client();
        $url = 'http://proxy.test.appercode.com/v1/webdev_test/login';
        $response = $client->request("POST", $url, [
            'json' => [
                'username' => 'admin',
                'password' => 111
            ]
        ]);
        $sessionId = json_decode((string)$response->getBody())->sessionId;

        // store appercode session token to .env
        $path = base_path('.env');

        if (file_exists($path)) {
            file_put_contents($path, str_replace(
                'X_APPERCODE_SESSION_TOKEN=' . Config::get('app.appercode_session_token'),
                'X_APPERCODE_SESSION_TOKEN=' . $sessionId, file_get_contents($path)
            ));
        }

        // update config cache
        Artisan::call('config:cache');

        return null;
    }

    /**
     * Get a list of users and their profiles.
     *
     * @return array $users
     */
    public function get()
    {
        $sessionId = Config::get('app.appercode_session_token');
        if ($sessionId === null) {
            $this->auth();
        }

        $client = new Client();

        // get users
        $url = 'http://proxy.test.appercode.com/v1/webdev_test/users';
        $response = $client->request("GET", $url, [
            'headers' => [
                'Accept'                    => 'application/json',
                'X-Appercode-Session-Token' => $sessionId
            ]
        ]);
        if ($response->getStatusCode() == 401) {
            $this->auth();
        } elseif ($response->getStatusCode() == 200) {
            $users = json_decode((string)$response->getBody());
        } else {
            die('Server error.');
        }

        // get user profiles
        $url = 'http://proxy.test.appercode.com/v1/webdev_test/objects/UserProfiles';
        $response = $client->request("GET", $url, [
            'headers' => [
                'X-Appercode-Session-Token' => $sessionId
            ]
        ]);
        if ($response->getStatusCode() == 401) {
            $this->auth();
        } elseif ($response->getStatusCode() == 200) {
            $profiles = json_decode((string)$response->getBody());
        } else {
            die('Server error.');
        }

        // collect an array of users and their profiles
        if (isset($users)) {
            foreach ($users as $key => $user) {
                $users[$key] = [
                    'id'       => $user->id,
                    'username' => $user->username,
                    'role'     => $user->roleId
                ];
                if (isset($profiles)) {
                    foreach ($profiles as $profile) {
                        if ($profile->userId == $user->id) {
                            $users[$key] = array_merge($users[$key], [
                                'firstname' => $profile->firstName,
                                'lastname'  => $profile->lastName,
                                'position'  => $profile->position
                            ]);
                        }
                    }
                }

            }
            return $users;
        }
        return null;
    }

    /**
     * Export users into .xls file.
     */
    public function export()
    {
        $users = $this->get();
        return Excel::download(new UsersExport($users), 'users.xls');
    }
}
