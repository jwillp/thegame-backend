<?php

namespace AppBundle\Controller\Api;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use AppBundle\Entity\Game;
use AppBundle\Form\GameType;


use AppBundle\Pagination\PaginatedCollection;
use AppBundle\Pagination\PaginationFactory;

/**
 * Game controller.
 *
 * @Route("api/games")
 * @Security("has_role('ROLE_USER')")
 */
class GameController extends ApiController
{
    /**
     * Lists all Game entities.
     *
     * @Route("/", name="api_games_index")
     * @Method("GET")
     */
    public function listAction(Request $request) {
        
        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('AppBundle:Game')->findAllQueryBuilder();

        $paginatedCollection = $this
            ->get('pagination_factory')
            ->createCollection($qb, $request, 'api_games_index');


        // Handle game visibility for current user
        $currentUser = $this->getUser();

        $paginatedCollection->filter(function($item) use (&$currentUser) {
           return $item->isVisibleTo($currentUser) && !$item->isDeleted();
        });

        $response = $this->createApiResponse($paginatedCollection, 200);

        return $response;
    }

    /**
     * Creates a new Game entity.
     *
     * @Route("/new", name="api_games_new")
     * @Method("POST")
     */
    public function newAction(Request $request) {

        $game = new Game();
        $form = $this->createForm('AppBundle\Form\GameType', $game);
        $this->processForm($request, $form);

        if (!$form->isValid()) {
            return $this->createValidationErrorResponse($form);
        }

        // validate 
        // if ($game->getEndDate() < $$game->getStartDate()) { return INVALID DATES }

        // Set created by
        $currentUser = $this->getUser();
        $game->setCreatedBy($currentUser);

        // Save Game in DB
        $em = $this->getDoctrine()->getManager();
        $em->persist($game);
        $em->flush();

        // Create notification
        $event = $this->get('event_factory')
                      ->createNewGameEvent($currentUser, $game);
        $em->persist($event);
        $em->flush();



        $gameUrl = $this->generateUrl('api_games_show', array('id' => $game->getId()));
        $response = $this->createApiResponse($game, 201);
        $response->headers->set('Location', $gameUrl);
        


        return $response;
    }

    /**
     * Finds and displays a Game entity.
     *
     * @Route("/{id}", name="api_games_show")
     * @Method("GET")
     */
    public function showAction($id) {
        $repo = $this->getDoctrine()
                     ->getManager()
                     ->getRepository('AppBundle:Game');
        $game = $repo->find($id);
        if(!$game) {
            return $this->createErrorResponse(
                404,
                'resource_not_found', // TODO create constant TYPE_RESOURCE_NOT_FOUND in a ApiError class ?
                'Could not find game with id ' . $id
            );
        }

        return $this->createApiResponse($game, 200);
    }


    /**
     * Displays a Game entity's leaderboard
     *
     * @Route("/{id}/leaderboard", name="api_games_leaderboard")
     * @Method("GET")
     */
    public function leaderboardAction(Request $request, Game $game) {
        
        $em = $this->getDoctrine()->getManager();

        // Get challenges
        $challenges = $em->getRepository('AppBundle:Challenge')
                         ->findByGame($game);
    
        // For each challenge get a pair (user.username, nbPoints)
        $userScores = array();
        foreach($challenges as $challenge) {
            if($challenge->isDeleted()) continue;
            foreach ($challenge->getScores() as $score) {
                $user = $score->getUser();
                $username = $user->getUsername();
                $nbPoints = $score->getNbPoints();

                if(!array_key_exists($username, $userScores)) {
                    $userScores[$username] = array(
                        'user' => $user,
                        'nbPoints' => 0
                    );
                }
                $userScores[$username]['nbPoints'] += $nbPoints;
            }
        }

        // Make sure users with same score gets awarded same rank
        // group by number of points
        $ranks = array();
        foreach ( $userScores as $value ) {
            $ranks[$value['nbPoints']][] = $value;
        }
        $ranks = array_reverse($ranks);

        // Sort from highest to highest
        /*usort($group, function($a, $b){
            return $a['nbPoints'] <= $b['nbPoints'];
        });*/

        // Rank from 1 to N instead of 0 to N
        array_unshift($ranks, "tmp");
        unset($ranks[0]);

        return $this->createApiResponse($ranks, 200);
    }


