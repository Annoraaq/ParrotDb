<?php

namespace ParrotDb\Persistence\Xml;

use \ParrotDb\Core\ClassMapper;
use \ParrotDb\Core\PSessionFactory;
use \ParrotDb\Core\ObjectMapper;


require_once dirname(__FILE__) . "/../../../testclasses/Author.php";
require_once dirname(__FILE__) . "/../../../testclasses/Publication.php";

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-08-27 at 13:49:11.
 */
class XmlSerializerTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var XmlSerializer
     */
    protected $object;
    
    protected $pm;
    
    protected $session;


    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->session = PSessionFactory::createSession("Testfile", \ParrotDb\Core\PSession::DB_XML);
        $this->pm = $this->session->createPersistenceManager();
        
        if (file_exists("pdb/Testfile/Testfile.pdb")) {
            unlink("pdb/Testfile/Testfile.pdb");
        }
        
       if (file_exists("pdb/Testfile/Author.pdb")) {
            unlink("pdb/Testfile/Author.pdb");
        }
        
        if (file_exists("pdb/Testfile/Publication.pdb")) {
            unlink("pdb/Testfile/Publication.pdb");
        }
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        if ($this->session != null) {
            $this->session->close();
        }
    }
    
    private function createTestAuthor() {
        $author = new \Author("Mr Satan", 53);
        
        $author->allPublications = array();
        
        $author->allPublications[] = new \Publication("Lord Of The Rings");
        $author->allPublications[] = new \Publication("Star Wars");
        
        $author->orderedPublications[17] = new \Publication("Lord Of The Rings");
        $author->orderedPublications[21] = new \Publication("Star Wars");
        $author->nestedPublications = array();
        
        $author->size = 175;
        
        return $author;
    }

    /**
     * @covers ParrotDb\Persistence\Xml\XmlSerializer::serialize
     */
    public function testSerializeClass() {
        
        $author = $this->createTestAuthor();
        $classMapper = new ClassMapper();
        $objectMapper = new ObjectMapper($this->session);
        $classSerializer = new XmlClassSerializer();
        
        $expected = "<?xml version=\"1.0\"?>\n"
         . "<class>"
         . "<name>Author</name>"
         . "<fields>"
         . "<field>name</field>"
         . "<field>age</field>"
         . "<field>size</field>"
         . "<field>publication</field>"
         . "<field>allPublications</field>"
         . "<field>orderedPublications</field>"
         . "<field>nestedPublications</field>"
         . "<field>partner</field>"
         . "<field>nationality</field>"
         . "<field>bla</field>"
         . "<field>testAttribute</field>"
         . "</fields>"
         . "<superclasses>"
         . "<superclass>Person</superclass>"
         . "<superclass>Human</superclass>"
         . "</superclasses>"
         . "</class>\n";
        
        $pClass = $classMapper->createClass($author);
        $xml = new \DOMDocument();
//        $is = $this->serializer->serializeClass($pClass, $xml);
//
//        
        $classSerializer->setDomDocument($xml);
        //$this->serializer->setDomDocument($xml);
        //$class = $this->serializer->serializeClass($classMapper->createClass($author));
        $classSerializer->setPClass($pClass);
        $class = $classSerializer->serialize();
        $xml->appendChild($class);
        $this->assertEquals($expected, $xml->saveXML());
        $oid = $objectMapper->makePersistanceReady($author);
        $obj = null;
        foreach ($objectMapper->getOIdToPhpId() as $pObj) {
            if ($pObj->getObjectId()->getId() == $oid->getId()) {
                $obj = $pObj;
                break;
            }
        }
            
 
        $exp = "<?xml version=\"1.0\"?>\n"
         . "<object>"
            . "<id>0</id>"
            . "<attributes>"
                . "<attribute>"
                    . "<name>name</name>"
                    . "<value>Mr Satan</value>"
                . "</attribute>"
                . "<attribute>"
                    . "<name>age</name>"
                    . "<value>53</value>"
                . "</attribute>"
                . "<attribute>"
                    . "<name>size</name>"
                    . "<value>175</value>"
                . "</attribute>"
                . "<attribute>"
                    . "<name>publication</name>"
                    . "<value/>"
                . "</attribute>"
                . "<attribute>"
                    . "<name>allPublications</name>"
                    . "<value>"
                        . "<array>"
                            . "<elem>"
                                . "<key>0</key>"
                                . "<value>"
                                    . "<objectId>1</objectId>"
                                . "</value>"
                            . "</elem>"
                            . "<elem>"
                                . "<key>1</key>"
                                . "<value>"
                                    . "<objectId>2</objectId>"
                                . "</value>"
                            . "</elem>"
                        . "</array>"
                    . "</value>"
                . "</attribute>"
                . "<attribute>"
                    . "<name>orderedPublications</name>"
                    . "<value>"
                        . "<array>"
                            . "<elem>"
                                . "<key>17</key>"
                                . "<value>"
                                 . "<objectId>3</objectId>"
                                . "</value>"
                            . "</elem>"
                            . "<elem>"
                                . "<key>21</key>"
                                . "<value>"
                                    . "<objectId>4</objectId>"
                                . "</value>"
                            . "</elem>"
                        . "</array>"
                    . "</value>"
                . "</attribute>"
                . "<attribute>"
                    . "<name>nestedPublications</name>"
                    . "<value>"
                        . "<array/>"
                    . "</value>"
                . "</attribute>"
                . "<attribute>"
                    . "<name>partner</name>"
                    . "<value>"
                        . "<objectId>0</objectId>"
                    . "</value>"
                . "</attribute>"
                . "<attribute>"
                    . "<name>nationality</name>"
                    . "<value>german</value>"
                . "</attribute>"
                . "<attribute>"
                    . "<name>bla</name>"
                    . "<value>blaBla</value>"
                . "</attribute>"
                . "<attribute>"
                    . "<name>testAttribute</name>"
                    . "<value>test</value>"
                . "</attribute>"
            . "</attributes>"
         . "</object>\n";


        $xml = new \DOMDocument;
        $objectSerializer = new XmlObjectSerializer();
        $objectSerializer->setDomDocument($xml);
        $objectSerializer->setPObject($obj);
        $object = $objectSerializer->serialize();

     
        $xml->appendChild($object);
        $os = $xml->saveXML();
              // echo "###" . $os;
   
        $this->assertEquals($exp, $os);
        
 

    }
    
    
    
    public function testAddObject() {


    }

}
