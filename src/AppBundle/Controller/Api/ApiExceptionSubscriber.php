<?php 

namespace AppBundle\Controller\Api;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
/**
* ApiExceptionSubscriber 
*/
class ApiExceptionSubscriber implements EventSubscriberInterface 
{
    function __construct($debug)
    {
        $this->debug = $debug;
    }
    public function onKernelException(GetResponseForExceptionEvent $event) {
        $e = $event->getException();
        if ($e instanceof ApiErrorException) {
           $statusCode = $e->getErrorObj();
           if ($statusCode == 500 && $this->debug) return;

           $response = new JsonResponse(
               $e->getErrorObj(),
               $e->getStatusCode()
           );
           $response->headers->set('Content-Type', 'application/problem+json');
           $event->setResponse($response);
        } else {
            $statusCode = ($e instanceof HttpExceptionInterface) ? $e->getStatusCode() : 500;

            if ($statusCode == 500 && $this->debug) {
              return;
            } 
              
            // title
            $statusTexts = array(
                401 => 'Unauthorized Access',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                500 => 'Fatal Error'
            );

            $title = isset($statusTexts[$statusCode]) ? 
                $statusTexts[$statusCode] : 'There was an error with status code : ' . $statusCode;
                
            $obj = array(
                'type' => 'about:blank',
                'title' => $title,
                'errors' => ''
            );
            $response = new JsonResponse(
              $obj,
              $statusCode
            );
            $response->headers->set('Content-Type', 'application/problem+json');
            $event->setResponse($response);
        }

    }

    public static function getSubscribedEvents() {
        return array(
            KernelEvents::EXCEPTION => 'onKernelException'
        );
    }
}