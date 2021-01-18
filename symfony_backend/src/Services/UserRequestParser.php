<?php

namespace App\Services;

use App\Repository\RoleRepository;
use App\Requests\UserRequest;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;


class UserRequestParser
{
    /** @var RolesManager */
    private RolesManager $rolesManager;

    /** @var RoleRepository */
    private RoleRepository $roleRepository;

    public function __construct(RolesManager $rolesManager, RoleRepository $roleRepository)
    {
        $this->rolesManager = $rolesManager;
        $this->roleRepository = $roleRepository;
    }

    /**
     * @param Request $request
     * @param bool $withRole
     * @return UserRequest
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function parseRequest(Request $request, bool $withRole = false): UserRequest
    {
        $request = JsonRequestDataKeeper::keepJson($request);

        $parsedRequest = new UserRequest();
        $parsedRequest->name = (string)$request->get('name', '');
        $parsedRequest->email = (string)$request->get('email', '');
        $parsedRequest->password = (string)$request->get('password', '');

        if (!$withRole) {
            $parsedRequest->role = $this->rolesManager->getDefaultRole();

            return $parsedRequest;
        }

        $roleId = (int)$request->get('role_id', 0);
        $parsedRequest->role = $this->roleRepository->find($roleId);

        return $parsedRequest;
    }
}
