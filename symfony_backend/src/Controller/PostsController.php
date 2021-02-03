<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("api/posts", name="posts.")
 */
class PostsController extends AbstractController implements TokenAuthenticatedController
{
    /** @var PostRepository */
    private PostRepository $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    /**
     * @Route("/store", name="store", methods={"POST"})
     */
    public function storePost()
    {
        dd(1);
    }

    /**
     * @Route("/show/{id}", name="show", methods={"GET"})
     * @param int $id
     */
    public function showPost(int $id)
    {
     dd(2);
    }

    /**
     * * @Route("/update/{id}", name="update", methods={"PUT"})
     */
    public function updatePost()
    {
        dd(3);
    }

    /**
     * * @Route("/delete/{id}", name="delete", methods={"DELETE"})
     */
    public function deletePost()
    {
        dd(4);
    }
}