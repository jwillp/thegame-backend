<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class WebController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request) {

        if ($this->isUserLoggedIn()) {
          return $this->redirect($this->generateUrl('newsfeed'));
        }

        return $this->render('default/index.html.twig');
    }

    /**
     * @Route("/newsfeed", name="newsfeed")
     */
    public function newsFeedAction() {
        if (!$this->isUserLoggedIn()) {
          return $this->redirect($this->generateUrl('homepage'));
        }
        return $this->render('default/newsfeed.html.twig');
    }

    /**
     * @Route("/challenges", name="challenge_list")
     */
    public function challengeListAction(Request $request) {
        if (!$this->isUserLoggedIn()) {
          return $this->redirect($this->generateUrl('homepage'));
        }
        return $this->render('default/challenge_list.html.twig');
    }

    /**
     * @Route("/leaderboard", name="leaderboard")
     */
    public function leaderboardAction(Request $request) {
        if (!$this->isUserLoggedIn()) {
          return $this->redirect($this->generateUrl('homepage'));
        }
        return $this->render('default/leaderboard.html.twig');
    }

    /**
     * @Route("/challenge/{challengeId}", name="view_challenge")
     */
    public function viewChallengeAction($challengeId) {
        if (!$this->isUserLoggedIn()) {
          return $this->redirect($this->generateUrl('homepage'));
        }
        
        $repo = $this->getDoctrine()
                     ->getManager()
                     ->getRepository('AppBundle:Challenge');
        $challenge = $repo->find($challengeId);

        // TODO Test if challenge exists for ID.

        $params = array(
            'challenge' => $challenge,
        );
        return $this->render('default/challenge_details.html.twig', $params);
    }

    /**
     * Returns wether or not the current user is logged in or not.
     * (authenticated)
     */
    private function isUserLoggedIn() {
        return $this->get('security.context')->isGranted('ROLE_USER');
    }


}


