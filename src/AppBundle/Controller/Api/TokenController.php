<?php

namespace AppBundle\Controller\Api;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Security\Core\Exception\BadCredentialsException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


use AppBundle\Entity\User;
use AppBundle\Form\UserType;

/**
 * Token Controller.
 *
 * @Route("/api/tokens")
 */
class TokenController extends ApiController
{
    /**
     * @Route("/", name="api_tokens_new")
     * @Method("POST")
     */
    public function newTokenAction(Request $request) {

        $user = $this->getDoctrine()
                    ->getRepository('AppBundle:User')
                    ->findOneBy(['username' => $request->getUser()]);
        if (!$user) {
            throw $this->createNotFoundException();
            //throw new BadCredentialsException();
        }

        $isValid = $this->get('security.password_encoder')
            ->isPasswordValid($user, $request->getPassword());

        if (!$isValid) {
            throw new BadCredentialsException();
        }

        $token = $this->get('lexik_jwt_authentication.encoder')->encode([
               'username' => $user->getUsername(),
               'exp' => time() + 3600 // 1 hour expiration
        ]);


        return $this->createApiResponse(['token' => $token]);
    }
}
