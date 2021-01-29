<?php

namespace App\Requests;

use App\Entity\Role;

class UserRequest
{
    /** @var string */
    public string $name = '';

    /** @var string */
    public string $email = '';

    /** @var string */
    public string $password = '';

    /** @var Role|null */
    public ?Role $role = null;
}

