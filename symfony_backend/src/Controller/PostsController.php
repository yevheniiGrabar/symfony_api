<?php

namespace App\Controller;

use Carbon\Carbon;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Constants\ResponseMessages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;

/**
 * @Route("/api/posts", name="posts.")
 */
class PostsController extends AbstractController
{
    /** @var PostRepository */
    private PostRepository $postRepository;

    /** @var UserRepository */
    private UserRepository $userRepository;

    /** @var TokenStorageInterface */
    private TokenStorageInterface $storage;

    public function __construct
    (
        PostRepository $postRepository,
        UserRepository $userRepository,
        TokenStorageInterface $storage
    )
    {
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
        $this->storage = $storage;
    }

    /**
     * @Route("/store", name="store", methods={"POST"})
     * @param Request $request
     * @param TokenStorageInterface $storage
     * @return JsonResponse
     */
    public function storeAction(Request $request): JsonResponse
    {
        $title = (string)$request->get('title', '');
        $content = (string)$request->get('content', '');

        $violations = self::validateUserPost($request);

        if (count($violations) > 0) {
            return new JsonResponse(['errors' => (string)$violations], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $post = new Post();
        $post->setTitle($title);
        $post->setContent($content);
        $post->setCreatedAt(Carbon::now());
        $post->setUpdatedAt(Carbon::now());
        $post->setUser($this->getCurrentUser());

        $this->postRepository->plush($post);

        return new JsonResponse($post->toArray());
    }

    /**
     * @Route("/show/{id}", name="show", methods={"GET"})
     * @param int $id
     * @return JsonResponse
     */
    public function showAction(int $id): JsonResponse
    {
        $post = $this->postRepository->find($id);

        if (!$post) {
            return new JsonResponse(['errors' => ResponseMessages::POST_NOT_FOUND_MESSAGE], Response::HTTP_NOT_FOUND);
        }

        if (!$this->currentUserIsPostOwner($post)) {
            return new JsonResponse(['errors' => ResponseMessages::ACCESS_DENIED_MESSAGE], Response::HTTP_FORBIDDEN);
        }

        return new JsonResponse($post->toArray());
    }

    /**
     * @Route("/update/{id}", name="update", methods={"PUT"})
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAction(int $id, Request $request): JsonResponse
    {
        $user = $this->getCurrentUser();

        if (!$user) {
            return new JsonResponse
            (['errors' => ResponseMessages::USER_NOT_FOUND_MESSAGE], Response::HTTP_NOT_FOUND);
        }

        $post = $this->postRepository->find($id);

        if (!$post) {
            return new JsonResponse(['errors' => ResponseMessages::POST_NOT_FOUND_MESSAGE], Response::HTTP_NOT_FOUND);
        }

        if (!$this->currentUserIsPostOwner($post)) {
            return new JsonResponse(['errors' => ResponseMessages::ACCESS_DENIED_MESSAGE], Response::HTTP_FORBIDDEN);
        }

        $violations = self::validateUserPost($request);

        if (count($violations) > 0) {
            return new JsonResponse(['errors' => (string)$violations], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $title = (string)$request->get('title', '');
        $content = (string)$request->get('content', '');

        $post->setTitle($title);
        $post->setContent($content);
        $post->setUpdatedAt(Carbon::now());
        $post->setUser($this->getCurrentUser());

        $this->postRepository->plush($post);

        return new JsonResponse($post->toArray());
    }

    /**
     * @Route("/delete/{id}", name="delete", methods={"DELETE"})
     * @param int $id
     * @return JsonResponse
     */
    public function deleteAction(int $id): JsonResponse
    {
        $post = $this->postRepository->find($id);

        if (!$post) {
            return new JsonResponse(['errors' => ResponseMessages::POST_NOT_FOUND_MESSAGE], Response::HTTP_NOT_FOUND);
        }

        if (!$this->currentUserIsPostOwner($post)) {
            return new JsonResponse(['errors' => ResponseMessages::ACCESS_DENIED_MESSAGE], Response::HTTP_FORBIDDEN);
        }

        $deleted = $this->postRepository->delete($post);

        if (!$deleted) {
            return new JsonResponse(
                ['errors' => ResponseMessages::ENTITY_WAS_NOT_REMOVED_MESSAGE],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse(['message' => ResponseMessages::POST_DELETED_SUCCESSFULLY], Response::HTTP_OK);
    }

    /**
     * @return User
     */
    private function getCurrentUser(): User
    {
        /** @var User $user */
        $user = $this->storage->getToken()->getUser();
        $userId = $user->getId();

        /** @var User $user */
        $user = $this->userRepository->find($userId);

        return $user;
    }

    /**
     * @param Post $post
     * @return bool
     */
    private function currentUserIsPostOwner(Post $post): bool
    {
        return $post->getUser()->getId() == $this->getCurrentUser()->getId();
    }

    private static function validateUserPost(Request $request)
    {
        $validator = Validation::createValidator();

        $violations = $validator->validate($request->get('title'), [
            new NotBlank(['message' => ResponseMessages::TITLE_IS_REQUIRED_MESSAGE]),
            new Length([
                'min' => 4,
                'max' => 255,
                'minMessage' => ResponseMessages::TITLE_IS_TOO_SHORT_MESSAGE,
                'maxMessage' => ResponseMessages::TITLE_IS_TOO_LONG_MESSAGE,
            ]),
        ]);
        $violations->addAll(
            $validator->validate($request->get('content'), [
                new NotBlank(['message' => ResponseMessages::CONTENT_IS_REQUIRED_MESSAGE]),
                new Length([
                    'min' => 10,
                    'max' => 255,
                    'minMessage' => ResponseMessages::CONTENT_IS_TOO_SHORT_MESSAGE,
                    'maxMessage' => ResponseMessages::CONTENT_IS_TOO_LONG_MESSAGE,
                ]),
            ])
        );
        return $violations;
    }
}

