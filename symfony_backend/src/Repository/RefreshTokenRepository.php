<?php

namespace App\Repository;

use App\Entity\RefreshToken;
use Doctrine\Persistence\ManagerRegistry;

class RefreshTokenRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry);
    }

    protected function getModel(): string
    {
        return RefreshToken::class;
    }

    /**
     * @param string $oldEmail
     * @param string $newEmail
     */
    public function updateTokenEmail(string $oldEmail, string $newEmail): void
    {
        $this->createQueryBuilder('token')
            ->update()
            ->set('token.username', ':newEmail')
            ->where('token.username = :oldEmail')
            ->setParameter('newEmail', $newEmail)
            ->setParameter('oldEmail', $oldEmail)
            ->getQuery()
            ->execute();
    }

    /**
     * @param string $email
     */
    public function removeAllByEmail(string $email): void
    {
        $this->createQueryBuilder('token')
            ->delete()
            ->where('token.username = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->execute();
    }
}
