<?php
/**
 * Created by PhpStorm.
 * User: gnat
 * Date: 31/03/16
 * Time: 3:28 PM
 */

namespace NS\SecurityBundle\Form\Types;

use Doctrine\Common\Persistence\ObjectManager;
use NS\SecurityBundle\Form\Transformer\ObjectIdToTargetEntity;
use NS\AceBundle\Form\AutocompleterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class ACLAutoCompleterType extends AbstractType
{
    /**
     * @var ObjectIdToTargetEntity
     */
    private $transformer;

    /**
     * ACLAutoCompleterType constructor.
     * @param $entityMgr
     */
    public function __construct(ObjectManager $entityMgr)
    {
        $this->transformer = new ObjectIdToTargetEntity($entityMgr);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'preSetData'))
            ->addModelTransformer($this->transformer);
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $parentData = $event->getForm()->getParent()->getData();

        if ($parentData) {
            $this->transformer->setAcl($parentData);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return AutocompleterType::class;
    }
}
