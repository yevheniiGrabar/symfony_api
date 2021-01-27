<?php

namespace App\Middleware;

use App\Entity\User;
use App\Services\RolesManager;
use App\Services\UserRequestParser;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Response;
use App\Controller\TokenAuthenticatedController;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserMiddleware implements EventSubscriberInterface
{
    private UrlGeneratorInterface $router;

    /** @var UserRequestParser */
    private UserRequestParser $userRequestParser;

    /** @var TokenStorageInterface */
    private TokenStorageInterface $tokenStorageInterface;

    /** @var RolesManager */
    private RolesManager $rolesManager;

    public function __construct
    (
        UrlGeneratorInterface $router,
        UserRequestParser $userRequestParser,
        TokenStorageInterface $storage,
        RolesManager $rolesManager
    )
    {
        $this->router = $router;
        $this->userRequestParser = $userRequestParser;
        $this->tokenStorageInterface = $storage;
        $this->rolesManager = $rolesManager;
    }

    /** @param ControllerEvent $event */
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

        if (!$user instanceof User) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED, 'Unauthorized');
        }

        if ($user->isAdmin) {
            return;
        }

        $defaultUserEndpoints = $this->userEndpoints($user->getId());
        $requestUri = $request->getRequestUri();

        if (!in_array($requestUri, $defaultUserEndpoints)) {
            throw new HttpException(Response::HTTP_FORBIDDEN, 'Access denied');
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
     * @return array
     */
    public function userEndpoints(int $userId): array
    {
        return [
            $this->router->generate('users.show', ['id' => $userId]),
            $this->router->generate('users.update', ['id' => $userId]),
            $this->router->generate('users.delete', ['id' => $userId])
        ];
    }
}

