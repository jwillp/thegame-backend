<?php

namespace AppBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Doctrine\ORM\EntityManager;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;

use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Controller\Api\ApiErrorException;


class JwtTokenAuthenticator extends AbstractGuardAuthenticator {

    private $jwtEncoder;
    private $em;

    function __construct(JWTEncoderInterface $jwtEncoder, EntityManager $em) {
        $this->jwtEncoder = $jwtEncoder;
        $this->em = $em;
    }

    public function getCredentials(Request $request) {

        // Extract token from the header
        $extractor = new AuthorizationHeaderTokenExtractor(
            'Bearer',
            'Authorization'
        );

        $token = $extractor->extract($request);

        if (!$token) {
            return;
        }

        return $token;
    }

    public function getUser($credentials, UserProviderInterface $userProvider) {
        // Extract username from the token
        try {
            $data = $this->jwtEncoder->decode($credentials);
        } catch (JWTDecodeFailureException $e) {
            // if you want to, use can use $e->getReason() to find out which of the 3 possible things went wrong
            // and tweak the message accordingly
            // https://github.com/lexik/LexikJWTAuthenticationBundle/blob/05e15967f4dab94c8a75b275692d928a2fbf6d18/Exception/JWTDecodeFailureException.php
            throw new CustomUserMessageAuthenticationException('Invalid Token');
        }
        $username = $data['username'];

        $user = $this->em
                    ->getRepository('AppBundle:User')
                    ->findOneBy(['username' => $username]);

        //exit("USER " . $user->getUsername());
        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user) {
       return true; //token was valid, we can authenticate
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception) {
        return new JsonResponse(array(
            'title' => 'Authentication Error',
            'errors' => [$exception->getMessageKey()]
        ), 401);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey) {
        // do nothing - let controller be called
    }

    public function supportsRememberMe() {
        return false;
    }

    public function start(Request $request, AuthenticationException $authException = null) {
        // called when authentication info is missing from a
        // request that requires it

        return new JsonResponse([
            'type' => 'about:blank',
            'title' => 'Unauthorized',
            'errors' => ''
        ], 401);
    }
}