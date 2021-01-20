<?php

/** @noinspection PhpUnused */

namespace App\Middleware;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ExceptionHandler implements EventSubscriberInterface
{
    /** @param ExceptionEvent $event */
    public function onKernelException(ExceptionEvent $event)
    {
        $throwable = $event->getThrowable();

        if ($throwable instanceof HttpException) {
            $response = new JsonResponse(['errors' => $throwable->getMessage()], $throwable->getStatusCode());
            $event->setResponse($response);
        }
    }

    /** @return array */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
