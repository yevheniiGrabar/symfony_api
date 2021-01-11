<?php

namespace App\Models;

use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;

class User implements JWTUserInterface
{
    /** @var int */
    private $id = 0;

    /** @var string */
    private $email = '';

    /** @var string */
    private $password = '';

    /** @var bool */
    private $isAdmin = false;

    /** @var string */
    private $name = '';

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string
     * @return $this
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function getSalt(): string
    {
        return (string)getenv('SALT');
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->email;
    }

    /**
     * @return User
     */
    public function eraseCredentials(): self
    {
        return $this;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param bool $isAdmin
     * @return $this
     */
    public function setIsAdmin(bool $isAdmin): self
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'email' => $this->getUsername(),
            'isAdmin' => $this->isAdmin()
        ];
    }

    /**
     * @param string $username
     * @param array $payload
     * @return JWTUserInterface
     * @noinspection PhpMissingParamTypeInspection
     */
    public static function createFromPayload($username, array $payload): JWTUserInterface
    {
        $user = new User();
        $user->setEmail($username);

        if (array_key_exists('id', $payload)) {
            $user->setId($payload['id']);
        }

        if (array_key_exists('name', $payload)) {
            $user->setName($payload['name']);
        }

        if (array_key_exists('isAdmin', $payload)) {
            $user->setIsAdmin($payload['isAdmin']);
        }

        return $user;
    }
}
