<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;

class UserController extends Controller
{
    /**
     * authentication
     * @return mixed
     */
    public function auth()
    {
        $client = new Client();
        $response = $client->request("POST", 'http://proxy.test.appercode.com/v1/webdev_test/login', [
            'json' => [
                'username' => 'admin',
                'password' => 111
            ]
        ]);
        $sessionId = json_decode((string)$response->getBody())->sessionId;
        return $sessionId;
    }

    public function export()
    {
        $sessionId = $this->auth();
        //Получаем список юзеров
        $client = new Client();
        $url = 'http://proxy.test.appercode.com/v1/webdev_test/users';
        $response = $client->request("GET", $url, [
            'headers' => [
                'Accept'     => 'application/json',
                'X-Appercode-Session-Token' => $sessionId
            ]
        ]);
        print_r((string)$response->getBody());

        //Получаем их должность, если она есть

        //Экспортируем все в эксель
    }
}
