<?php

namespace App\Controller;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\Repository\RefreshTokenRepository;
use Doctrine\ORM\ORMException;
use App\Services\RolesManager;
use App\Repository\UserRepository;
use App\Services\UserRequestParser;
use App\Services\UserRequestValidator;
use Doctrine\ORM\OptimisticLockException;
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
    private UserRepository $userRepository;

    /** @var UserRequestParser */
    private UserRequestParser $userRequestParser;

    /** @var RefreshTokenRepository */
    private RefreshTokenRepository $refreshTokenRepository;


    public function __construct(
        UserRepository $userRepository,
        UserRequestParser $userRequestParser,
        RefreshTokenRepository $refreshTokenRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->userRequestParser = $userRequestParser;
        $this->refreshTokenRepository = $refreshTokenRepository;
    }

    /**
     * @Route("/register", name="register", methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $encoder): JsonResponse
    {
        $request = $this->userRequestParser->parseRequest($request);
        $violations = UserRequestValidator::validate($request);

        if (count($violations) > 0) {
            return new JsonResponse(['errors' => (string)$violations], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($this->userRepository->findOneBy(['email' => $request->email])) {
            return new JsonResponse(['errors' => 'This email is already in use'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = new User();
        $user->setName($request->name);
        $user->setEmail($request->email);
        $user->setPassword($encoder->encodePassword($user, $request->password));
        $user->setRole($request->role);
        $this->userRepository->plush($user);

        return new JsonResponse($user->toArray());
    }

    /**
     * @Route("/login", name="login", methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @param JWTTokenManagerInterface $tokenManager
     * @param AuthenticationSuccessHandler $authHandler
     * @param RolesManager $rolesManager
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function login(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        JWTTokenManagerInterface $tokenManager,
        AuthenticationSuccessHandler $authHandler,
        RolesManager $rolesManager
    ): JsonResponse
    {

        $request = $this->userRequestParser->parseRequest($request);

        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['email' => $request->email]);

        if (!$user) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        if (!$encoder->isPasswordValid($user, $request->password)) {
            return new JsonResponse(['errors' => 'Invalid password'], Response::HTTP_UNAUTHORIZED);
        }

        $user->setIsAdmin($rolesManager->isAdmin($user));
        $token = $tokenManager->createFromPayload($user, $user->toArray());
        $authHandler->handleAuthenticationSuccess($user, $token);

        /** @var RefreshToken|null $userToken */
        $userToken = $this->refreshTokenRepository->findOneBy(['username' => $request->email], ['id' => 'DESC']);

        if (!$userToken) {
            return new JsonResponse(['errors' => 'Refresh Token not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['token' => $token, 'refresh_token' => $userToken->refresh_token]);
    }

    /**
     *
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
}
