<?php 

namespace AppBundle\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\FormInterface;

/**
 * ApiController
 */
abstract class ApiController extends Controller
{
    
    protected function processForm(Request $request, FormInterface $form) {
        //$data = json_decode($request->getContent(), true);
        $data =  $this->deserialize($request->getContent());

        $clearMissing = $request->getMethod() != 'PATCH';
        $form->submit($data, $clearMissing);
        return $data;
    }

    /**
     * Creates a valid (serialised) response for the api with a $statusCode
     */
    protected function createApiResponse($data, $statusCode = 200) {
        $data = $this->serialize($data);

        return new Response($data, $statusCode, array(
            'Content-Type' => 'application/json', 
        ));
    }

    /**
     * Serializes data for API
     */
    protected function serialize($data, $format='json') {
        $context = new SerializationContext();
        $context->setSerializeNull(true);

        return $this->container->get('jms_serializer')->serialize($data, $format, $context);
        //return json_encode($data);
    }

    /**
     * Deserializes JSON
     */
    protected function deserialize($data) {
        $data = json_decode($data);
        if ($data === null) {
            throw new ApiErrorException(400, array(
                'type' => 'invalid_request_body_format',
                'title' => 'Invalid JSON',
                'errors' => []
            ));
        }
        return $this->camelizeArrayKeys($data);
    }

    protected function getErrorsFromForm(FormInterface $form) {
        $errors = array();
        foreach ($form->getErrors() as $error) {    
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $this->snakizeCamelArrayKeys($errors);
    }

    protected function createValidationErrorResponse(FormInterface $form) {
        /*header('Content-Type: cli');
        dump((string) $form->getErrors(true, false));die;*/
        $errors = $this->getErrorsFromForm($form);
        return $this->createErrorResponse(
            400,
            'validation_error', // TODO create constant
            'There was a validation error',
            $errors
        );
    }

    protected function createErrorResponse($statusCode, $type, $title, $errors = []) {
        $data = array(
            'type' => $type,
            'title' => $title,
            'errors' => $errors
        );
        return $this->createApiResponse($data, $statusCode);
    }

    /**
     * Changes snake_keys to camelCase of an multidimensional array 
     */
    protected function camelizeArrayKeys($apiResponseArray)
    {
        $arr = [];
        foreach ($apiResponseArray as $key => $value) {
            $key = lcfirst(implode('', array_map('ucfirst', explode('_', $key))));


            if (is_array($value)){
                $value = $this->camelizeArrayKeys($value);
            }
          $arr[$key] = $value;
      }
      return $arr;
    }

    /**
     * Changes camelCase to snake_keys of an multidimensional array 
     */
    protected function snakizeCamelArrayKeys($apiResponseArray)
    {
        $arr = [];
        foreach ($apiResponseArray as $key => $value) {
            $key = $this->snakizeCamel($key);

            if (is_array($value)){
                $value = $this->snakizeCamelArrayKeys($value);
            }
          $arr[$key] = $value;
      }
      return $arr;
    }

    /**
     * Converts a string from camelCase to snake_case
     */
    protected function snakizeCamel($input) {
        if(!$input) return $input;
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
    }
}