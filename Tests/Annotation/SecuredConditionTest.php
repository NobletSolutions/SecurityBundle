<?php

namespace NS\SecurityBundle\Tests\Annotation;
use NS\SecurityBundle\Annotation\SecuredCondition;

/**
 * Created by PhpStorm.
 * User: gnat
 * Date: 2016-05-12
 * Time: 10:42 PM
 */
class SecuredConditionTest extends \PHPUnit_Framework_TestCase
{
    public function testThroughIsArray()
    {
        $annotation = new SecuredCondition(array('through' => 'something', 'enabled' => false, 'roles' => array('ROLE_ONE')));
        $this->assertTrue(is_array($annotation->getThrough()));
        $this->assertEquals(array('something'),$annotation->getThrough());
    }

    /**
     * @dataProvider getIncompleteOptions
     */
    public function testMissingRequired(array $options)
    {
        $this->setExpectedException('Symfony\Component\Validator\Exception\MissingOptionsException');

        new SecuredCondition($options);
    }

    public function getIncompleteOptions()
    {
        return array(
            array(array('through' => 'something')),
            array(array('roles' => array('ROLE_ONE'))),
            array(array('roles' => array('ROLE_ONE'), 'relation' => 'relation')),
            array(array('roles' => array('ROLE_ONE'), 'class' => 'class')),
        );
    }
}
