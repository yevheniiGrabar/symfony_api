<?php

namespace App\Requests;

use App\Services\JsonRequestDataKeeper;
use App\Services\RolesManager;
use Symfony\Component\HttpFoundation\Request;

class UserRequestSetter
{
    /** @var RolesManager */
    private $rolesManager;

    /** @var UserRequest */
    private $userRequest;

    public function __construct(RolesManager $rolesManager, UserRequest $userRequest)
    {
        $this->rolesManager = $rolesManager;
        $this->userRequest = $userRequest;
    }

    /**
     * @param Request $request
     * @todo: move this method into separate service
     */
    public function setUserRequest(Request $request): void
    {
        $request = JsonRequestDataKeeper::keepJson($request);
        $roleId = (int)$request->get('role_id', 0);
        $role = $this->rolesManager->findOrDefault($roleId);
        $this->userRequest->name = (string)$request->get('name', '');
        $this->userRequest->email = (string)$request->get('email', '');
        $this->userRequest->password = (string)$request->get('password', '');
        $this->userRequest->role = $role;
    }
}

