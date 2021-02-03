<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Lexik\Bundle\JWTAuthenticationBundle\Security\User\JWTUserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements EntityInterface, JWTUserInterface
{

    /** @var bool */
    public bool $isAdmin = false;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @var int|null
     */
    public ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @var string
     */
    private string $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    public string $password;

    /**
     * @ORM\ManyToOne(targetEntity=Role::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     * @var Role
     */
    private Role $role;

    /**
     * @ORM\OneToMany(targetEntity=AccessToken::class, mappedBy="user", orphanRemoval=true)
     * @var Collection|AccessToken[]
     */
    private $accessTokens;

    /**
     * @ORM\OneToMany(targetEntity=post::class, mappedBy="user")
     */
    private ArrayCollection $post;

    public function __construct()
    {
        $this->accessTokens = new ArrayCollection();
        $this->post = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
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
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @param Role $role
     * @return $this
     */
    public function setRole(Role $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return Role
     */
    public function getRole(): Role
    {
        return $this->role;
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
    public function getUsername(): string
    {
        return $this->getEmail();
    }

    /**
     * @return $this
     */
    public function eraseCredentials(): self
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getSalt(): string
    {
        return (string)getenv('SALT');
    }

    /**
     * @param int $id
     * @return User
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param bool $isAdmin
     * @return User
     */
    public function setIsAdmin(bool $isAdmin): self
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->isAdmin;
    }

    /**
     * @return Collection|AccessToken[]
     */
    public function getAccessTokens(): Collection
    {
        return $this->accessTokens;
    }

    /**
     * @param AccessToken $accessToken
     * @return $this
     */
    public function addAccessToken(AccessToken $accessToken): self
    {
        if (!$this->accessTokens->contains($accessToken)) {
            $this->accessTokens[] = $accessToken;
            $accessToken->setUser($this);
        }

        return $this;
    }

    /**
     * @param AccessToken $accessToken
     * @return $this
     */
    public function removeAccessToken(AccessToken $accessToken): self
    {
        if ($this->accessTokens->removeElement($accessToken)) {
            if ($accessToken->getUser() === $this) {
                $accessToken->setUser(null);
            }
        }

        return $this;
    }

    /** @return array */
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

    /**
     * @return Collection|post[]
     */
    public function getPost(): Collection
    {
        return $this->post;
    }

    public function addPost(post $post): self
    {
        if (!$this->post->contains($post)) {
            $this->post[] = $post;
            $post->setUser($this);
        }

        return $this;
    }

    public function removePost(post $post): self
    {
        if ($this->post->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getUser() === $this) {
                $post->setUser(null);
            }
        }

        return $this;
    }
}

