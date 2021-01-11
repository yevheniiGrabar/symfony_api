<?php

namespace App\Entity;

use App\Repository\AccessTokenRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AccessTokenRepository::class)
 */
class AccessToken implements EntityInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var int|null
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    private $token;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="accessToken")
     *@ORM\JoinColumn(nullable=false)
     * @var User
     */
    private $user;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function getUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }
}