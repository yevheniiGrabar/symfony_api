<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Services\UserRequestValidator;
use Carbon\Carbon;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("api/posts", name="posts.")
 */
class PostsController extends AbstractController implements TokenAuthenticatedController
{
    /** @var PostRepository */
    private PostRepository $postRepository;

    /** @var UserRepository */
    private UserRepository $userRepository;

    public function __construct
    (
        PostRepository $postRepository,
        UserRepository $userRepository
    )
    {
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/store", name="store", methods={"POST"})
     * @param Request $request
     * @param UserInterface $userInterface
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function storeAction(Request $request, UserInterface $userInterface): JsonResponse
    {
        $userId = $userInterface->getId();
        $user = $this->userRepository->findOneBy(['id' => $userId]);

        if(!$user) {
            return new JsonResponse(UserRequestValidator::USER_NOT_FOUND_MESSAGE, Response::HTTP_NOT_FOUND);
        }

        if ($userId != $user->id) {
            return new JsonResponse(UserRequestValidator::ACCESS_DENIED_MESSAGE, Response::HTTP_FORBIDDEN);
        }

        $postDate = $this->postRepository->find('createdAt');
        $postDate = Carbon::now($postDate);
        $post = new Post();
        $post->setTitle($request->get('title', ''));
        $post->setContent($request->get('content', ''));
        $post->setCreatedAt(Carbon::now());
        $post->setUpdatedAt($postDate);
        $post->setUser($user);
        $this->postRepository->plush($post);

        return new JsonResponse($post->getPostData());
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
            return new JsonResponse(null,Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($post->getPostData());
    }

    /**
     * * @Route("/update/{id}", name="update", methods={"PUT"})
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateAction(int $id, Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $this->userRepository->find($id);
        //dd($user->getId());
        if (!$user) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        $post = $this->postRepository->find($id);
        if (!$post) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $post->setTitle($request->get('title', ''));
        $post->setContent($request->get('content', ''));
        $post->setUpdatedAt(Carbon::now());
        $post->setUser($user);
        $this->postRepository->plush($post);

        return new JsonResponse($post->getPostData());
    }

    /**
     * * @Route("/delete/{id}", name="delete", methods={"DELETE"})
     */
    public function deleteAction()
    {
        dd(4);
    }
}