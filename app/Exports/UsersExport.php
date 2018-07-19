<?php

namespace App\Exports;

use GuzzleHttp\Client;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /**
     * Authentication
     *
     * @return string $sessionId
     */
    public function auth()
    {
        $client = new Client();
        $url = 'http://proxy.test.appercode.com/v1/webdev_test/login';
        $response = $client->request("POST", $url, [
            'json' => [
                'username' => 'admin',
                'password' => 111
            ]
        ]);
        $sessionId = json_decode((string)$response->getBody())->sessionId;
        return $sessionId;
    }

    /**
     * Get users and them profiles if it exists
     *
     * @return array $users
     */
    public function getUsers()
    {
        $sessionId = $this->auth();
        $client = new Client();

        // get users
        $url = 'http://proxy.test.appercode.com/v1/webdev_test/users';
        $response = $client->request("GET", $url, [
            'headers' => [
                'Accept'                    => 'application/json',
                'X-Appercode-Session-Token' => $sessionId
            ]
        ]);
        $users = json_decode((string)$response->getBody());

        // get profiles
        $url = 'http://proxy.test.appercode.com/v1/webdev_test/objects/UserProfiles';
        $response = $client->request("GET", $url, [
            'headers' => [
                'X-Appercode-Session-Token' => $sessionId
            ]
        ]);
        $profiles = json_decode((string)$response->getBody());

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
                        'lastname' => $profile->lastName,
                        'position' => $profile->position
                    ]);
                }
            }
        }
        return $users;

    }

    public function collection()
    {
        $users = $this->getUsers();
        return collect($users);
    }

    public function headings(): array
    {
        return [
            'id',
            'username',
            'role',
            'firstname',
            'lastname',
            'position'
        ];
    }


}