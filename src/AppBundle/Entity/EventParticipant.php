<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventParticipant
 *
 * @ORM\Table(name="event_participant")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EventParticipantRepository")
 */
class EventParticipant
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
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="objectId", type="integer")
     */
    private $objectId;

    /**
     * @var object
     * Represents object. (Not stored in DB)
     */
    private $object;

    function __construct($type, $objectId) {
        $this->setType($type);
        $this->setObjectId($objectId);
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return EventParticipant
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set objectId
     *
     * @param integer $objectId
     * @return EventParticipant
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * Get objectId
     *
     * @return integer 
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Set objectId
     * 
     * @param object $objectId
     * @return EventParticipant 
     */
    public function setObject($object){
        $this->object = $object;
        
        return $this;
    }

    /**
     * Get objectId
     *
     * @return object 
     */
    public function getObject(){
        return $this->object = $object;
    }
}
