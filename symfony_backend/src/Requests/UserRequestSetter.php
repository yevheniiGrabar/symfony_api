<?php

namespace App\Requests;

use App\Services\RolesManager;
use App\Services\JsonRequestDataKeeper;
use Symfony\Component\HttpFoundation\Request;

class UserRequestSetter
{
    /** @var RolesManager */
    private $rolesManager;

    /** @var UserRequest */
    private $userRequest;

    public function __construct(RolesManager $rolesManager, UserRequest $userRequest)
    {
        $this->userRequest = $userRequest;
        $this->rolesManager = $rolesManager;
    }

    /**
     * @param Request $request
     */
    public function setUserRequest(Request $request): void
    {
        $roleId = (int)$request->get('role_id', 0);
        $role = $this->rolesManager->findOrDefault($roleId);
        $this->userRequest->name = (string)$request->get('name', '');
        $this->userRequest->email = (string)$request->get('email', '');
        $this->userRequest->password = (string)$request->get('password', '');
        $this->userRequest->role = $role;
    }
}

