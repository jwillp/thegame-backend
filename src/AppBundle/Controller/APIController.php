<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use AppBundle\Entity\Challenge;
use AppBundle\Entity\Score;
use AppBundle\Entity\NPUserCreatedChallenge;
use AppBundle\Entity\NPUserCompletedChallenge;
use AppBundle\Entity\NPUserCanceledChallengeScore;
use AppBundle\Entity\NPUserTookLead;

class APIController extends Controller
{

    /**
     * Main Entry point for getting infos about challenges
     * @Route("/api/v1/challenge", name="api_challenge")
     */
    public function challengeAction(Request $request) {
        $action = $request->query->get('action');
        $response = "";
        switch ($action) {
            case 'get':
                $response = $this->getChallenge($request);
                break;
            case 'create':
                $response = $this->createChallenge($request);
                break;
            case 'delete':
                $response = $this->deleteChallenge($request);
                break;
            case 'complete':
            case 'cancel':
                $response = $this->updateScore($request);
                break;
            default:
                $response = $this->errorReportResponse(
                    '400',
                    'Bad Request',
                    'Invalid challenge action: "' . $action .
                    '" must be one in get, create, delete complete, cancel'
                );
                break;
        }
        return new JsonResponse($response);
    }

    /**
    * Main logic for the get action of api_challenge
    */
    public function getChallenge(Request $request) {
        if($request->query->get('challengeId') == 'all')
            return $this->getAllChallenges();
        else
            return $this->getChallengeById($request->query->get('challengeId'));
    }

    /**
     * Returns a challenge with challengeId
     * request: { challengeId: id, action: get }
     * response: {status: "OK", challenge : { ... }}
     * response: {status: "error", errorMsg : "Bad Request", errorReport: "Challenge with Id does not exit"} when error
     */
    public function getChallengeById($challengeId) {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizer = new ObjectNormalizer();
        $serializer = new Serializer(array($normalizer), $encoders);

        $repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Challenge');
        $challenge = $repo->find($challengeId);

                // ignore score.challenge to avoir recursion error
        $normalizer->setIgnoredAttributes(array('challenge'));

        $response = array(
            'status' => 'OK',
            'challenge' => $serializer->normalize($challenge, 'json'),
        );
        return $response;
    }

    /**
     * Returns all challenges
     * request:  {challengeId:"all", action: get }
     * response: {status: "OK", challenges : [{}, {}, {}]}
     * response: {status: "error", errorMsg : "Bad Request", errorReport: "Unexpected Error"} // when error
     */
    public function getAllChallenges() {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizer = new ObjectNormalizer();
        $serializer = new Serializer(array($normalizer), $encoders);

        $repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Challenge');
        $challenge = $repo->findAll();

        // ignore score.challenge to avoir recursion error
        $normalizer->setIgnoredAttributes(array('challenge'));

        $response = array(
            'status' => 'OK',
            'challenges' => $serializer->normalize($challenge, 'json'),
        );
        return $response;
    }

    /**
     * Creates a new challenge
     * request: {action: create, data: { title: "title", desc: "desc", nbPoints: 80} }
     * response: {status: "OK"} // when success
     * response: {status: "400", errorMsg : "Bad Request", errorReport{field: "The value must not be null"}} when error
     */
    public function createChallenge(Request $request) {
        if (!$this->isUserLoggedIn()) {
            return $this->errorReportResponse('400', 'Invalid permission', 'You must be logged in, in order to create challenges');
        }

        $data = $request->query->get('data');
        if ($data['title'] == "") {
            return $this->errorReportResponse('400', 'Invalid Value', 'title must not be null');
        }
        if ($data['nbPoints'] == "") {
            return $this->errorReportResponse('400', 'Invalid Value', 'nbPoints must not be null');
        }

        if (!is_numeric($data['nbPoints'])) {
            return $this->errorReportResponse('400', 'Invalid Value', 'nbPoints must be an integer number');
        }

        // Create challenge
        $challenge = new Challenge();
        $challenge->setTitle($data['title']);
        $challenge->setDescription($data['description']);
        $nbPoints = (int)$data['nbPoints'];
        $challenge->setNbPoints($nbPoints);
        $em = $this->getDoctrine()->getManager();
        $em->persist($challenge);

        // Create NewsPost
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $newsPost = new NPUserCreatedChallenge();
        $newsPost->setUser($user);
        $newsPost->setChallenge($challenge);
        $em->persist($newsPost);

        $em->flush();
        return $this->successResponse();
    }

