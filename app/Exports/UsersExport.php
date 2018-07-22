<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    private $users;

    public function __construct($users)
    {
        $this->users = $users;
    }

    /**
     * Create an instance of the collection from an array of users.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect($this->users);
    }

    /**
     * Set column headers in the table.
     *
     * @return array
     */
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