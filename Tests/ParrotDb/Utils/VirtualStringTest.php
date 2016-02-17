<?php
namespace ParrotDb\Utils;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-02-11 at 10:04:43.
 */
class VirtualStringTest extends \PHPUnit_Framework_TestCase
{
    protected $pm;
    
    protected $session;
    
    
    /**
     * @covers ParrotDb\Persistence\Xml\XmlSerializer::serialize
     */
    public function testVirtualStringGet() {
        
       $input = "abcdefghijklmnopqrstuvwxyz";
       
       $file = fopen("testfile.txt", "w");
       fwrite($file, $input);
       fclose($file);
       
       $virtualString = new VirtualString("testfile.txt", 1);
       $virtualString->open();
       
       $this->assertEquals('a',$virtualString->get(0));
       $this->assertEquals('z',$virtualString->get(25));
       $this->assertEquals('i',$virtualString->get(8));
    
       
       $this->setExpectedException(\ParrotDb\Core\PException::class);

       $virtualString->get(26);
       
       $virtualString->close();
       
      
    }
    
    /**
     * @covers ParrotDb\Persistence\Xml\XmlSerializer::serialize
     */
    public function testVirtualStringSubstr() {
        
       $input = "abcdefghijklmnopqrstuvwxyz";
       
       $file = fopen("testfile.txt", "w");
       fwrite($file, $input);
       fclose($file);
       
       $virtualString = new VirtualString("testfile.txt", 10);
       $virtualString->open();
       
       $this->assertEquals('abcdefg',$virtualString->substr(0,7));
       
       $this->assertEquals('efghijklmnopqrstuvwxyz',$virtualString->substr(4,26));
       
       $this->assertEquals('efghijklmnopqrstuvwxyz',$virtualString->substr(4,30));
       
       $virtualString->close();
    }
    
    /**
     * @covers ParrotDb\Persistence\Xml\XmlSerializer::serialize
     */
    public function testVirtualStringFind() {
        
       $input = "abcdefghijklmnopqrstuvwxyz";
       
       $file = fopen("testfile.txt", "w");
       fwrite($file, $input);
       fclose($file);
       
       $virtualString = new VirtualString("testfile.txt", 5);
       $virtualString->open();
       
       $this->assertEquals(0,$virtualString->findFirst('a'));
       
       $this->assertEquals(-1,$virtualString->findFirst('4'));
       
       $this->assertEquals(0,$virtualString->findFirst('ab'));
       
       $this->assertEquals(1,$virtualString->findFirst('bcd'));
       
       $this->assertEquals(-1,$virtualString->findFirst('abd'));
       
       $this->assertEquals(25,$virtualString->findFirst('z'));
       
       $virtualString->close();
       
       $input = "abcabdade";
       
       $file = fopen("testfile.txt", "w");
       fwrite($file, $input);
       fclose($file);
       
       // test offset
       $virtualString = new VirtualString("testfile.txt", 5);
       $virtualString->open();
       
       $this->assertEquals(3,$virtualString->findFirst('a',1));
       $this->assertEquals(6,$virtualString->findFirst('a',4));
       
       $virtualString->close();
    }
    
    /**
     * @covers ParrotDb\Persistence\Xml\XmlSerializer::serialize
     */
    public function testVirtualStringGetInterval() {
        
       $input = "abcdefghijklmnopqrstuvwxyz";
       
       $file = fopen("testfile.txt", "w");
       fwrite($file, $input);
       fclose($file);
       
       $virtualString = new VirtualString("testfile.txt", 5);
       $virtualString->open();
       
       $this->assertEquals('bc',$virtualString->getNextInterval(0, 'a', 'd'));
       
       $this->assertEquals('wx',$virtualString->getNextInterval(0, 'v', 'y'));
       
       $this->assertEquals('y',$virtualString->getNextInterval(0, 'x', 'z'));
       
       $virtualString->close();
    }
    
    /**
     * @covers ParrotDb\Persistence\Xml\XmlSerializer::serialize
     */
    public function testVirtualStringGetIntervalSame() {
        
       $input = "abcdeaghijklmnopqrstuvwxyz";
       
       $file = fopen("testfile.txt", "w");
       fwrite($file, $input);
       fclose($file);
       
       $virtualString = new VirtualString("testfile.txt", 5);
       $virtualString->open();
       
       $this->assertEquals('bcde',$virtualString->getNextInterval(0, 'a', 'a'));

       
       $virtualString->close();
    }
    
    /**
     * @covers ParrotDb\Persistence\Xml\XmlSerializer::serialize
     */
    public function testVirtualStringGetIntervalOffset() {
        
       $input = "axxxbayyyb";
       
       $file = fopen("testfile.txt", "w");
       fwrite($file, $input);
       fclose($file);
       
       $virtualString = new VirtualString("testfile.txt", 5);
       $virtualString->open();
       
       $this->assertEquals('xxx',$virtualString->getNextInterval(0, 'a', 'b'));
       $this->assertEquals('yyy',$virtualString->getNextInterval(1, 'a', 'b'));
       
       $virtualString->close();
    }
    
    /**
     * @covers ParrotDb\Persistence\Xml\XmlSerializer::serialize
     */
    public function testVirtualWriteStringGet() {
        
       $input = "abcdefghijklmnopqrstuvwxyz";
       
       $file = fopen("testfile.txt", "w");
       fwrite($file, $input);
       fclose($file);
       
       $virtualString = new VirtualWriteString("testfile.txt", 1);
       $virtualString->open();
       
       $virtualString->replace(0,"i");

       $this->assertEquals("ibc", $virtualString->substr(0,3));
       $virtualString->close();
      
    }
}