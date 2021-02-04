<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Services\PostRequestParser;
use App\Services\UserRequestParser;
use App\Services\UserRequestValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("api/posts", name="posts.")
 */
class PostsController extends AbstractController implements TokenAuthenticatedController
{
    /** @var PostRepository */
    private PostRepository $postRepository;

    /** @var UserRepository */
    private UserRepository $userRepository;

    /** @var PostRequestParser */
    private PostRequestParser $postRequestParser;

    public function __construct
    (
        PostRepository $postRepository,
        UserRepository $userRepository,
        PostRequestParser $postRequestParser
    )
    {
        $this->postRepository = $postRepository;
        $this->userRepository = $userRepository;
        $this->postRequestParser = $postRequestParser;
    }

//    /**
//     * @Route("/store", name="store", methods={"POST"})
//     * @param Request $request
//     * @return JsonResponse
//     */
//    public function storePost(Request $request): JsonResponse
//    {
//
//        $request = $this->postRequestParser->PostParseRequest($request);
//
//        $post = new Post();
//        $post->setTitle($request->title);
//        $post->setContent($request->content);
//        $post->setCreatedAt();
//        $this->postRepository->plush($post);
//
//        return new JsonResponse($post->getPostData());
//    }

    /**
     * @Route("/show/{id}", name="show", methods={"GET"})
     * @param int $id
     * @return JsonResponse
     */
    public function showPost(int $id): JsonResponse
    {
        $post = $this->postRepository->find($id);

        if (!$post) {
            return new JsonResponse(null,Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($post->getPostData());
    }

    /**
     * * @Route("/update/{id}", name="update", methods={"PUT"})
     */
    public function updatePost(): JsonResponse
    {
    }

    /**
     * * @Route("/delete/{id}", name="delete", methods={"DELETE"})
     */
    public function deletePost()
    {
        dd(4);
    }
}