    /**
     * Deletes a challenge with challengeId
     * request: { challengeId: id, action: delete }
     * response: {status: "OK"} // when success
     * response: {status: "error", errorMsg : "Bad Request", errorReport: "Challenge with Id cannot be deleted:  challenge does not exit"} when error
     */
    public function deleteChallenge(Request $request) {
        if (!$this->isUserLoggedIn()) {
            return $this->errorReportResponse('400', 'Invalid permission', 'You must be logged in, in order to create challenges');
        }

        $challengeId = $request->query->get('challengeId');
        if ($challengeId === null) {
            return $this->errorReportResponse('400', 'Invalid Value', 'challengeId must not be null');
        }
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository('AppBundle:Challenge');
        $challenge = $repo->find($challengeId);

        $challenge->setIsDeleted(true);

        // Create notification
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $notif = Notification::withType(Notification::USER_DELETED_CHALLENGE);
        $notif->setUser($user);
        $notif->setChallenge($challenge);
        $em->persist($notif);



        return $this->successResponse();
    }

    /**
     * Edit a challenge with challengeId
     * request: { challengeId: id, action: edit, data: { title: "title", desc: "desc", nbPoints: 80} }
     * response: {status: "OK"} // when success
     * response: {status: "400", errorMsg : "Bad Request", errorReport{field: "The value must not be null"}} when error
     */
    public function editChallenge(Request $request) {
        $challengeId = $request->query->get('challengeId');
        if ($challengeId === null) {
            return $this->errorReportResponse('400', 'Invalid Value', 'challengeId must not be null');
        }
        $data = $request->query->get('data');
        if ($data['title'] === null) {
            return $this->errorReportResponse('400', 'Invalid Value', 'title must not be null');
        }
        if ($data['nbPoints'] === null) {
            return $this->errorReportResponse('400', 'Invalid Value', 'nbPoints must not be null');
        }
        if (!is_int($data['nbPoints'])) {
            return $this->errorReportResponse('400', 'Invalid Value', 'nbPoints must be an integer number');
        }
        if (!$this->isUserLoggedIn()) {
            return $this->errorReportResponse('400', 'Invalid permission', 'you do not have to permission to access this page');
        }

        $repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Challenge');
        $challenge = $repo->find($challengeId);


        $newTitle = $data['title'];
        $newDesc = $data['description'];
        $newNbPoints = $data['nbPoints'];

        if($challenge->getTitle() != $newTitle){
            $challenge->setTitle($newTitle);
        }
        if($challenge->getDescription() != $newDesc){
            $oldDesc = $challenge->getDescription();
            if($oldDest == "" || $oldDest == null ){
                // Create newspost user added a description
                // to challenge
            }
            $challenge->setDescription($newDesc);
        }

        if($challenge->getNbPoints() != $newNbPoints){
            $challenge->setNbPoints($newNbPoints);
            // Create newspost user updated challenge points
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($challenge);
        $em->flush();
    }

    /**
     * Completes a challenge with challengeId (creates a new Scorem or update)
     * request: {action: complete|cancel,challengeId: id }
     * response: {status: "OK"} // when success
     * response: {status: "400", errorMsg : "Bad Request", errorReport: "Challenge with Id does not exist"} when error
     */
    public function updateScore(Request $request) {
        $challengeId = $request->query->get('challengeId');
        if ($challengeId === null) {
            return $this->errorReportResponse('400', 'Invalid Value', 'challengeId must not be null');
        }
        if (!$this->isUserLoggedIn()) {
            return $this->errorReportResponse('400', 'Invalid permission', 'you do not have to permission to access this page');
        }
        // Get User
        // Check if user with userId and current user are the same.
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $em = $this->getDoctrine()->getManager();

        // Get Challenge
        $challengeRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Challenge');
        $challenge = $challengeRepo->find($challengeId);


        // If score does not already exists for current user and challengeId create score,
        $scoreRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Score');
        $score = $scoreRepo->findOneBy(array('challenge' => $challenge, 'user' => $user));
        if($score === null){
            $score = new Score();
            $score->setUser($user);
            $score->setChallenge($challenge);
        }

        // increment or decrement nbTimes
        $action = $request->query->get('action');
        if ($action == 'complete'){
            $oldNbTimes = $score->getNbTimes();
            $score->increment();
            // create NewsPost
            $newsPost = new NPUserCompletedChallenge();
            $newsPost->setUser($user);
            $newsPost->setChallenge($challenge);
            $newsPost->setNbTimesBefore($oldNbTimes);
            $newsPost->setNbTimesAfter($score->getNbTimes());
            $em->persist($newsPost);

        }

        if ($action == 'cancel'){
            $oldNbTimes = $score->getNbTimes();
            $score->decrement();
            // create NewsPost
            $newsPost = new NPUserCanceledChallengeScore();
            $newsPost->setUser($user);
            $newsPost->setChallenge($challenge);
            $newsPost->setNbTimesBefore($oldNbTimes);
            $newsPost->setNbTimesAfter($score->getNbTimes());
            $em->persist($newsPost);
        }


        // Find leader and create NewsPost if current leader has less points
        $leaderScore = $this->getLeaderScore();
        if ($leaderScore['user'] != $user) {
            // If current leader and current user are not the same,
            // maybe current user has taken the lead
            $currentUserScore = $this->getTotalUserScore($user);
            $currentUserNbPoints = $currentUserScore['nbPoints'] + $score->getNbPoints();
            if ($currentUserNbPoints >= $leaderScore['nbPoints']) {
                $newsPost = new NPUserTookLead();
                $newsPost->setFormerLeader($leaderScore['user']);
                $newsPost->setUser($user);
                $newsPost->setNbPoints($currentUserNbPoints);
                $em->persist($newsPost);
            }


        }


        $em->persist($score);
        $em->persist($challenge);
        $em->flush();
        return $this->successResponse();
    }

   /**
    * Returns the leaderboard
    * request: { range: all } (range: 10 means top ten, range: 1 means board leader, range: all, means all)
    * reponse: {"status": "OK", leaderboard: [user:{{user1}, nbPoints:0}, {user: {user2}, nbPoints:0}, {user: {user3}, nbPoints:0} ]}
    * response: {status: "400", errorMsg : "Bad Request", errorReport: "Unexpected error"} when error
    * @Route("/api/v1/leaderboard", name="api_leaderboard")
    */
   public function leaderboardAction() {


        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizer = new ObjectNormalizer();
        $serializer = new Serializer(array($normalizer), $encoders);

        $ranks = $this->getRankedUsers();

        $response = array(
            'status' => 'OK',
            'ranks' => $serializer->normalize($ranks, 'json'),
        );
        return new JsonResponse($response);
   }

   /**
    * Returns the notifications
    * request: { range: "all"} (range: 10 means the 10 most recent)
    * reponse: {"status": "OK", notifications: [ {notification1}, {notification2}, {notification3} ]}
    * response: {status: "400", errorMsg : "Bad Request", errorReport: "Unexpected error"} when error
    * @Route("/api/v1/notifications", name="api_notifications")
    */
   public function notificationsAction() {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizer = new ObjectNormalizer();
        $serializer = new Serializer(array($normalizer), $encoders);

        $repo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Notification');
        $notifications = array_reverse($repo->findAll());

            // ignore challenge.scores to avoir recursion error
        $normalizer->setIgnoredAttributes(array('scores'));

        $response = array(
            'status' => 'OK',
            'notifications' => $serializer->normalize($notifications, 'json'),
        );
        return new JsonResponse($response);
   }

    /**
    * Returns the user with the msot points
    */
    private function getLeaderScore(){
        return $this->getRankedUsers()[0];
    }

    /**
    * Returns the total number of points for a user object
    */
    private function getTotalUserScore($user, $scoreRepo = null){
        if($scoreRepo == null)
            $scoreRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Score');
        $totalPoints = 0;
            $scores = $scoreRepo->findBy(array('user' => $user));
            foreach ($scores as $score) {
                //die(var_dump($score));
                // make total
                $totalPoints += $score->getNbPoints();

            }
            // store in array (user, totalScore)
            $rank = array(
                'user' => $user,
                'nbPoints' => $totalPoints,
            );
        return $rank;
    }

    /**
     * Returns an array containing all the users ordered by total nb of points
     */
    private function getRankedUsers(){
        $scoreRepo = $this->getDoctrine()->getManager()->getRepository('AppBundle:Score');
        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();

        $ranks = array();

        // For each user
        foreach ($users as $user) {
            // get scores
            $totalPoints = 0;
            $scores = $scoreRepo->findBy(array('user' => $user));
            foreach ($scores as $score) {
                //die(var_dump($score));
                // make total
                $totalPoints += $score->getNbPoints();

            }
            // store in array (user, totalScore)
             $rank = array(
                'user' => $user,
                'nbPoints' => $totalPoints,
            );
            $ranks[] = $rank;
        }

        // Sort from lowest to highest
        usort($ranks, function($a, $b){
            return $a['nbPoints'] <= $b['nbPoints'];
        });
        return $ranks;
    }

    /**
     * Returns an error array response
     */
    private function errorReportResponse($errorStatus, $errorMsg, $errorReport){
        return array(
            'status' => $errorStatus,
            'errorMsg' => $errorMsg,
            'errorReport' => $errorReport,
        );
   }

    /**
     * Returns an success array response
     */
    private function successResponse(){
        return array('status' => 'OK');
    }

    /**
     * Returns wether or not the current user is logged in or not.
     * (authenticated)
     */
    private function isUserLoggedIn() {
        return $this->get('security.context')->isGranted('ROLE_USER');
    }
}