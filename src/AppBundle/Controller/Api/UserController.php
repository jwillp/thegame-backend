<?php

namespace AppBundle\Controller\Api;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;

use AppBundle\Pagination\PaginatedCollection;
use AppBundle\Pagination\PaginationFactory;

use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;

/**
 * User Controller.
 *
 * @Route("/api/users")
 */
class UserController extends ApiController
{
    /**
    * Finds and displays a Game entity.
    *
    * @Route("/register", name="api_users_register")
    * @Method("POST")
    */
    public function registerAction(Request $request) {
        // Modify received data to add the mandatory form_name
        $data =  $this->deserialize($request->getContent(), 'json');

        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.registration.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $this->createApiResponse($event->getResponse(), 500);
        }
        $form = $formFactory->createForm([
            'csrf_protection' => false, 
            'allow_extra_fields' => true
        ]);

        $form->setData($user);
        $form->submit($data);

        if (!$form->isValid()) {
            return $this->createValidationErrorResponse($form);
        }

        $user->setPlainPassword($data['plainPassword']);

        $event = new FormEvent($form, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

        $userManager->updateUser($user);

        $userUrl = $this->generateUrl('api_users_show', ['id' => $user->getId()]);
        $response = $this->createApiResponse($user, 201);
        $response->headers->set('Location', $userUrl);
        
        return $response;
    }

    /**
     * Logs a user in and returns a token
     *
     * @Route("/login", name="api_user_login")
     * @Method("POST")
     */
    public function login(Request $request) {
        // Request token. This method is really an alias
        return $this->forward('AppBundle:Api/Token:newToken');
    }

    /**
    * Finds and displays a User entity's profile
    *
    * @Route("/{id}", name="api_users_show")
    * @Method("GET")
    * @Security("is_granted('ROLE_USER')")
    */
    public function showAction(Request $request, User $user) {
        return $this->createApiResponse($user, 200);
    }

    /**
     * List users
     * @Route("/", name="api_users_index")
     * @Method("GET")
     * Security("is_granted('ROLE_USER')")
     */
    public function listAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('AppBundle:User')->createQueryBuilder('user');

        $paginatedCollection = $this
            ->get('pagination_factory')
            ->createCollection($qb, $request, 'api_users_index');

        $response = $this->createApiResponse($paginatedCollection, 200);

        return $response;
    }
}
