<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionSubscriber implements EventSubscriberInterface
{
    /**
 * This method is called when a kernel exception occurs.
 * It checks if the exception is an instance of HttpException and sets a JsonResponse with the appropriate status and message.
 * If the exception is not an instance of HttpException, it sets a JsonResponse with a 500 status and the exception message.
 *
 * @param ExceptionEvent $event The event that triggered this method.
 *
 * @return void
 */
public function onKernelException(ExceptionEvent $event)
{
    $exception = $event->getThrowable();

    if ($exception instanceof HttpException){
        $data = [
            'tatus' => $exception->getStatusCode(),
            'essage' => $exception->getMessage()
        ];
        $event->setResponse(new JsonResponse($data));
    } else {
        $data = [
            'tatus' => 500, 
            'essage' => $exception->getMessage()
        ];
        $event->setResponse(new JsonResponse($data));
    }
}

    public static function getSubscribedEvents()
    {
        return [
            'kernel.exception' => 'onKernelException',
        ];
    }
}