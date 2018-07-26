<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class User
{
    /**
     * Authentication.
     *
     * @return null
     */
    public function auth()
    {
        // get appercode session token
        $username = 'Admin';
        $password = 111;
        $url = 'http://proxy.test.appercode.com/v1/webdev_test/login';
        $response = $this->request($url, "POST", null, $username, $password);
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
     * Send http-request via guzzle.
     *
     * @param $url
     * @param $method
     * @param null $sessionId
     * @param null $username
     * @param null $password
     * @return mixed|null|\Psr\Http\Message\ResponseInterface
     */
    public function request($url, $method, $sessionId = null, $username = null, $password = null)
    {
        $client = new Client();
        if ($username !== null and $password !== null) {
            try {
                $response = $client->request($method, $url, [
                    'json' => [
                        'username' => $username,
                        'password' => $password
                    ]
                ]);
            } catch (RequestException $e) {
                echo Psr7\str($e->getRequest());
                if ($e->hasResponse()) {
                    echo Psr7\str($e->getResponse());
                }
            }
        }
        if ($sessionId !== null) {
            try {
                $response = $client->request($method, $url, [
                    'headers' => [
                        'Accept'                    => 'application/json',
                        'X-Appercode-Session-Token' => $sessionId
                    ]
                ]);
            } catch (RequestException $e) {
                // if session token expired
                if ($e->getResponse()->getStatusCode() == 401) {
                    $this->auth();
                    die(header('Location: /users/export'));
                }
            }
        }
        if (isset($response)) {
            return $response;
        } else {
            return null;
        }
    }

    /**
     * Get a list of users and their profiles.
     *
     * @return array $users
     */
    public function get()
    {
        $sessionId = Config::get('app.appercode_session_token');

        //get users
        $url = 'http://proxy.test.appercode.com/v1/webdev_test/users';
        $response = $this->request($url, "GET", $sessionId);
        $users = json_decode((string)$response->getBody());

        // get user profiles
        $url = 'http://proxy.test.appercode.com/v1/webdev_test/objects/UserProfiles';
        $response = $this->request($url, "GET", $sessionId);
        $profiles = json_decode((string)$response->getBody());

        // collect an array of users and their profiles
        foreach ($users as $key => $user) {
            $users[$key] = [
                'id'       => $user->id,
                'username' => $user->username,
                'role'     => $user->roleId
            ];
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
        return $users;
    }
}
