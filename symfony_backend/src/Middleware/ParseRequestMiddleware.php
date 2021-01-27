<?php

namespace App\Middleware;

use App\Services\JsonRequestDataKeeper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ParseRequestMiddleware implements EventSubscriberInterface
{
    /** @param ControllerEvent $event */
    public function onKernelController(ControllerEvent $event): void
    {
        /** @var AbstractController|AbstractController[] $controller */
        $controller = $event->getController();

        if (is_array($controller)) {
            $controller = $controller[0];
        }

        if (!$controller instanceof AbstractController) {
            return;
        }

        $request = $event->getRequest();
        $request = JsonRequestDataKeeper::keepJson($request);
    }

    /** @return array */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}
