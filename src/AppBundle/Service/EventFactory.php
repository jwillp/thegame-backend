<?php 

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;

use AppBundle\Entity\Event;
use AppBundle\Entity\EventParticipant;
use AppBundle\Entity\User;
use AppBundle\Entity\Challenge;
use AppBundle\Entity\Game;

/**
* Event Service
* This class is responsible for creating Event objects
* that will act as notifications on users' news feeds.
*
*/
class EventFactory
{
    // EntityManager
    private $em;

    function __construct(EntityManager $entityManager) {
        $this->em = $entityManager;
    }

    public function createNewChallengeEvent(User $creator, Challenge $challenge) {
        return $this->createEvent(
            $creator, 
            'USER' ,
            "{agent} created challenge {target}",
            $challenge,
            'CHALLENGE',
            'USER_CREATED_CHALLENGE_EVENT',
            $challenge->getGame()
        );
    }

    public function createChallengePointsUpdatedEvent(User $updater, Challenge $challenge) {
        return $this->createEvent(
            $updater, 
            'USER' ,
            "{agent} changed number of points of challenge {target}",
            $challenge,
            'CHALLENGE',
            'USER_UPDATED_CHALLENGE_POINTS_EVENT',
            $challenge->getGame()
        );
    }

    public function createUserCompletedChallengeEvent(User $updater, Challenge $challenge) {
        return $this->createEvent(
            $updater, 
            'USER' ,
            "{agent} completed challenge {target}",
            $challenge,
            'CHALLENGE',
            'USER_COMPLETED_CHALLENGE_EVENT',
            $challenge->getGame()
        );
    }

    public function createUserCanceledChallengeEvent(User $updater, Challenge $challenge) {
        return $this->createEvent(
            $updater, 
            'USER' ,
            "{agent} canceled a score for challenge {target}",
            $challenge,
            'CHALLENGE',
            'USER_CANCELED_CHALLENGE_EVENT',
            $challenge->getGame()
        );
    }

    public function createNewGameEvent(User $creator, Game $game) {
        return $this->createEvent(
            $creator, 
            'USER' ,
            "{agent} created game {target}",
            $game,
            'GAME',
            'USER_CREATED_GAME_EVENT',
            $game
        );
    }

    /**
     * Creates a generic event using provided information
     */
    private function createEvent($agentObject, $agentType,
                                 $action, 
                                 $targetObject, $targetType, 
                                 $iid, $game = null) {

        // Find participant entries for target and agent
        $agent = $this->getParticipant($agentObject, $agentType);
        $target = $this->getParticipant($targetObject, $targetType);

        $event = new Event();
        $event->setDate(new \DateTime());
        $event->setAgent($agent);
        $event->setTarget($target);
        $event->setAction($action);
        $event->setIid($iid);
        $event->setGame($game);

        return $event;
    }

    /** 
     * Finds a participant for specified object. 
     * If none find, create one
     */
    private function getParticipant($object, $objectType) {
        $participant = $this->em->getRepository('AppBundle:EventParticipant')
            ->findOneByObjectId($object->getId());
        if($participant === null) {
            $participant = new EventParticipant($objectType, $object->getId());
        }

        return $participant;
    }
}