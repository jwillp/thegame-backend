<?php

namespace AppBundle\Controller\Api;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Security\Core\Exception\BadCredentialsException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;

/**
 * Token Controller.
 *
 * @Route("api/events")
 * @Security("has_role('ROLE_USER')")
 */
class EventController extends ApiController
{
    /**
     * List events
     * @Route("/", name="api_events_list")
     * @Method("GET")
     */
    public function listAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $filter = $request->query->get('gameId');

        $qb = $em->getRepository('AppBundle:Event')->findAllQueryBuilder($filter);

        $paginatedCollection = $this
            ->get('pagination_factory')
            ->createCollection($qb, $request, 'api_events_list');


        // Handle game visibility for current user
        $currentUser = $this->getUser();
        
        $paginatedCollection->filter(function($item) use (&$currentUser) {
           return $item->getGame() === null || $item->getGame()->isVisibleTo($currentUser);
        });
        

        // Fetch target and agent data for every event
        foreach ($paginatedCollection->getItems() as $item) {
            $agent = $item->getAgent();
            $target = $item->getTarget();

            $agent->setObject($this->getObjectForParticipant($agent, $em));
            $target->setObject($this->getObjectForParticipant($target, $em));
        }

        $response = $this->createApiResponse($paginatedCollection, 200);
        return $response;
    }


    private function getObjectForParticipant($eventParticipant, $entityManager)  {
        // UPPER SNAKE CASE TO CAMEL CASE
        $type = strtolower($eventParticipant->getType());
        $participantClass = ucfirst(lcfirst(implode('', array_map('ucfirst', explode('_', $type)))));



        $object = $entityManager->getRepository('AppBundle:'. $participantClass)
                     ->findOneById($eventParticipant->getObjectId());

        return $object;
    }
}
