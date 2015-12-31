<?php

namespace ParrotDb\Persistence\Xml;

use \ParrotDb\Core\PSessionFactory;
use \ParrotDb\Core\PSession;
use \ParrotDb\ObjectModel\PObjectId;
use \ParrotDb\Core\ObjectMapper;


require_once dirname(__FILE__) . "/testclasses/Author.php";
require_once dirname(__FILE__) . "/testclasses/Publication.php";

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-08-27 at 21:39:56.
 */
class XmlFileManagerTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var XmlFileManager
     */
    protected $fileManager;
    
    protected $pm;
    
    protected $session;
    
    protected $serializer;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->fileManager = new XmlFileManager("Testfile");
        $this->session = PSessionFactory::createSession(
            "Testfile",
            PSession::DB_MEMORY
        );
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
        
        if (file_exists("pdb/Testfile/PrivateConstructor.pdb")) {
            unlink("pdb/Testfile/PrivateConstructor.pdb");
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
     * @covers ParrotDb\Persistence\Xml\XmlFileManager::storeObject
     * @todo   Implement testStoreObject().
     */
    public function testStoreObject() {
        
        $this->assertFalse($this->fileManager->isObjectStored(new PObjectId(0)));
        $this->assertFalse($this->fileManager->isObjectStored(new PObjectId(1)));
            
        $author = $this->createTestAuthor();
        $author2 = $this->createTestAuthor();
        $author2->setName("Piccolo");
        $objectMapper = new ObjectMapper($this->session);

        $oid = $objectMapper->makePersistanceReady($author);
        $obj = null;
        foreach ($objectMapper->getOIdToPhpId() as $pObj) {
            if ($pObj->getObjectId()->getId() == $oid->getId()) {
                $obj = $pObj;
                break;
            }
        }

        $this->fileManager->storeObject($obj);
        
        $this->assertTrue($this->fileManager->isObjectStored(new PObjectId(0)));
        $this->assertFalse($this->fileManager->isObjectStored(new PObjectId(1)));
        
        $oid = $objectMapper->makePersistanceReady($author2);
        $obj = null;
        foreach ($objectMapper->getOIdToPhpId() as $pObj) {
            if ($pObj->getObjectId()->getId() == $oid->getId()) {
                $obj = $pObj;
                break;
            }
        }

        $this->fileManager->storeObject($obj);
        
        $this->assertTrue($this->fileManager->isObjectStored(new PObjectId(0)));
        $this->assertTrue($this->fileManager->isObjectStored(new PObjectId(5)));
    
    }
    
    /**
     * @covers ParrotDb\Persistence\Xml\XmlFileManager::storeObject
     * @todo   Implement testFetchObject().
     */
    public function testFetchObject() {
        
//        $this->assertFalse($this->fileManager->isObjectStored(new PObjectId(0)));
//        $this->assertFalse($this->fileManager->isObjectStored(new PObjectId(1)));
//            
        $author = $this->createTestAuthor();
        $author2 = $this->createTestAuthor();
        $author2->setName("Piccolo");
        $objectMapper = new ObjectMapper($this->session);

        $oid = $objectMapper->makePersistanceReady($author);
        $obj = null;
        foreach ($objectMapper->getOIdToPhpId() as $pObj) {
            if ($pObj->getObjectId()->getId() == $oid->getId()) {
                $obj = $pObj;
                break;
            }
        }

        $this->fileManager->storeObject($obj);
        
        $this->assertTrue($this->fileManager->isObjectStored(new PObjectId(0)));
        $this->assertTrue($this->fileManager->fetch(new PObjectId(0))->equals($obj));
        
        $this->assertTrue($this->fileManager->isObjectStored(new PObjectId(0)));
        $this->assertFalse($this->fileManager->isObjectStored(new PObjectId(1)));
        
        $oid = $objectMapper->makePersistanceReady($author2);
        $obj = null;
        foreach ($objectMapper->getOIdToPhpId() as $pObj) {
            if ($pObj->getObjectId()->getId() == $oid->getId()) {
                $obj = $pObj;
                break;
            }
        }

        $this->fileManager->storeObject($obj);
        
        $this->assertTrue($this->fileManager->isObjectStored(new PObjectId(0)));
        $this->assertTrue($this->fileManager->isObjectStored(new PObjectId(5)));
    
    }
    

}
