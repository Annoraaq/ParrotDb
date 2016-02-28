<?php
namespace ParrotDb\ObjectModel;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-08-18 at 17:50:22.
 */
class PObjectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PObject
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new PObject(new PObjectId(3));
        $this->object->addAttribute("attr1", "val1");
        $this->object->addAttribute("attr2", "val2");
        $this->object->setClass(new PClass("Testclass"));

    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }
    
    /**
     * @covers ParrotDb\ObjectModel\PObject::getAttributes
     * @todo   Implement testGetAttributes().
     */
    public function testHasAttribute()
    {
        $this->assertTrue($this->object->hasAttribute("attr1"));
        $this->assertTrue($this->object->hasAttribute("attr2"));
    }

    /**
     * @covers ParrotDb\ObjectModel\PObject::getAttributes
     * @todo   Implement testGetAttributes().
     */
    public function testGetAttributes()
    {
        $this->assertEquals($this->object->getAttributes()["attr1"]->getValue(), "val1");
        $this->assertEquals($this->object->getAttributes()["attr2"]->getValue(), "val2");
    }

    /**
     * @covers ParrotDb\ObjectModel\PObject::getObjectId
     * @todo   Implement testGetObjectId().
     */
    public function testGetObjectId()
    {
        $this->assertEquals($this->object->getObjectId(), new PObjectId(3));
    }


    /**
     * @covers ParrotDb\ObjectModel\PObject::addAttribute
     * @todo   Implement testAddAttribute().
     */
    public function testAddAttribute()
    {
        $this->assertFalse($this->object->hasAttribute("attr3"));
        $this->object->addAttribute("attr3", "val3");
        $this->assertTrue($this->object->hasAttribute("attr3"));
    }


    /**
     * @covers ParrotDb\ObjectModel\PObject::isIdentical
     * @todo   Implement testIsIdentical().
     */
    public function testIsIdentical()
    {
        $this->assertTrue($this->object->isIdentical($this->object));
        $this->assertFalse($this->object->isIdentical($this));
    }

    /**
     * @covers ParrotDb\ObjectModel\PObject::isEqual
     * @todo   Implement testIsEqual().
     */
    public function testIsEqual()
    {
        $obj = new PObject(new PObjectId(3));
        $obj->addAttribute("attr1", "val1");
        $obj->addAttribute("attr2", "val2");
        $obj->setClass(new PClass("Testclass"));
        
        $obj2 = new PObject(new PObjectId(77));
        $obj2->addAttribute("attr1", "val3");
        $obj2->addAttribute("attr2", "val2");
        $obj2->setClass(new PClass("Testclass2"));
        
        $obj3 = new PObject(new PObjectId(3));
        $obj3->addAttribute("attr1", "val1");
        $obj3->addAttribute("attr2", "val2");
        $obj3->addAttribute("attr3", "val3");
        $obj3->setClass(new PClass("Testclass"));
        
        $this->assertTrue($this->object->equals($obj));
        
        $this->assertFalse($this->object->equals($obj2));
        
        $this->assertFalse($this->object->equals($obj3));
        
        $this->assertFalse($this->object->equals($this));
    }
}
