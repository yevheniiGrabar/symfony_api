<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Requests\UserRequest;
use App\Responses\UserResponse;
use App\Services\RoleManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;


/**
 * @Route("api/users", name="users.")
 */
class UsersController extends AbstractController
{
    /** @var UserRepository */
    private $userRepository;

    /** @var RoleRepository */
    private $roleRepository;

    /** @var RoleManager */
    private $roleManager;

    /** @var UserRequest */
    private $userRequest;

    /** @var UserResponse */
    private $userResponse;

    public function __construct(
        UserRepository $userRepository,
        RoleRepository $roleRepository,
        RoleManager $roleManager,
        UserRequest $userRequest,
        UserResponse $userResponse
    ){
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->roleManager = $roleManager;
        $this->userRequest = $userRequest;
        $this->userResponse = $userResponse;

    }

    /**
     * @Route("/find-by-credentials", name="find-by-credentials", methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return JsonResponse
     */
    public function getByCredentials(Request $request, UserPasswordEncoderInterface $encoder): JsonResponse
    {
        $request = JsonRequsetDataKeeper::keepJson($request);
        $email = (string)$request->get('email', '');
        $password = (string)$request->get('password', '');

        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            return new JsonResponse(['errors' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$encoder->isPasswordValid($user, $password)) {
            return new JsonResponse(['errors' => 'Invalid password'], Response::HTTP_UNAUTHORIZED);
        }

        $this->setUserResponse($user);

        return new JsonResponse($this->userResponse);
    }

    /**
     * @Route("/store", name="store", methods={"POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return JsonResponse
     */
    public function storeAction(Request $request, UserPasswordEncoderInterface $encoder): JsonResponse
    {
        $this->setUserRequest($request);
        $violations = $this->validateUserRequest();

        if (count($violations) >0) {
            return new JsonResponse(['errors' => (string)$violations], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = new User();
        $this->persistUser($user, $encoder);

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

        $this->setUserResponse($user);

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
        $this->setUserRequest($request);
        $violations = $this->validateUserRequest();

        if (count($violations) >0) {
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

        $this->persistUser($user, $encoder);

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

    /**
     * @param Request $request
     */
    private function setUserRequest(Request $request): void
    {
        $request = JsonRequestDataKeeper::keepJson($request);
        $roleId = (int)$request->get('role_id', 0);
        $role = $this->roleManager->findOrDefault($roleId);
        $this->userRequest->name = (string)$request->get('name', '');
        $this->userRequest->email = (string)$request->get('email', '');
        $this->userRequest->password = (string)$request->get('password', '');
        $this->userRequest->role = $role;
    }

    private function validateUserRequest(): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();

        $violations = $validator->validate($this->userRequest->name,[
            new NotBlank(['message'=> 'Name is required']),
            new Length([
                'min' => 2,
                'max' => 255,
                'minMessage' => 'Name is too short',
                'maxMessage' => 'Name is too long'
            ]),
        ]);

        $violations->addAll(
            $validator->validate($this->userRequest->password, [
                new NotBlank(['message' => 'Password is required']),
                new Length([
                    'min' => 8,
                    'max' => 255,
                    'minMessage' => 'Password is too short',
                    'maxMessage' => 'Password is too long'
                ]),
                new NotCompromisedPassword(['message' => 'This password was compromised'])
            ])
        );

        $violations->addAll(
            $validator->validate($this->userRequest->email, [
                new NotBlank(['message' => 'Email is required']),
                new Email(['message' => 'Invalid email'])
            ])
        );

        $violations->addAll(
            $validator->validate($this->userRequest->role, [
                new NotNull(['message' => 'Role is required'])
            ])
        );

        return $violations;
    }

    /**
     * @param User $user
     */
    private function setUserResponse(User $user): void
    {
        $this->userResponse->id = $user->getId();
        $this->userResponse->name = $user->getName();
        $this->userResponse->email = $user->getEmail();
        $this->userResponse->isAdmin = $this->rolesManager->isAdmin($user);
    }

    /**
     * @param User $user
     * @param UserPasswordEncoderInterface $encoder
     */
    private function persistUser(User $user, UserPasswordEncoderInterface $encoder): void
    {
        $user->setName($this->userRequest->name);
        $user->setEmail($this->userRequest->email);
        $user->setPassword($encoder->encodePassword($user, $this->userRequest->password));
        $user->setRole($this->userRequest->role);
        $this->userRepository->plush($user);
        $this->setUserResponse($user);
    }
}