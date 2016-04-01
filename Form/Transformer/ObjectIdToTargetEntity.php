<?php
/**
 * Created by PhpStorm.
 * User: gnat
 * Date: 31/03/16
 * Time: 3:32 PM
 */

namespace NS\SecurityBundle\Form\Transformer;

use Doctrine\Common\Persistence\ObjectManager;
use NS\SecurityBundle\Entity\BaseACL;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class ObjectIdToTargetEntity implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    private $entityMgr;

    /**
     * @var BaseACL
     */
    private $acl;

    /**
     * ObjectIdToTargetEntity constructor.
     * @param $entityMgr
     */
    public function __construct(ObjectManager $entityMgr)
    {
        $this->entityMgr = $entityMgr;
    }

    /**
     * @param mixed $acl
     */
    public function setAcl(BaseACL $acl)
    {
        $this->acl = $acl;
    }

    /**
     * @inheritDoc
     */
    public function transform($value)
    {
        if (null === $value) {
            return json_encode(array());
        }

        if (is_object($this->acl) && method_exists($this->acl, 'getType')) {
            $entity = $this->entityMgr->getRepository($this->acl->getType()->getClassMatch())->find($value);
        } else {
            throw new TransformationFailedException("Unable to transform $value to any acl role type");
        }

        return json_encode(array(array('id'=>$value,'name'=>$entity->__toString())));
    }

    /**
     * @inheritDoc
     */
    public function reverseTransform($value)
    {
        if ('' === $value || null === $value) {
            return null;
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $idsArray = json_decode($value, true);

        if (empty($idsArray)) {
            return null;
        } elseif (count($idsArray) > 1) {
            throw new \Exception('Too many ids');
        }

        return key($idsArray);
    }
}
