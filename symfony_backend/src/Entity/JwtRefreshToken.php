<?php

namespace App\Entity;

use App\Repository\JwtRefreshTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Gesdinet\JWTRefreshTokenBundle\Entity\AbstractRefreshToken;

/**
 * @ORM\Entity(repositoryClass=JwtRefreshTokenRepository::class)
 * @ORM\Table("jwt_refresh_token")
 */
class JwtRefreshToken extends AbstractRefreshToken implements EntityInterface
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected int $id;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}

