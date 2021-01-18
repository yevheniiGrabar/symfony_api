<?php

namespace App\Services;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class RolesManager
{
    private const ADMIN_ROLE = 'admin';
    private const USER_ROLE = 'user';
    private const AVAILABLE_ROLES = [
        self::ADMIN_ROLE,
        self::USER_ROLE
    ];

    /** @var RoleRepository */
    private RoleRepository $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * @param User $user
     * @return bool
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function isAdmin(User $user): bool
    {
        $adminRole = $this->getAdminRole();
        $userRole = $user->getRole();

        return $adminRole->getId() === $userRole->getId() && $adminRole->getName() === $userRole->getName();
    }

    /**
     * @param int $roleId
     * @return Role
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function findOrDefault(int $roleId): Role
    {
        $role = $this->roleRepository->find($roleId);

        if (!$role) {
            $role = $this->getDefaultRole();
        }

        return $role;
    }

    /**
     * @return Role
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function getDefaultRole(): Role
    {
        return $this->getByNameOrCreate(self::USER_ROLE);
    }

    /**
     * @return Role
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function getAdminRole(): Role
    {
        return $this->getByNameOrCreate(self::ADMIN_ROLE);
    }


    /**
     * @param string $name
     * @return Role
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function getByNameOrCreate(string $name): Role
    {
        if (!in_array($name, self::AVAILABLE_ROLES)) {
            $name = self::USER_ROLE;
        }

        $role = $this->roleRepository->findOneBy(['name' => $name]);

        if (!$role) {
            $role = $this->roleRepository->createWithName($name);
        }
        return $role;
    }
}
