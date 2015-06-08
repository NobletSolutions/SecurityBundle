<?php

namespace NS\SecurityBundle\Entity;

use \Doctrine\ORM\Mapping as ORM;

/**
 * NS\SecurityBundle\Entity
 * @ORM\MappedSuperclass
 */
class BaseACL implements \Serializable
{
    /**
     * @var integer $id
     * @ORM\Column(name="id",type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer $user_id
     * 
     * @ORM\Column(name="user_id",type="integer")
     */
    private $user_id;
    
    /**
     * @var integer $object_id
     * 
     * @ORM\Column(name="object_id",type="integer")
     */    
    protected $object_id;
    
    /**
     * @var integer $type
     * 
     * @ORM\Column(name="type",type="integer")
     */    
    protected $type;

    /**
     * @var DateTime $valid_from
     * @ORM\Column(name="valid_from",type="datetime",nullable=true)
     */
    protected $valid_from;
    
    /**
     * @var DateTime $valid_to
     * @ORM\Column(name="valid_to",type="datetime",nullable=true)
     */    
    protected $valid_to;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return var_export($this, true);
    }

    /**
     * Set user_id
     *
     * @param integer $userId
     * @return UserDivision
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;
    
        return $this;
    }

    /**
     * Get user_id
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set object_id
     *
     * @param integer $objectId
     * @return ACL
     */
    public function setObjectId($objectId)
    {
        $this->object_id = $objectId;
    
        return $this;
    }

    /**
     * Get object_id
     *
     * @return integer 
     */
    public function getObjectId()
    {
        return $this->object_id;
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return ACL
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return integer 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set valid_from
     *
     * @param \DateTime $validFrom
     * @return ACL
     */
    public function setValidFrom(\DateTime $validFrom = null)
    {
        $this->valid_from = $validFrom;
    
        return $this;
    }

    /**
     * Get valid_from
     *
     * @return \DateTime
     */
    public function getValidFrom()
    {
        return $this->valid_from;
    }

    /**
     * Set valid_to
     *
     * @param \DateTime $validTo
     * @return ACL
     */
    public function setValidTo(\DateTime $validTo = null)
    {
        $this->valid_to = $validTo;
    
        return $this;
    }

    /**
     * Get valid_to
     *
     * @return \DateTime
     */
    public function getValidTo()
    {
        return $this->valid_to;
    }
    
    /**
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->user_id,
            $this->object_id,
            $this->type,
            $this->valid_from,
            $this->valid_to,
        ));
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->user_id,
            $this->object_id,
            $this->type,
            $this->valid_from,
            $this->valid_to
            ) = unserialize($serialized);
    }
}
