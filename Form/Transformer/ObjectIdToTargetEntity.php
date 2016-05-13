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
        if (null === $value || empty($value)) {
            return json_encode(array());
        }

        if (is_object($this->acl) && method_exists($this->acl, 'getType')) {
            $entity = $this->entityMgr->getRepository($this->acl->getType()->getClassMatch())->find($value);
            if (!$entity) {
                return json_encode(array());
            }
        } else {
            throw new TransformationFailedException("Unable to transform $value to any acl role type");
        }

        return json_encode(array(array('id'=>$value,'name'=>$entity->__toString())));
    }

    /**
     * @inheritDoc
     *
     * Because this transformer is used strictly for the ACL object_id form field
     * We know that the object_id is a field that is a lose PK to other tables.
     * As such no reverse transformation is required since the autocomplete form field
     * is handled by JS and that submits only the ID of the {'id': X, 'name': 'name'} that is
     * in the input field.
     */
    public function reverseTransform($value)
    {
        return $value;
    }
}
