<?php

namespace Tests\Unit\Warehouse\Mocks\Picqer\BatchPicklists;

use Illuminate\Contracts\Support\Arrayable;

class PicqerAssignedTo implements Arrayable
{
    protected int $idUser;
    protected string $fullName;
    protected string $username;

    /**
     * @param int $idUser
     * @param string $fullName
     * @param string $username
     */
    public function __construct(int $idUser, string $fullName, string $username)
    {
        $this->idUser = $idUser;
        $this->fullName = $fullName;
        $this->username = $username;
    }

    public function toArray(): array
    {
        return [
            'iduser' => $this->idUser,
            'full_name' => $this->fullName,
            'username' => $this->username
        ];
    }
}
