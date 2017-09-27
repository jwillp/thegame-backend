<?php

namespace AppBundle\Controller\Api;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use AppBundle\Entity\Game;

use AppBundle\Entity\Score;

use AppBundle\Entity\Challenge;
use AppBundle\Form\ChallengeType;

use AppBundle\Entity\ChallengeBatch;


use AppBundle\Pagination\PaginatedCollection;
use AppBundle\Pagination\PaginationFactory;

/**
 * Challenge Controller.
 *
 * @Route("/api")
 * @Security("has_role('ROLE_USER')")
 */
class ChallengeController extends ApiController
{
    /**
     * Lists all Challenge entities of a certain game
     *
     * @Route("/games/{id}/challenges", name="api_challenges_index")
     * @Method("GET")
     */
    public function listAction(Request $request, Game $game) {
        
        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('AppBundle:Challenge')->findAllQueryBuilder($game->getId());

        $paginatedCollection = $this
            ->get('pagination_factory')
            ->createCollection($qb, $request, 'api_challenges_index', ['id' => $game->getId()]);

        $paginatedCollection->filter(function($item) {
           return !$item->isDeleted(); // do not return deleted challenges
        });     
     
        $response = $this->createApiResponse($paginatedCollection, 200);

        return $response;
    }

    /**
     * Creates a new Challenge Entity.
     *
     * @Route("/games/{id}/challenges/new", name="api_challenges_new")
     * @Method("POST")
     */
    public function newAction(Request $request, Game $game) {

        $challenge = new Challenge();
        $challenge->setGame($game);
        $form = $this->createForm('AppBundle\Form\ChallengeType', $challenge);
        $this->processForm($request, $form);

        if (!$form->isValid()) {
            return $this->createValidationErrorResponse($form);
        }

        // Set created by
        $currentUser = $this->getUser();
        $challenge->setCreatedBy($currentUser);

        // Save challenge in DB
        $em = $this->getDoctrine()->getManager();
        $em->persist($challenge);
        $em->flush();


        // Create notification
        $event = $this->get('event_factory')
                      ->createNewChallengeEvent($this->getUser(), $challenge);
        $em->persist($event);
        $em->flush();

        $challengeUrl = $this->generateUrl('api_challenges_show', array('id' => $challenge->getId()));
        $response = $this->createApiResponse($challenge, 201);
        $response->headers->set('Location', $challengeUrl);
        
        return $response;
    }

    /**
     * Finds and displays a Game entity.
     *
     * @Route("/challenges/{id}", name="api_challenges_show")
     * @Method("GET")
     */
    public function showAction(Request $request, Challenge $challenge) {
        if($challenge->isDeleted()) {
            throw new Exception("Resource not found", 404);
        }

        return $this->createApiResponse($challenge, 200);
    }

