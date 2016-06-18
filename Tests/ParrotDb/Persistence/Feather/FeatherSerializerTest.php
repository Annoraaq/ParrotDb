<?php

namespace ParrotDb\Persistence\Feather;

use \ParrotDb\Core\ClassMapper;
use \ParrotDb\Core\PSessionFactory;
use \ParrotDb\Core\ObjectMapper;


require_once dirname(__FILE__) . "/../../../testclasses/Author.php";
require_once dirname(__FILE__) . "/../../../testclasses/Publication.php";

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-08-27 at 13:49:11.
 */
class FeatherSerializerTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var FeatherSerializer
     */
    protected $object;
    
    protected $pm;
    
    protected $session;


    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->session = PSessionFactory::createSession(dirname(__FILE__) . "/pdb/Feather", \ParrotDb\Core\PSession::DB_FEATHER);
        $this->pm = $this->session->createPersistenceManager();
        
//        if (file_exists("pdb/Feather/Feather.pdb")) {
//            unlink("pdb/Feather/Feather.pdb");
//        }
//        
//       if (file_exists("pdb/Feather/Author.pdb")) {
//            unlink("pdb/Feather/Author.pdb");
//        }
//        
//        if (file_exists("pdb/Feather/Publication.pdb")) {
//            unlink("pdb/Feather/Publication.pdb");
//        }
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        PSessionFactory::closeSession(dirname(__FILE__) . "/pdb/Feather");
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
        $classSerializer = new FeatherClassSerializer();
        
        $expected = "c['Author',attr{'name','age','size','publication',"
         . "'allPublications','orderedPublications',"
         . "'nestedPublications','partner','nationality','bla',"
         . "'testAttribute'},sc{'Person','Human'}]";
        
        $pClass = $classMapper->createClass($author);

        $classSerializer->setPClass($pClass);
        $class = $classSerializer->serialize();
        $this->assertEquals($expected, $class);
        $oid = $objectMapper->makePersistanceReady($author);
        $obj = null;
        foreach ($objectMapper->getOIdToPhpId() as $pObj) {
            if ($pObj->getObjectId()->getId() == $oid->getId()) {
                $obj = $pObj;
                break;
            }
        }
            
        $exp = "[0,267,'name':8:'Mr Satan','age':2:'53','size':3:'175',"
            . "'publication':0:'','allPublications':19:{'0':1:(1),'1':1:(2)},"
            . "'orderedPublications':21:{'17':1:(3),'21':1:(4)},"
            . "'nestedPublications':0:{},'partner':1:(0),"
            . "'nationality':6:'german','bla':6:'blaBla',"
            . "'testAttribute':4:'test']";
        
       
        $objectSerializer = new FeatherObjectSerializer();
        $objectSerializer->setPObject($obj);
        $object = $objectSerializer->serialize();
        $this->assertEquals($exp, $object);

    }
    
    /**
     * @covers ParrotDb\Persistence\Xml\XmlSerializer::serialize
     */
    public function testSerializePublicationClass() {
        
        $publication = new \Publication("Test");
        $classMapper = new ClassMapper();
        $objectMapper = new ObjectMapper($this->session);
        $classSerializer = new FeatherClassSerializer();
        
        $expected = "c['Publication',attr{'name'},sc{}]";
        
        $pClass = $classMapper->createClass($publication);

        $classSerializer->setPClass($pClass);
        $class = $classSerializer->serialize();
        $this->assertEquals($expected, $class);
    }
    
    
    
    public function testAddObject() {


    }

}
