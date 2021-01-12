<?php

namespace App\Requests;

use App\Entity\Role;

class UserRequest
{
    /** @var string */
    public $name = '';

    /** @var string */
    public $email = '';

    /** @var string */
    public $password = '';

    /** @var Role|null */
    public $role = null;
}

