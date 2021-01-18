<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends AbstractRepository
{
    /** @param ManagerRegistry $registry */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry);
    }

    /** @return string */
    protected function getModel(): string
    {
        return User::class;
    }
}

