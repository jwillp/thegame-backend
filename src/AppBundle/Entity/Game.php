<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Game
 * Represents a game
 *
 * @ORM\Table(name="game")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GameRepository")
 */
class Game
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
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank(message="Please enter a title")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startDate", type="datetime")
     * @Assert\NotBlank(message="Please enter a start date")
     */
    private $startDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="endDate", type="datetime")
     * @Assert\NotBlank(message="Please enter an end date")
     */
    private $endDate;

    /**
     * @var bool
     *
     * @ORM\Column(name="deleted", type="boolean")
     */
    protected $deleted;


    /**
     * @var string 
     *
     * @ORM\Column(name="visibility", type="string", length=255)
     */
    protected $visibility;
    const VISIBILITY_PUBLIC = "VISIBILITY_PUBLIC";
    const VISIBILITY_PRIVATE = "VISIBILITY_PRIVATE";

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinTable(name="game_administrator")
     */
    protected $administrators;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinTable(name="game_authorized_player")
     */
    protected $authorizedPlayers;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     */
    protected $createdBy;
    

    function __construct() {
        $this->startDate = new \DateTime();
        $this->endDate = new \DateTime();
        $this->setDeleted(false);

        $this->visibility = self::VISIBILITY_PUBLIC;

        $this->administrators = new \Doctrine\Common\Collections\ArrayCollection();
        $this->authorizedPlayers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Game
     */
    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Game
     */
    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Set startDate
     *
     * @param \DateTime $startDate
     * @return Game
     */
    public function setStartDate($startDate) {
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime 
     */
    public function getStartDate() {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param string $endDate
     * @return Game
     */
    public function setEndDate($endDate) {
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * Get endDate
     *
     * @return string 
     */
    public function getEndDate() {
        return $this->endDate;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     * @return Game
     */
    public function setDeleted($deleted) {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * Get deleted
     *
     * @return boolean 
     */
    public function isDeleted() {
        return $this->deleted;
    }

    /**
     * Get deleted
     *
     * @return boolean
     */
    public function getDeleted() {
        return $this->deleted;
    }

    /**
     * Set visibility
     *
     * @param string $visibility
     *
     * @return Game
     */
    public function setVisibility($visibility) {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * Get visibility
     *
     * @return string
     */
    public function getVisibility() {
        return $this->visibility;
    }

    /**
     * Add administrator
     *
     * @param \AppBundle\Entity\User $administrator
     *
     * @return Game
     */
    public function addAdministrator(\AppBundle\Entity\User $administrator) {
        $this->administrators[] = $administrator;

        return $this;
    }

    /**
     * Remove administrator
     *
     * @param \AppBundle\Entity\User $administrator
     */
    public function removeAdministrator(\AppBundle\Entity\User $administrator) {
        $this->administrators->removeElement($administrator);
    }

    /**
     * Get administrators
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdministrators() {
        return $this->administrators;
    }

    /**
     * Add authorizedPlayer
     *
     * @param \AppBundle\Entity\User $authorizedPlayer
     *
     * @return Game
     */
    public function addAuthorizedPlayer(\AppBundle\Entity\User $authorizedPlayer) {
        $this->authorizedPlayers[] = $authorizedPlayer;

        return $this;
    }

    /**
     * Remove authorizedPlayer
     *
     * @param \AppBundle\Entity\User $authorizedPlayer
     */
    public function removeAuthorizedPlayer(\AppBundle\Entity\User $authorizedPlayer) {
        $this->authorizedPlayers->removeElement($authorizedPlayer);
    }

    /**
     * Get authorizedPlayers
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAuthorizedPlayers() {
        return $this->authorizedPlayers;
    }

    /**
     * Set createdBy
     *
     * @param \AppBundle\Entity\User $createdBy
     *
     * @return Game
     */
    public function setCreatedBy(\AppBundle\Entity\User $createdBy = null) {
        $this->createdBy = $createdBy;

        $this->addAdministrator($createdBy);
        $this->addauthorizedPlayer($createdBy);

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \AppBundle\Entity\User
     */
    public function getCreatedBy() {
        return $this->createdBy;
    }

    /**
     * Indicates if the game is visibile to a certain user
     *
     * @return boolean 
     */
    public function isVisibleTo(\AppBundle\Entity\User $user) {
        $visibility = $this->getVisibility();
        if($visibility == self::VISIBILITY_PUBLIC) {
            return true;
        }

        if($visibility == self::VISIBILITY_PRIVATE) {
            return $this->getAuthorizedPlayers()->contains($user)
            || $this->getAdministrators()->contains($user);
        }

        // Should not happen
        return false;
    }

    /**
     * isStarted
     *
     * Indicates if the game is started or not
     * @Serializer\VirtualProperty()
     */
    public function isStarted() {
        return new \DateTime() >= $this->getStartDate();
    }
    /**
     * isFinished
     *
     * Indicates if the game is finished or not
     * @Serializer\VirtualProperty()
     */
    public function isFinished() {
        return new \DateTime() >= $this->getEndDate();
    }
    /**
     * isInProgress
     *
     * Indicates if the game is in progress or not
     * @Serializer\VirtualProperty()
     */
    public function isInProgress() {
        return $this->isStarted() && !$this->isFinished();
    }
}
