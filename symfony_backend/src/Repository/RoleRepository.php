<?php

namespace App\Repository;

use App\Entity\Role;
use Doctrine\Persistence\ManagerRegistry;

class RoleRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry);
    }

    /**
     * @param string $name
     * @return Role
     */
    public function createWithName(string $name): Role
    {
        $role = new Role();
        $role->setName($name);
        $this->plush($role);

        return $role;
    }

    /**
     * @return string
     */
    protected function getModel(): string
    {
        return Role::class;
    }
}
