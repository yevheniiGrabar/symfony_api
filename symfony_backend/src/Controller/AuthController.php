<?php

namespace App\Controller;

use App\Entity\User;
use App\Requests\UserRequest;
use App\Repository\UserRepository;
use App\Requests\ValidationRequest;
use App\Responses\UserResponse;
use App\Responses\UserResponseSetter;
use App\Services\JsonRequestDataKeeper;
use App\Services\RolesManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;

/**
 * @Route("/api", name="auth.")
 */
class AuthController extends AbstractController
{
    /** @var UserRepository */
    private $userRepository;

    /** @var UserRequest */
    private $userRequest;

    /** @var JWTTokenManagerInterface */
    private $tokenManager;

    /** @var AuthenticationSuccessHandler */
    private $authHandler;


    /** @var ValidationRequest */
    private $validationRequest;

    /** @var RolesManager */
    private $rolesManager;

    /** @var UserResponse */
    private $userResponse;

    public function __construct
    (
        UserRepository $userRepository,
        JWTTokenManagerInterface $tokenManager,
        AuthenticationSuccessHandler $authHandler,
        ValidationRequest $validationRequest,
        UserRequest $userRequest,
        UserResponse $userResponse,
        RolesManager $rolesManager
    )
    {
        $this->userRepository = $userRepository;
        $this->tokenManager = $tokenManager;
        $this->authHandler = $authHandler;
        $this->userRequest = $userRequest;
        $this->rolesManager = $rolesManager;
        $this->userResponse = $userResponse;
        $this->validationRequest = $validationRequest;
    }

    /**
     * @Route("/register", name="register", methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return JsonResponse
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $encoder): JsonResponse
    {
        $this->setUserRequest($request);

        $violations = $this->validationRequest->validateUserRequest();

        if (count($violations) > 0) {
            return new JsonResponse(['errors' => (string)$violations], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($this->userRepository->findOneBy(['email' => $this->userRequest->email])) {
            return new JsonResponse(['errors' => 'This email is already in use'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = new User();
        $this->persistUser($user, $encoder);

        return new JsonResponse($this->userResponse);

    }

    /**
     * @Route("/login", name="login", methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return JsonResponse
     */
    public function login(Request $request, UserPasswordEncoderInterface $encoder): JsonResponse
    {
        $this->setUserRequest($request);
        $request = JsonRequestDataKeeper::keepJson($request);

        $email = (string)$request->get('email', '');
        $password = (string)$request->get('password', '');

        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        if (!$encoder->isPasswordValid($user, $password)) {
            return new JsonResponse(['errors' => 'Invalid password'], Response::HTTP_UNAUTHORIZED);
        }

        $user->setIsAdmin($this->rolesManager->isAdmin($user));
        $token = $this->tokenManager->createFromPayload($user, $user->toArray());
        $this->authHandler->handleAuthenticationSuccess($user, $token);

        return new JsonResponse(['token' => $token]);
    }

    /**
     * @Route("/current", name="current", methods={"GET"})
     * @param TokenStorageInterface $storage
     * @return JsonResponse
     */
    public function currentUserAction(TokenStorageInterface $storage)
    {
        /** @var User $user */
        $user = $storage->getToken()->getUser();

        return new JsonResponse($user->toArray());
    }

    /**
     * @param User $user
     * @param UserPasswordEncoderInterface $encoder
     */
    public function persistUser(User $user, UserPasswordEncoderInterface $encoder): void
    {
        $user->setName($this->userRequest->name);
        $user->setEmail($this->userRequest->email);
        $user->setPassword($encoder->encodePassword($user, $this->userRequest->password));
        $user->setRole($this->userRequest->role);
        $this->userRepository->plush($user);
        $this->setUserResponse($user);
    }

    /**
     * @param Request $request
     */
    private function setUserRequest(Request $request): void
    {
        $request = JsonRequestDataKeeper::keepJson($request);
        $roleId = (int)$request->get('role_id', 0);
        $role = $this->rolesManager->findOrDefault($roleId);
        $this->userRequest->name = (string)$request->get('name', '');
        $this->userRequest->email = (string)$request->get('email', '');
        $this->userRequest->password = (string)$request->get('password', '');
        $this->userRequest->role = $role;
    }

    /**
     * @param User $user
     */
    public function setUserResponse(User $user)
    {
        $this->userResponse->id = $user->getId();
        $this->userResponse->name = $user->getName();
        $this->userResponse->email = $user->getEmail();
        $this->userResponse->isAdmin = $this->rolesManager->isAdmin($user);
    }
}
