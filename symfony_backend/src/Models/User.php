<?php

namespace App\Models;

class User
{
    /** @var int */
    public $id = 0;

    /** @var string */
    public $name = '';

    /** @var string */
    public $email = '';

    /** @var bool */
    public $isAdmin = false;

    /**
     * @param array $data
     * @return User
     */
    public static function createFromArray(array $data): User
    {
        $user = new User();

        if (array_key_exists('id', $data)) {
            $user->id = (int)$data['id'];
        }

        if (array_key_exists('name', $data)) {
            $user->name = (string)$data['name'];
        }

        if (array_key_exists('email', $data)) {
            $user->email = (string)$data['email'];
        }

        if (array_key_exists('isAdmin', $data)) {
            $user->isAdmin = (bool)$data['isAdmin'];
        }

        return $user;
    }
}