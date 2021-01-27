<?php

namespace App\Responses;

class UserResponse
{
    /** @var int */
    public int $id = 0;

    /** @var string */
    public string $name = '';

    /** @var string */
    public string $email = '';

    /** @var bool */
    public bool $isAdmin = false;
}

