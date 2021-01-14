<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\RolesManager;
use Doctrine\ORM\ORMException;
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

/**
 * @Route("api/users", name="users.")
 */
class UsersController extends AbstractController implements TokenAuthenticatedController
{
    /** @var UserRepository */
    private UserRepository $userRepository;

    /** @var RolesManager */
    private RolesManager $rolesManager;

    /** @var UserRequestParser */
    private UserRequestParser $userRequestParser;

    public function __construct(
        UserRepository $userRepository,
        RolesManager $rolesManager,
        UserRequestParser $userRequestParser
    )
    {
        $this->userRepository = $userRepository;
        $this->rolesManager = $rolesManager;
        $this->userRequestParser = $userRequestParser;
    }

    /**
     * @Route("/store", name="store", methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function storeAction(Request $request, UserPasswordEncoderInterface $encoder): JsonResponse
    {
        $request = $this->userRequestParser->parseRequest($request, true);
        $violations = UserRequestValidator::validate($request, true);

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
        $user->setIsAdmin($this->rolesManager->isAdmin($user));
        $this->userRepository->plush($user);

        return new JsonResponse($user->toArray());
    }

    /**
     * @Route("/show/{id}", name="show", methods={"GET"})
     * @param int $id
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function showAction(int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->userRepository->find($id);

        if (!$user) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $user->setIsAdmin($this->rolesManager->isAdmin($user));

        return new JsonResponse($user->toArray());
    }

    /**
     * @Route("/update/{id}", name="update", methods={"PUT"})
     * @param int $id
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function updateAction(int $id, Request $request, UserPasswordEncoderInterface $encoder): JsonResponse
    {
        $request = $this->userRequestParser->parseRequest($request, true);
        $violations = UserRequestValidator::validate($request, true);

        if (count($violations) > 0) {
            return new JsonResponse(['errors' => (string)$violations], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var User|null $user */
        $user = $this->userRepository->find($id);

        if (!$user) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        if (
            $request->email != $user->getEmail()
            && $this->userRepository->findOneBy(['email' => $request->email])
        ) {
            return new JsonResponse(['errors' => 'This email already in use'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = new User();
        $user->setName($request->name);
        $user->setEmail($request->email);
        $user->setPassword($encoder->encodePassword($user, $request->password));
        $user->setRole($request->role);
        $user->setIsAdmin($this->rolesManager->isAdmin($user));
        $this->userRepository->plush($user);

        return new JsonResponse($user->toArray());
    }

    /**
     * @Route("/delete/{id}", name="delete", methods={"DELETE"})
     * @param int $id
     * @return JsonResponse
     */
    public function deleteAction(int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->userRepository->find($id);

        if (!$user) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $removed = $this->userRepository->delete($user);

        if (!$removed) {
            return new JsonResponse(['errors' => 'Entity was not removed'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['success' => true]);
    }
}
