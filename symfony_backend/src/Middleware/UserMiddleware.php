<?php

namespace App\Middleware;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Services\RolesManager;
use App\Services\UserRequestParser;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;
use App\Controller\TokenAuthenticatedController;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class UserMiddleware
 * @package App\Middleware
 */
class UserMiddleware implements EventSubscriberInterface
{
    private UrlGeneratorInterface $router;

    /** @var UserRequestParser */
    private UserRequestParser $userRequestParser;

    /** @var TokenStorageInterface */
    private TokenStorageInterface $tokenStorageInterface;

    /** @var RolesManager */
    private RolesManager $rolesManager;

    /** @var PostRepository */
    private PostRepository $postRepository;

    public function __construct
    (
        UrlGeneratorInterface $router,
        UserRequestParser $userRequestParser,
        TokenStorageInterface $storage,
        RolesManager $rolesManager,
        PostRepository $postRepository
    )
    {
        $this->router = $router;
        $this->userRequestParser = $userRequestParser;
        $this->tokenStorageInterface = $storage;
        $this->rolesManager = $rolesManager;
        $this->postRepository = $postRepository;
    }

    /**
     * @param ControllerEvent $event
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function onKernelController(ControllerEvent $event): void
    {
        /** @var AbstractController|AbstractController[] $controller */
        $controller = $event->getController();

        if (is_array($controller)) {
            $controller = $controller[0];
        }

        if (!$controller instanceof TokenAuthenticatedController) {
            return;
        }

        $request = $event->getRequest();
        $user = $this->tokenStorageInterface->getToken()->getUser();
        $post = $this->postRepository->find(['id' => $request->get('id')]);

        if (!$user instanceof User) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Unauthorized');
        }

        if (!$post instanceof Post) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'Post not found');
        }

        if ($user->isAdmin) {
            return;
        }

        $defaultUserEndpoints = $this->userEndpoints($user->getId(), $post->getId());
        $requestUri = $request->getRequestUri();

        if (!in_array($requestUri, $defaultUserEndpoints)) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Access denied');
        }

        if ($post->getUser()->getId() !== $user->getId()) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Access denied for current user');
        }

        $roleId = (int)$this->rolesManager->getDefaultRole()->getId();
        $request->request->set('role_id', $roleId);
    }

    /** @return array */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    /**
     * @param int $userId
     * @param int $postId
     * @return array
     */
    public function userEndpoints(int $userId, int $postId): array
    {
        return [
            $this->router->generate('users.show', ['id' => $userId]),
            $this->router->generate('users.update', ['id' => $userId]),
            $this->router->generate('users.delete', ['id' => $userId]),
            $this->router->generate('posts.show', ['id' => $postId]),
            $this->router->generate('posts.update', ['id' => $postId]),
            $this->router->generate('posts.delete', ['id' => $postId]),
        ];
    }
}

