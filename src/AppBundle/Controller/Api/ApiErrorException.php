<?php 

namespace AppBundle\Controller\Api;

use Symfony\Component\HttpKernel\Exception\HttpException;

/**
* ApiErrorException
*/
class ApiErrorException extends HttpException
{
    private $errorObj; // array

    function __construct($statusCode, $errorObj) {
        $this->errorObj = $errorObj;
        $message = $errorObj['title'];
        parent::__construct(
            $statusCode, 
            $message, 
            null /* previous */, 
            array() /* header */, 
            0 /* code */
        );
    }

    public function getErrorObj() {
        return $this->errorObj;
    }
}