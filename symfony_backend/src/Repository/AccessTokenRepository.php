<?php

namespace App\Repository;

use App\Entity\AccessToken;
use Doctrine\Persistence\ManagerRegistry;

class AccessTokenRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry);
    }

    /**
     * @return string
     */
    protected function getModel(): string
    {
        return AccessToken::class;
    }
}