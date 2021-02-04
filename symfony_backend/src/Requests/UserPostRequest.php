<?php

namespace App\Requests;

class UserPostRequest
{
    /** @var string */
    public string $title = '';

    /** @var string */
    public string $content = '';

    public $created_at = '';

    public $updated_at = '';
}