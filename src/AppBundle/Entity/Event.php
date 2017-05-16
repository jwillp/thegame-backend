<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Event
 *
 * @ORM\Table(name="event")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventRepository")
 */
class Event {
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetimetz")
     */
    private $date;

    /**
     * @var \AppBundle\Entity\EventParticipant
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EventParticipant", cascade={"persist"})
     * @ORM\JoinTable(name="event_agent_participant")
     */
    private $agent;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=255)
     */
    private $action;

    /**
     * @var \AppBundle\Entity\EventParticipant
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EventParticipant", cascade={"persist"})
     * @ORM\JoinTable(name="event_target_participant")
     */
    private $target;

    /**
     * @var string
     *
     * @ORM\Column(name="iid", type="string", length=255)
     */
    private $iid;

    /**
     * @var \AppBundle\Entity\Game
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Game")
     * @ORM\JoinColumn(nullable=true)
     */
    private $game;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Event
     */
    public function setDate($date) {
        $this->date = $date;
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * Set agent
     *
     * @param \AppBundle\Entity\EventParticipant $agent
     * @return Event
     */
    public function setAgent(\AppBundle\Entity\EventParticipant $agent) {
        $this->agent = $agent;
        return $this;
    }

    /**
     * Get agent
     *
     * @return \AppBundle\Entity\EventParticipant 
     */
    public function getAgent() {
        return $this->agent;
    }

    /**
     * Sets the action.
     * The action is a field that represents what happened.
     * E.g.: User "ABC" CREATED challenge "XYZ".
     * In this case CREATED would be the action.
     * For better display purpose this string can be formatted as follow:
     * {agent} create {target}
     * {target} was created by {agent}
     *
     * @param string $action
     * @return Event
     */
    public function setAction($action) {
        $this->action = $action;
        return $this;
    }

    /**
     * Get action
     *
     * @return string 
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Set target
     *
     * @param \AppBundle\Entity\EventParticipant $target
     * @return Event
     */
    public function setTarget(\AppBundle\Entity\EventParticipant $target) {
        $this->target = $target;
        return $this;
    }

    /**
     * Get target
     *
     * @return \AppBundle\Entity\EventParticipant 
     */
    public function getTarget() {
        return $this->target;
    }

    /**
     * Sets the internal Id representing the type of the event
     *
     * @param string $iid
     * @return Event
     */
    public function setIid($iid) {
        $this->iid = $iid;
        return $this;
    }

    /**
     * Get internal id
     *
     * @return string 
     */
    public function getIid() {
        return $this->iid;
    }

    /**
     * Set game
     *
     * @param \AppBundle\Entity\Game $game
     * @return Event
     */
    public function setGame(\AppBundle\Entity\Game $game = null) {
        $this->game = $game;
        return $this;
    }

    /**
     * Get game
     *
     * @return \AppBundle\Entity\Game 
     */
    public function getGame() {
        return $this->game;
    }
}
