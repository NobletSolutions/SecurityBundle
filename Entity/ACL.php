<?php

namespace NS\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NS\SecurityBundle\Entity
 *
 * @ORM\Table(name="acls")
 * @ORM\Entity
 */
class ACL
{
    /**
     * @var integer $user_id
     * 
     * @ORM\Column(name="user_id",type="integer")
     * @ORM\Id
     */
    protected $user_id;
    
    /**
     * @var integer $object_id
     * 
     * @ORM\Column(name="object_id",type="integer")
     * @ORM\Id
     */    
    protected $object_id;
    
    /**
     * @var integer $type
     * 
     * @ORM\Column(name="type",type="integer")
     * @ORM\Id
     */    
    protected $type;

    /**
     * @var NobletSolutions\NedcoBundle\Entity\User $user
     * 
     * @ORM\ManyToOne(targetEntity="NobletSolutions\NedcoBundle\Entity\User", inversedBy="acls")
     */
    protected $user;
    
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
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
     * Set user
     *
     * @param \NobletSolutions\NedcoBundle\Entity\User $user
     * @return ACL
     */
    public function setUser(\NobletSolutions\NedcoBundle\Entity\User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \NobletSolutions\NedcoBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}