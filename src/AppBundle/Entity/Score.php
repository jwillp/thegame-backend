<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation as Serializer;

/**
 * Score
 *
 * @ORM\Table(name="score")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ScoreRepository")
 */
class Score
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     * 
     * @ORM\Column(name="nbTimes", type="integer")
     */
    private $nbTimes;

    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
    * @ORM\JoinColumn(nullable=false)
    */
    private $user;

    /**
    * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Challenge", inversedBy="scores")
    * @ORM\JoinColumn(nullable=false)
    *
    * // Exlude as scores will always be provided as child object of challenge
    * @Serializer\Exclude()
    */
    private $challenge;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Increments by 1 the number of times for this score
     */
    public function increment() {
        $this->setNbTimes($this->getNbTimes() + 1);
        return $this;
    }

    /**
     * Decrements by 1 the number of times for this score
     */
    public function decrement() {
        $this->setNbTimes($this->getNbTimes() - 1);
        return $this;
    }

    /**
     * Returns the number of points
     */
    public function getNbPoints() {
        return $this->getChallenge()->getNbPoints() * $this->getNbTimes();
    }

    /**
     * Set nbTimes
     *
     * @param integer $nbTimes
     * @return Score
     */
    public function setNbTimes($nbTimes) {
        $this->nbTimes = ($nbTimes >= 0) ? $nbTimes : 0;
        return $this;
    }

    /**
     * Returns the number of time the challenge has been completed
     * for the current score
     *
     * @return integer
     */
    public function getNbTimes() {
        return $this->nbTimes;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     * @return Score
     */
    public function setUser(\AppBundle\Entity\User $user) {
        $this->user = $user;
        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Set challenge
     *
     * @param \AppBundle\Entity\Challenge $challenge
     * @return Score
     */
    public function setChallenge(\AppBundle\Entity\Challenge $challenge) {
        $this->challenge = $challenge;
        return $this;
    }

    /**
     * Get challenge
     *
     * @return \AppBundle\Entity\Challenge
     */
    public function getChallenge() {
        return $this->challenge;
    }
}
