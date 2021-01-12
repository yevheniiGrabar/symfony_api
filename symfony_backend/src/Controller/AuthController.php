<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Requests\UserRequest;
use App\Requests\UserRequestSetter;
use App\Requests\ValidationRequest;
use App\Responses\UserResponse;
use App\Responses\UserResponseSetter;
use App\Services\JsonRequestDataKeeper;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Http\Authentication\AuthenticationSuccessHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/api", name="auth.")
 */
class AuthController extends AbstractController
{
    /** @var UserRepository */
    private $userRepository;

    /** @var UserRequest */
    private $userRequest;

    /** @var UserResponse */
    private $userResponse;

    /** @var JWTTokenManagerInterface */
    private $tokenManager;

    /** @var AuthenticationSuccessHandler */
    private $authHandler;

    /** @var UserResponseSetter */
    private $userResponseSetter;

    /** @var UserRequestSetter */
    private $userRequestSetter;

    /** @var ValidationRequest */
    private $validationRequest;

    public function __construct
    (
        UserRepository $userRepository,
        JWTTokenManagerInterface $tokenManager,
        AuthenticationSuccessHandler $authHandler,
        ValidationRequest $validationRequest,
        UserRequest $userRequest,
        UserRequestSetter $userRequestSetter,
        UserResponseSetter $userResponseSetter
    )
    {
        $this->userRepository = $userRepository;
        $this->tokenManager = $tokenManager;
        $this->authHandler = $authHandler;
        $this->userRequest = $userRequest;
        $this->userRequestSetter = $userRequestSetter;
        $this->userResponseSetter = $userResponseSetter;
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
        $this->userRequestSetter->setUserRequest($request);

        $violations = $this->validationRequest->validateUserRequest();

        if (count($violations) > 0) {
            return new JsonResponse(['errors' => (string)$violations], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($this->userRepository->findOneBy(['email' => $this->userRequest->email])) {
            return new JsonResponse(['errors' => 'This email is already in use'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = new User();
        $this->persistUser($user, $encoder);

        return new JsonResponse($this->userResponseSetter);

    }

    /**
     * @Route("/login", name="login", methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return JsonResponse
     */
    public function login(Request $request, UserPasswordEncoderInterface $encoder): JsonResponse
    {
        $this->userRequestSetter->setUserRequest($request);
        $request = JsonRequestDataKeeper::keepJson($request);
        // @todo: Find user by email, validate password, set isAdmin

        $email = (string)$request->get('email', '');
        $password = (string)$request->get('password', '');
        $request = $request->toArray();
        // todo: remove
        $user = User::createFromPayload($email, (array)$password);

        $token = $this->tokenManager->createFromPayload($user, $request);
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
        $this->userResponseSetter->setUserResponse($user);
    }
}
