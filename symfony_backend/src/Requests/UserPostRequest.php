<?php

namespace App\Requests;

use Carbon\Carbon;

class UserPostRequest
{
    /** @var string */
    public string $title = '';

    /** @var string */
    public string $content = '';

    public $createdAt = '';

    public $updatedAt = '';

}