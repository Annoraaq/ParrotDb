<?php
namespace ParrotDb\ObjectModel;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-08-18 at 17:54:28.
 */
class PObjectIdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PObjectId
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new PObjectId(3);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers ParrotDb\ObjectModel\PObjectId::equals
     * @todo   Implement testEquals().
     */
    public function testEquals()
    {
       $this->assertEquals($this->object->getId(), 3);
       $this->assertEquals($this->object, new PObjectId(3));
       $this->assertFalse($this->object->getId() == 4);
    }
}
