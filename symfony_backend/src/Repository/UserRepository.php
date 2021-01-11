<?php

namespace App\Repository;

use App\Entity\User;
use App\Requests\UserRequest;
use App\Responses\UserResponse;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserRepository extends AbstractRepository
{
    /** @var UserRequest */
    private $userRequest;

    /** @var UserResponse */
    private $userResponse;

    public function __construct(ManagerRegistry $registry, UserRequest $userRequest, UserResponse $userResponse)
    {
        parent::__construct($registry);
        $this->userRequest = $userRequest;
        $this->userResponse = $userResponse;
    }

    /**
     * @return string
     */
    protected function getModel(): string
    {
        return User::class;
    }

    /**
     * @param User $user
     * @param UserPasswordEncoderInterface $encoder
     */
    public function persistUser(User $user,  UserPasswordEncoderInterface $encoder): void
    {
        $user->setName($this->userRequest->name);
        $user->setEmail($this->userRequest->email);
        $user->setPassword($encoder->encodePassword($user, $this->userRequest->password));
        $user->setRole($this->userRequest->role);
        $this->plush($user);
        $this->userResponse->setUserResponse($user);
    }
}

