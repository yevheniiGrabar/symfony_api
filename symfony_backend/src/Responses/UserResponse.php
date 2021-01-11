<?php

namespace App\Responses;

use App\Entity\User;
use App\Services\RolesManager;

class UserResponse
{
    /** @var int */
    public $id = 0;

    /** @var string */
    public $name = '';

    /** @var string */
    public $email = '';

    /** @var bool */
    public $isAdmin = false;

    /** @var RolesManager */
    private $rolesManager;

    public function __construct(RolesManager $rolesManager)
    {
        $this->rolesManager = $rolesManager;
    }

    /**
     * @param User $user
     */
    public function setUserResponse(User $user): void
    {
        $this->id = $user->getId();
        $this->name = $user->getName();
        $this->email = $user->getEmail();
        $this->isAdmin = $this->rolesManager->isAdmin($user);
    }
}
