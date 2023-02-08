<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();
        $statusCode = method_exists($exception, 'getStatusCode()') ? $exception->getStatusCode() : 400;

        $resp = new JsonResponse(
            [
                'status' => $statusCode,
                'error' => $exception->getMessage()
            ],
            $statusCode
        );

        $event->setResponse($resp);
    }
}