    /**
     * Displays a form to edit an existing Game entity.
     *
     * @Route("/{id}", name="api_games_update")
     * @Method({"PUT"})
     */
    public function updateAction(Request $request, Game $game) {

        $currentUser = $this->getUser();

        // Make sure current user is admin
        if(!$game->getAdministrators()->contains($currentUser)) {
            return $this->createApiResponse(array('message' => 'Unauthorized Access' , 401));
        }

        
        $form = $this->createForm('AppBundle\Form\GameType', $game);
        $data = $this->processForm($request, $form);

        if (!$form->isValid()) {
            return $this->createValidationErrorResponse($form);
        }

        $em = $this->getDoctrine()->getManager();
        $userRepo = $em->getRepository('AppBundle:User');

        // Handle admins that are specifie by names
        if(array_key_exists('administrators', $data)){
            $administrators = $game->getAdministrators();
            // If there is only one admin in the provided data,
            // make sure it is the current user. 
            // (In order to avoid an admin to delete themself if they are 
            // the only one)
            if(count($data['administrators']) == 0) {
                return $this->createErrorResponse(
                    404,
                    'no_admin_specified', // TODO create constant in a ApiError class ?
                    'You must specify at least one admin '
                );
            }

            $administrators->clear();
            foreach ($data['administrators'] as $admin) {
                // This can either be an objet User, an array or simply the user name
                $username = '';
                if(is_object($admin)) { 
                    $username = $admin->username;
                } else if(is_array($admin)) {
                    $username = $admin['username'];
                } else {
                    $username = $admin;
                }

                // find in db
                $user = $userRepo->findOneByUsername($username);
                if($user !== null){
                    $game->addAdministrator($user);
                }
            }
        }

        // Handle authorized users that are specifie by names
        if(array_key_exists('authorizedPlayers', $data)){
            $game->getAuthorizedPlayers()->clear();
            foreach ($data['authorizedPlayers'] as $auth) {
                // This can either be an objet User or simply the user name
                $username = '';
                if(is_object($auth)) { 
                    $username = $auth->username;
                } else if(is_array($auth)) {
                    $username = $auth['username'];
                } else {
                    $username = $auth;
                }

                // find in db
                $user = $userRepo->findOneByUsername($username);
                if($user !== null){
                    $game->addAuthorizedPlayer($user);
                }
            }
        }


        // Make sure admins are always authorized
        foreach ($game->getAdministrators() as $admin) {
            if(!$game->getAuthorizedPlayers()->contains($admin)) {
                $game->addAuthorizedPlayer($admin);
            }
        }


        $em->persist($game);
        $em->flush();

        $gameUrl = $this->generateUrl('api_games_show', array('id' => $game->getId()));
        $response = $this->createApiResponse($game, 200);
        $response->headers->set('Location', $gameUrl);

        return $response;
    }

    /**
     * Deletes a Game entity.
     *
     * @Route("/{id}", name="api_games_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Game $game) {

        // Make sure current user is admin
        $currentUser = $this->getUser();
        if(!$game->getAdministrators()->contains($currentUser)) {
            return $this->createApiResponse(
                array(
                    'message' => 'Unauthorized Access',
                    'admins' => $game->getAdministrators()
                ), 
                401
            );
        }

        $game->setDeleted(true);
        $em = $this->getDoctrine()->getManager();
        $em->persist($game);
        $em->flush();

        return $this->createApiResponse([], 200);
    }
}
