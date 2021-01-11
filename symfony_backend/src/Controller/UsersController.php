<?php

namespace App\Controller;

use App\Entity\User;
use App\Requests\UserRequest;
use App\Services\RolesManager;
use App\Responses\UserResponse;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("api/users", name="users.")
 */
class UsersController extends AbstractController
{
    /** @var UserRepository */
    private $userRepository;

    /** @var RoleRepository */
    private $roleRepository;

    /** @var RolesManager */
    private $rolesManager;

    /** @var UserRequest */
    private $userRequest;

    /** @var UserResponse */
    private $userResponse;

    public function __construct(
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        RolesManager $rolesManager,
        UserRequest $userRequest,
        UserResponse $userResponse
    )
    {
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->rolesManager = $rolesManager;
        $this->userRequest = $userRequest;
        $this->userResponse = $userResponse;
    }

    /**
     * @Route("/store", name="store", methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return JsonResponse
     */
    public function storeAction(Request $request, UserPasswordEncoderInterface $encoder): JsonResponse
    {
        $this->userRequest->setUserRequest($request);
        // @todo: validate with role
        $violations = $this->userRequest->validateUserRequest();

        if (count($violations) > 0) {
            return new JsonResponse(['errors' => (string)$violations], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($this->userRepository->findOneBy(['email' => $this->userRequest->email])) {
            return new JsonResponse(['errors' => 'This email is already in use'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // @todo: Find and set role by role_id

        $user = new User();
        $this->userRepository->persistUser($user, $encoder);

        return new JsonResponse($this->userResponse);
    }

    /**
     * @Route("/show/{id}", name="show", methods={"GET"})
     * @param int $id
     * @return JsonResponse
     */
    public function showAction(int $id): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->userRepository->find($id);

        if (!$user) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $this->userResponse->setUserResponse($user);

        return new JsonResponse($this->userResponse);
    }

    /**
     * @Route("/update/{id}", name="update", methods={"PUT"})
     * @param int $id
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return JsonResponse
     */
    public function updateAction(int $id, Request $request, UserPasswordEncoderInterface $encoder): JsonResponse
    {
        $this->userRequest->setUserRequest($request);
        $violations = $this->userRequest->validateUserRequest();

        if (count($violations) > 0) {
            return new JsonResponse(['errors' => (string)$violations], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var User|null $user */
        $user = $this->userRepository->find($id);

        if (!$user) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        if (
            $this->userRequest->email != $user->getEmail()
            && $this->userRepository->findOneBy(['email' => $this->userRequest->email])
        ) {
            return new JsonResponse(['errors' => 'This email already in use'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->userRepository->persistUser($user, $encoder);

        return new JsonResponse($this->userResponse);
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
