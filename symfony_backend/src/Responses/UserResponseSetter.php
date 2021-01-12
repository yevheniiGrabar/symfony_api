<?php

namespace App\Responses;

use App\Entity\User;
use App\Services\RolesManager;

class UserResponseSetter
{

    /** @var RolesManager */
    private $rolesManager;

    /** @var UserResponse */
    public $userResponse;

    public function __construct(RolesManager $rolesManager, UserResponse $userResponse)
    {
        $this->rolesManager = $rolesManager;
        $this->userResponse = $userResponse;
    }

    /**
     * @param User $user
     */
    public function setUserResponse(User $user)
    {
        $this->userResponse->id = $user->getId();
        $this->userResponse->name = $user->getName();
        $this->userResponse->email = $user->getEmail();
        $this->userResponse->isAdmin = $this->rolesManager->isAdmin($user);
    }
}

