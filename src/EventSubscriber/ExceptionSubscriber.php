<?php
// src/EventSubscriber/ExceptionSubscriber.php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use App\Exception\ProjectNotFoundException;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $response = new JsonResponse();
        $response->headers->set('Content-Type', 'application/json');

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
        } elseif ($exception instanceof ProjectNotFoundException) {
            $response->setStatusCode(404); // Not Found
        } else {
            $response->setStatusCode(500); // Internal Server Error
        }

        $response->setContent(json_encode([
            'error' => [
                'code' => $response->getStatusCode(),
                'message' => $exception->getMessage(),
            ]
        ]));

        $event->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