    /**
     * Displays a form to edit an existing Game entity.
     *
     * @Route("/challenges/{id}", name="api_challenges_update")
     * @Method({"PUT"})
     */
    public function updateAction(Request $request, Challenge $challenge) {

        if($challenge->isDeleted()) {
            throw new Exception("Resource not found", 404);
        }

        // Possible events:
        // - Nb points changed

        $oldNbPoints = $challenge->getNbPoints();

        $form = $this->createForm('AppBundle\Form\ChallengeType', $challenge);
        $this->processForm($request, $form);

        if (!$form->isValid()) {
            return $this->createValidationErrorResponse($form);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($challenge);
        $em->flush();


        // Notification
        if ($challenge->getNbPoints() != $oldNbPoints) {
            $event = $this->get('event_factory')
                          ->createChallengePointsUpdatedEvent($this->getUser(), $challenge);
            $em->persist($event);
            $em->flush();

        }

        $challengeUrl = $this->generateUrl('api_challenges_show', array('id' => $challenge->getId()));
        $response = $this->createApiResponse($challenge, 200);
        $response->headers->set('Location', $challengeUrl);
        
        return $response;
    }

    /**
     * Completes a challenge for a certain user
     *
     * @Route("/challenges/{id}/complete", name="api_challenges_complete")
     * @Method("POST")
     */
    public function completeChallengeAction(Request $request, Challenge $challenge) {
        if($challenge->isDeleted()) {
            throw new Exception("Resource not found", 404);
        }

        // Is there a score for the current user ?
        // if not we will create one before incrementing it
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $score = $this->getScoreForUser($user, $challenge);

        // Increment score
        $score->increment();

        $em->persist($score);
        $em->persist($challenge);

        // Create Event
        $event = $this->get('event_factory')->createUserCompletedChallengeEvent(
                        $this->getUser(), 
                        $challenge
        );
        $em->persist($event);

        $em->flush();

        return $this->createApiResponse($challenge, 200);
    }

    /**
     * Completes challenges in batch
     *
     * @Route("/challenges/complete", name="api_challenges_complete_batch")
     * @Method("POST")
     */
    public function completeBatchChallengeAction(Request $request) {
        $data =  $this->deserialize($request->getContent());

        $em = $this->getDoctrine()->getManager();
        $challengeRepo = $em->getRepository('AppBundle:Challenge');
        $user = $this->getUser();

        $batch = new ChallengeBatch();
        $batch->setUser($user);

        foreach ($data['ids'] as $challengeId) {
            $challenge = $challengeRepo->findOneById($challengeId);

            if (!$challenge) {
                // TODO cumul invalid id
            }

            // Get score of user for challenge
            $score = $this->getScoreForUser($user, $challenge);

            // Increment score
            $score->increment();

            $em->persist($score);
            $em->persist($challenge);

            $batch->setGame($challenge->getGame());
            $batch->addChallenge($challenge);
        }

        $em->persist($batch);
        $em->flush();
                
        // Create Event
        $event = $this->get('event_factory')->createNewBatchCompletionEvent(
                        $this->getUser(), 
                        $batch
        );
        $em->persist($event);

        $em->flush();

        return $this->createApiResponse($batch, 200);
    }

    /**
     * Cancel the success of a challenge for a certain user
     *
     * @Route("/challenges/{id}/cancel", name="api_challenges_cancel")
     * @Method("POST")
     */
    public function cancelScoreChallengeAction(Request $request, Challenge $challenge) {

        if($challenge->isDeleted()) {
            throw new Exception("Resource not found", 404);
        }

        // Is there a score for the current user ?
        // if not we will create one before incrementing it
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $score = $this->getScoreForUser($user, $challenge);

        // Decrement score
        $score->decrement();

        // Create Event
        $event = $this->get('event_factory')->createUserCanceledChallengeEvent(
                        $this->getUser(), 
                        $challenge
        );
        $em->persist($event);

        $em->persist($score);
        $em->persist($challenge);
        $em->flush();

        return $this->createApiResponse($challenge, 200);
    }


    /**
     * Deletes a Game entity.
     *
     * @Route("/challenges/{id}", name="api_challenges_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Challenge $challenge) {

        // Make sure current user is admin
        $currentUser = $this->getUser();
        if(!$challenge->getGame()->getAdministrators()->contains($currentUser)) {
            return $this->createApiResponse(
                array('message' => 
                    'Unauthorized Access' , 
                    401
                )
            );
        }

        $challenge->setDeleted(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($challenge);
        $em->flush();

        return $this->createApiResponse($challenge, 200);
    }

    /**
     * Returns the score for a user. 
     * If none found, create a new one.
     */
    private function getScoreForUser($user, $challenge) {
        foreach ($challenge->getScores() as $score) {
            if($score->getUser()->getId() == $user->getId()) {
                return $score;
            }
        }

        // No score was found, create a new one
        // and return it
        $score = new Score();
        $score->setUser($user);
        $challenge->addScore($score);
        return $score;        
    }
}
