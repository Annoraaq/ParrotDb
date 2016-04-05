<?php
namespace ParrotDb\Core;

use \ParrotDb\ObjectModel\PObjectId;
use \ParrotDb\Query\Constraint\PClassConstraint;
use \ParrotDb\Query\Constraint\PAttributeConstraint;
use \ParrotDb\Query\Constraint\PAndConstraint;
use \ParrotDb\Query\Constraint\POrConstraint;
use \ParrotDb\Query\Constraint\PNotConstraint;
use \ParrotDb\Query\Constraint\PRelationConstraint;
use \ParrotDb\Query\LotB\Parser\Parser;
use \ParrotDb\Query\LotB\Tokenizer;

require_once  dirname(__FILE__) . "/testclasses/Author.php";
require_once  dirname(__FILE__) . "/testclasses/TestRec.php";
require_once  dirname(__FILE__) . "/testclasses/Publication.php";
require_once  dirname(__FILE__) . "/testclasses/StaticStub.php";
require_once  dirname(__FILE__) . "/testclasses/PrivateConstructor.php";


/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-08-18 at 17:07:28.
 */
class PPersistanceManagerFeatherTest  extends \PHPUnit_Framework_TestCase
{
    
     /**
     * @var PPersistanceManager
     */
    protected $pm;
    
    protected $session;
    

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
       

        $this->session = PSessionFactory::createSession("Feather", PSession::DB_FEATHER);
        $this->pm = $this->session->createPersistenceManager();
        
        if (file_exists("pdb/Feather/Feather.pdb")) {
            unlink("pdb/Feather/Feather.pdb");
        }
       if (file_exists("pdb/Feather/Author.pdb")) {
            unlink("pdb/Feather/Author.pdb");
        }
        
        if (file_exists("pdb/Feather/Publication.pdb")) {
            unlink("pdb/Feather/Publication.pdb");
        }
        
        if (file_exists("pdb/Feather/PrivateConstructor.pdb")) {
            unlink("pdb/Feather/PrivateConstructor.pdb");
        }
        
         if (file_exists("pdb/Feather/StaticStub.pdb")) {
            unlink("pdb/Feather/StaticStub.pdb");
        }
        
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
       PSessionFactory::closeSession("Feather");
       if (file_exists("pdb/Feather/Feather.pdb")) {
            unlink("pdb/Feather/Feather.pdb");
        }
        
        if (file_exists("pdb/Feather/Author.pdb")) {
            unlink("pdb/Feather/Author.pdb");
        } 
       
        if (file_exists("pdb/Feather/Publication.pdb")) {
            unlink("pdb/Feather/Publication.pdb");
        }
        
        if (file_exists("pdb/Feather/PrivateConstructor.pdb")) {
            unlink("pdb/Feather/PrivateConstructor.pdb");
        }
        
        if (file_exists("pdb/Feather/StaticStub.pdb")) {
            unlink("pdb/Feather/StaticStub.pdb");
        }
    }
    

    
    protected function createTestAuthor() {
        $author = new \Author("Mr Satan", 53);
        $author->publication = new \Publication("Test");
        $author->allPublications = array();
        
        $author->allPublications[] = new \Publication("Lord Of The Rings");
        $author->allPublications[] = new \Publication("Star Wars");
        
        $author->orderedPublications[17] = new \Publication("Lord Of The Rings");
        $author->orderedPublications[21] = new \Publication("Star Wars");
        $author->nestedPublications = array();
        
        $author->size = 175;
        
        return $author;
    }
    
    
    public function testReplace() {
         $author = new \Author("Mr Satan", 53);
         $author->size = 175;
         
         $this->pm->persist($author);
         
         $this->pm->commit();
         $authorReFetched = $this->pm->fetch(new PObjectId(0));
         $this->assertEquals($author, $authorReFetched);
         
         
         $authorReFetched->setAge(1337);
         $this->pm->commit();
         
         
        $constraint = new PClassConstraint("Author");
        
        $result = $this->pm->query($constraint);
        
        $this->assertEquals(1, $result->size());
        
        $refetched = $this->pm->fetch(new PObjectId(0));
        $this->assertEquals(1337, $refetched->getAge());
    }


    /**
     * @covers ParrotDb\Core\PPersistanceManager::persist
     * @todo   Implement testPersist().
     */
    public function testPersist()
    {

        $author = $this->createTestAuthor();
        
        $author2 = $this->createTestAuthor();
        
        $this->assertTrue($author->equals($author2));
        
        $this->pm->persist($author);
        
        $this->pm->commit();

        $authorReFetched = $this->pm->fetch(new PObjectId(0));

        $this->assertTrue($author2->equals($authorReFetched));
        
    }
    
    public function testPersistStatic()
    {
        $static = new \StaticStub();
        $human = $this->createTestAuthor();
        //\StaticStub::$human = "Test";
        \StaticStub::$human = $human;

        $this->pm->persist($static);
        
        $this->pm->commit();
        
        
        
        \StaticStub::$human->setName("Fridolin");
        $this->pm->fetch(new PObjectId(0));

        $this->assertFalse(\StaticStub::$human->getName() == "Fridolin");
        
        $this->assertFalse(\StaticStub::$human->equals($human));
        
    }
    
    public function testIgnoreStatic()
    {
        $static = new \StaticStub();
        $human = $this->createTestAuthor();
        $this->pm->setConfigValue("ignoreStatic", true);
        //\StaticStub::$human = "Test";
        \StaticStub::$human = $human;

        $this->pm->persist($static);
        
        $this->pm->commit();
        
        
        
        \StaticStub::$human->setName("Fridolin");
        $this->pm->fetch(new PObjectId(0));

        $this->assertTrue(\StaticStub::$human->getName() == "Fridolin");
        
        $this->assertTrue(\StaticStub::$human->equals($human));
        
    }
    
    public function testPersistPrivateConstructor()
    {

        $privateConstructorObject = \PrivateConstructor::createObject("TestAttr");
        
        $this->assertTrue($privateConstructorObject->equals($privateConstructorObject));
        
        $this->pm->persist($privateConstructorObject);
        
        $this->pm->commit();

        $privateConstructorObjectReFetched = $this->pm->fetch(new PObjectId(0));

        $this->assertTrue($privateConstructorObject->equals($privateConstructorObjectReFetched ));
        
    }
    
    /**
     * @covers ParrotDb\Core\PPersistanceManager::persist
     * @todo   Implement testPersist().
     */
    public function testTransaction()
    {
        $author = $this->createTestAuthor();
        $this->pm->persist($author);
        $this->assertFalse(
        $this->session->getDatabase()->isPersisted(new PObjectId(0)));
        $author->setName("Son Goku");
        $this->pm->commit();
        $this->assertTrue($this->session->getDatabase()->isPersisted(new PObjectId(0)));
        $this->assertEquals($this->pm->fetch(new PObjectId(0))->getName(), "Son Goku");

    }
    
    /**
     * @covers ParrotDb\Core\PPersistanceManager::persist
     * @todo   Implement testPersist().
     */
    public function testPersistNestedArray()
    {
        $author = $this->createTestAuthor();
        
        $author->nestedPublications[] = array (
         new \Publication("Star Wars2"),
         new \Publication("Star Wars3"),
         new \Publication("Star Wars4")
        );
//        
        $author2 = $this->createTestAuthor();
        
        $author2->nestedPublications[] = array (
         new \Publication("Star Wars2"),
         new \Publication("Star Wars3"),
         new \Publication("Star Wars4"),
        );
        
        $author3 = $this->createTestAuthor();
        
        $author3->nestedPublications[] = array (
         new \Publication("Star Wars2"),
         new \Publication("Star Wars3"),
         new \Publication("Star Wars5"),
        );
        
        $author4 = $this->createTestAuthor();
        
        $author4->nestedPublications[] = array (
         new \Publication("Star Wars2"),
         new \Publication("Star Wars3"),
         new \Publication("Star Wars4"),
         new \Publication("Star Wars5"),
        );
        $this->assertTrue($author->equals($author2));
        $this->assertFalse($author->equals($author3));
        $this->assertFalse($author->equals($author4));
        
        $this->pm->persist($author);
        $this->pm->commit();

        echo "startFetch\n";
        $authorReFetched = $this->pm->fetch(new PObjectId(0));
        echo "endFetch\n";
                
//        echo "AUTHOR2\n";
//       var_dump($author2);
//        
//        echo "AUTHOR\n";
//       var_dump($author);
//        echo "AUTHOR FETCHED\n";
//       var_dump($authorReFetched);

        
//        
        $this->assertTrue($author2->equals($authorReFetched));
        $this->assertFalse($author3->equals($authorReFetched));
        $this->assertFalse($author->equals($author4));
        
    }

    /**
     * @covers ParrotDb\Core\PPersistanceManager::fetch
     * @todo   Implement testFetch().
     */
    public function testFetch()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }
    
    public function testClassConstraint() {
        $author = $this->createTestAuthor();
        $author2 = $this->createTestAuthor();
        $this->pm->persist($author);
        $this->pm->persist($author2);
        
        $this->pm->commit();

        $constraint = new PClassConstraint("Author");
        
        $result = $this->pm->query($constraint);
        
        $this->assertEquals(2, $result->size());
        


        $this->assertTrue($author->equals($result->first()));
    }
    
    public function testAmountConstraint() {
        $author = $this->createTestAuthor();
        $author2 = $this->createTestAuthor();
        $this->pm->persist($author);
        $this->pm->persist($author2);
        
        $this->pm->commit();

        $constraint = new PClassConstraint("Author");
        $constraint->setAmount(1);
        
        $result = $this->pm->query($constraint);
        
        $this->assertEquals($result->size(), 1);
    }
    
    /**
     * @covers ParrotDb\Core\PPersistanceManager::query
     */
    public function testAttributeConstraint() {
        $author = $this->createTestAuthor();
        
        $this->pm->persist($author);
        
        $this->pm->commit();
        
       // $query = PQueryBuilder::get("Author")->attr("name", "Mr Satan");
        $constraint = new PClassConstraint("Author", new PAttributeConstraint("name", "Mr Satan"));
        
        $authorReQueried = $this->pm->query($constraint)->first();

        $this->assertTrue($author->equals($authorReQueried));
        
        $author2 = $this->createTestAuthor();
        $this->pm->persist($author2);
        $this->pm->commit();
        
        $result = $this->pm->query($constraint);
        
        $this->assertEquals(2, $result->size());

    }
    
    
    /**
     * @covers ParrotDb\Core\PPersistanceManager::query
     */
    public function testAndConstraint() {
        $author = $this->createTestAuthor();
        $author2 = $this->createTestAuthor();
        $author2->setAge(10);
        $this->pm->persist($author);
        $this->pm->persist($author2);
        
        $this->pm->commit();
        
       // $query = PQueryBuilder::get("Author")->attr("name", "Mr Satan");
        $constraints[] = new PAttributeConstraint("name", "Mr Satan");
        $constraints[] = new PAttributeConstraint("age", 10);
        $constraint = new PClassConstraint("Author", new PAndConstraint($constraints));
        
        $result = $this->pm->query($constraint);
        
        $this->assertEquals($result->size(), 1);
        

    }
    
    /**
     * @covers ParrotDb\Core\PPersistanceManager::query
     */
    public function testOrConstraint() {
        $author = $this->createTestAuthor();
        $author2 = $this->createTestAuthor();
        $author2->setAge(10);
        $author2->setName("Mrs Satan");
        $this->pm->persist($author);
        $this->pm->persist($author2);
        
        $this->pm->commit();
        
        $constraints[] = new PAttributeConstraint("name", "Mr Satan");
        $constraints[] = new PAttributeConstraint("age", 10);
        $constraint = new PClassConstraint("Author", new POrConstraint($constraints));
        
        $result = $this->pm->query($constraint);
        
        $this->assertEquals($result->size(), 2);


    }
    
    /**
     * @covers ParrotDb\Core\PPersistanceManager::query
     */
    public function testNotConstraint() {
        $author = $this->createTestAuthor();
        $author2 = $this->createTestAuthor();
        $author2->setAge(10);
        $this->pm->persist($author);
        $this->pm->persist($author2);
        
        $this->pm->commit();
        
        $constraint = new PClassConstraint("Author", new PNotConstraint(new PAttributeConstraint("age", 10)));
        
        $result = $this->pm->query($constraint);
        
        $this->assertEquals($result->size(), 1);
        
        $this->assertTrue($author->equals($result->first()));


    }
    
    /**
     * @covers ParrotDb\Core\PPersistanceManager::query
     */
    public function testRelationConstraint() {
        $author = $this->createTestAuthor();
        
        $author2 = $this->createTestAuthor();
        $author2->allPublications = array (
         new \Publication("Star Wars2"),
         new \Publication("Star Wars3"),
         new \Publication("Star Wars4"),
        );
        
        $author3 = $this->createTestAuthor();
        
        $author3->allPublications = array (
         new \Publication("Star Wars2"),
         new \Publication("Star Wars2")
        );
        
        $author4 = $this->createTestAuthor();
        
        $author4->allPublications = array (
         new \Publication("Star Wars2"),
         new \Publication("Star Wars3"),
         new \Publication("Star Wars2")
        );
        
        
        $this->pm->persist($author);
        $this->pm->persist($author2);
        $this->pm->persist($author3);
        $this->pm->persist($author4);
        
        $this->pm->commit();
        
        $constraint = new PClassConstraint("Author",
         new PRelationConstraint($this->session->getDatabase(), "allPublications", "Publication", PRelationConstraint::OP_GTE, "1",
         new PAttributeConstraint("name", "Star Wars2")));

        $result = $this->pm->query($constraint);
        
        $this->assertEquals(3, $result->size());
        
        $constraint = new PClassConstraint("Author",
         new PRelationConstraint($this->session->getDatabase(), "allPublications", "Publication", PRelationConstraint::OP_EQ, "1",
         new PAttributeConstraint("name", "Star Wars2")));

        $result = $this->pm->query($constraint);
        
        $this->assertEquals(1, $result->size());
        
        $this->assertTrue($author2->equals($result->first()));
  


    }
    
    /**
     * @covers ParrotDb\Core\PPersistanceManager::query
     */
    public function testClassConstraintParser() {
        $author = $this->createTestAuthor();
        $author2 = $this->createTestAuthor();
        $this->pm->persist($author);
        $this->pm->persist($author2);
        
        $this->pm->commit();

        //$constraint = new PClassConstraint("Author");
        $parser = new Parser($this->session->getDatabase());
        $constraint = $parser->parse("get Author");
        
        $result = $this->pm->query($constraint);
        
        $this->assertEquals($result->size(), 2);

        $this->assertTrue($author->equals($result->first()));

    }
    
    /**
     * @covers ParrotDb\Core\PPersistanceManager::query
     */
    public function testStandardizeParser() {
        
        $parser = new Tokenizer("get Author 3 (name = \"test\", age = 53) or (name = \"Mrs Satan\", age<=23) or publications contains <=3{Publication name = \"birdbook\"} >> age");
        $out = $parser->tokenize();
        
        $comp[] = "get";
        $comp[] = "Author";
        $comp[] = "3";
        $comp[] = "(";
        $comp[] = "name";
        $comp[] = "=";
        $comp[] = "\"test\"";
        $comp[] = ",";
        $comp[] = "age";
        $comp[] = "=";
        $comp[] = "53";
        $comp[] = ")";
        $comp[] = "or";
        $comp[] = "(";
        $comp[] = "name";
        $comp[] = "=";
        $comp[] = "\"Mrs Satan\"";
        $comp[] = ",";
        $comp[] = "age";
        $comp[] = "<=";
        $comp[] = "23";
        $comp[] = ")";
        $comp[] = "or";
        $comp[] = "publications";
        $comp[] = "contains";
        $comp[] = "<=";
        $comp[] = "3";
        $comp[] = "{";
        $comp[] = "Publication";
        $comp[] = "name";
        $comp[] = "=";
        $comp[] = '"birdbook"';
        $comp[] = "}";
        $comp[] = ">>";
        $comp[] = "age";
        
//var_dump($out);
        $this->assertEquals($comp, $out);
        
        
        $parser = new Tokenizer('get 1 Author name = "Mrs Satan", age <= 3');
        $out = $parser->tokenize();
        
        $comp = [];
        $comp[] = "get";
        $comp[] = "1";
        $comp[] = "Author";
        $comp[] = "name";
        $comp[] = "=";
        $comp[] = '"Mrs Satan"';
        $comp[] = ",";
        $comp[] = "age";
        $comp[] = "<=";
        $comp[] = "3";
        
//        
//        var_dump($out);
        
        $this->assertEquals($comp, $out);


    }
    
    /**
     * @covers ParrotDb\Core\PPersistanceManager::query
     */
    public function testAmountConstraintParser() {
        $author = $this->createTestAuthor();
        $author2 = $this->createTestAuthor();
        $this->pm->persist($author);
        $this->pm->persist($author2);
        
        $this->pm->commit();

        //$constraint = new PClassConstraint("Author");
        //$constraint->setAmount(1);
        $parser = new Parser($this->session->getDatabase());
        $constraint = $parser->parse("get 1 Author");
        
        $result = $this->pm->query($constraint);
        
        $this->assertEquals($result->size(), 1);

    }
    
    /**
     * @covers ParrotDb\Core\PPersistanceManager::query
     */
    public function testAttributeConstraintParser() {
        $author = $this->createTestAuthor();
        $author2 = $this->createTestAuthor();
        $author2->setName("Mrs Satan");
        $this->pm->persist($author);
        $this->pm->persist($author2);
        
        $this->pm->commit();

        //$constraint = new PClassConstraint("Author");
        //$constraint->setAmount(1);
        $parser = new Parser($this->session->getDatabase());
        $constraint = $parser->parse('get 1 Author name = "Mrs Satan"');
        

        
        $result = $this->pm->query($constraint);
        
//        var_dump($result->first());
//        
        $this->assertTrue($author2->equals($result->first()));

    }
    
    /**
     * @covers ParrotDb\Core\PPersistanceManager::query
     */
    public function testAttributeAdvancedConstraintParser() {
        
        $author = $this->createTestAuthor();
        $author2 = $this->createTestAuthor();
        $author2->setAge(1);
        $author3 = $this->createTestAuthor();
        $author3->setAge(2);
        $author4 = $this->createTestAuthor();
        $author4->setAge(3);
        $this->pm->persist($author);
        $this->pm->persist($author2);
        $this->pm->persist($author3);
        $this->pm->persist($author4);
        
        $this->pm->commit();

        $parser = new Parser($this->session->getDatabase());
        $constraint = $parser->parse('get Author name = "Mr Satan", age <= 10');
        

        $result = $this->pm->query($constraint);

        $this->assertEquals(3, $result->size());
        
        
        
        $constraint = $parser->parse('get Author name = "Mr Satan" or age <= 10');


        $result = $this->pm->query($constraint);

        $this->assertEquals(4, $result->size());

    }
    
    /**
     * @covers ParrotDb\Core\PPersistanceManager::query
     */
    public function testRelationalConstraintParser() {
        
        $author = $this->createTestAuthor();
        
        $author2 = $this->createTestAuthor();
        $author2->allPublications = array (
         new \Publication("Star Wars2"),
         new \Publication("Star Wars3"),
         new \Publication("Star Wars4"),
        );
        
        $author3 = $this->createTestAuthor();
        
        $author3->allPublications = array (
         new \Publication("Star Wars2"),
         new \Publication("Star Wars2")
        );
        
        $author4 = $this->createTestAuthor();
        
        $author4->allPublications = array (
         new \Publication("Star Wars2"),
         new \Publication("Star Wars3"),
         new \Publication("Star Wars2")
        );
        
        
        $this->pm->persist($author);
        $this->pm->persist($author2);
        $this->pm->persist($author3);
        $this->pm->persist($author4);
        
        $this->pm->commit();

        $parser = new Parser($this->session->getDatabase());
        $constraint = $parser->parse('get Author allPublications contains >= 1 {Publication name = "Star Wars2"}');
        
        $result = $this->pm->query($constraint);

        $this->assertEquals(3, $result->size());
        
        $constraint = $parser->parse('get Author (allPublications contains = 1 {Publication (name = "Star Wars2")})');
        
        $result = $this->pm->query($constraint);

        $this->assertEquals(1, $result->size());
        $this->assertTrue($author2->equals($result->first()));
        
        $constraint = $parser->parse('get Author allPublications contains {Publication name = "Star Wars2"}');
        
        $result = $this->pm->query($constraint);

        $result = $this->pm->query($constraint);
        $this->assertEquals(3, $result->size());
        


    }
    
    /**
     * @covers ParrotDb\Core\PPersistanceManager::query
     */
    public function testGroupingParser() {
        
        $author = $this->createTestAuthor();
        $author->setAge(53);
        
        $author2 = $this->createTestAuthor();
        $author2->setName("Mrs Satan");
        $author2->setAge(23);
        
      
        
        $this->pm->persist($author);
        $this->pm->persist($author2);
        
        $this->pm->commit();

        $parser = new Parser($this->session->getDatabase());
        $constraint = $parser->parse('get Author name = "Mr Satan", age = 53 or age=23, name = "Mr Satan2"');
        
        $result = $this->pm->query($constraint);

        $this->assertEquals(1, $result->size());
        
        $constraint = $parser->parse('get Author (name = "Mr Satan", age = 53) or (age=23, name = "Mr Satan2")');
        
        $result = $this->pm->query($constraint);

        $this->assertEquals(1, $result->size());

        


    }
    
    /**
     * @covers ParrotDb\Core\PPersistanceManager::query
     */
    public function testNotConstraintParser() {
        
        $author = $this->createTestAuthor();
        $author->setAge(53);
        
        $author2 = $this->createTestAuthor();
        $author2->setName("Mrs Satan");
        $author2->setAge(23);
        
      
        
        $this->pm->persist($author);
        $this->pm->persist($author2);
        
        $this->pm->commit();

        $parser = new Parser($this->session->getDatabase());
        $constraint = $parser->parse('get Author not name = "Mr Satan"');
        
        $result = $this->pm->query($constraint);

        $this->assertEquals(1, $result->size());

         $this->assertTrue($author2->equals($result->first()));


    }
    
    /**
     * @covers ParrotDb\Core\PPersistanceManager::query
     */
    public function testOrderByParser() {
        
        $author = $this->createTestAuthor();
        $author->setAge(10);
        
        $author2 = $this->createTestAuthor();
        $author2->setName("Mrs Satan");
        $author2->setAge(23);
        
        $author3 = $this->createTestAuthor();
        $author3->setName("Piccolo");
        $author3->setAge(10);
        $author3->size = 200;
        
        $author4 = $this->createTestAuthor();
        $author4->setName("Son Goku");
        $author4->setAge(23);
        $author4->size = 200;
        
      
        
        $this->pm->persist($author);
        $this->pm->persist($author2);
        
        $this->pm->commit();

        $parser = new Parser($this->session->getDatabase());
        $constraint = $parser->parse('get Author >> age');
        
        $result = $this->pm->query($constraint);

        $this->assertEquals(2, $result->size());


         $this->assertTrue($author2->equals($result->first()));
         
         $constraint = $parser->parse('get Author << age');
        
        $result = $this->pm->query($constraint);

        $this->assertEquals(2, $result->size());


         $this->assertTrue($author->equals($result->first()));
         
         
         $this->pm->persist($author3);
        $this->pm->persist($author4);
        
        $this->pm->commit();
         
        $constraint = $parser->parse('get Author >> (age,size)');
        
        $result = $this->pm->query($constraint);

        $this->assertEquals(4, $result->size());
        
        $this->assertTrue($author4->equals($result->first()));
        
        
        $constraint = $parser->parse('get 2 Author >> age');
        
        $result = $this->pm->query($constraint);

        $this->assertEquals(2, $result->size());


         $this->assertTrue($author2->equals($result->first()));
         
         $constraint = $parser->parse('get 4 Author >> (age,size)');
        
        $result = $this->pm->query($constraint);

        $this->assertEquals(4, $result->size());
        $this->assertTrue($author4->equals($result->first()));
    }
    
    
    /**
     * @covers ParrotDb\Core\PPersistanceManager::query
     */
    public function testDelete() {
        
        $author = $this->createTestAuthor();
        $author->setAge(53);
        
        $author2 = $this->createTestAuthor();
        $author2->setName("Mrs Satan");
        $author2->setAge(23);
        
        $this->pm->persist($author);
        $this->pm->persist($author2);
        
        $this->pm->commit();
        

        $parser = new Parser($this->session->getDatabase());
        $constraint = $parser->parse('get Author name = "Mr Satan", age = 53 or age=23, name = "Mr Satan2"');
        
        $this->pm->delete($constraint);
        $result = $this->pm->commit();

        $this->assertEquals(1, $result);
        
        $constraint = $parser->parse('get Author (name = "Mr Satan", age = 53) or (age=23, name = "Mr Satan2")');
        
        $result = $this->pm->query($constraint);

        $this->assertEquals(0, $result->size());
        
        $constraint = $parser->parse('get Author name = "Mrs Satan"');
        
        $result = $this->pm->query($constraint);

        $this->assertEquals(1, $result->size());
        
        $this->assertTrue($author2->equals($result->first()));
        
        
        $this->pm->persist($author);
        $this->pm->commit();
        $parser = new Parser($this->session->getDatabase());
        $constraint = $parser->parse('get Author');
        
        $result = $this->pm->query($constraint);
        
        $this->assertEquals(2, $result->size());
        
        
        $this->pm->delete($constraint);
        $delCounter = $this->pm->commit();
        $this->assertEquals(2, $delCounter);
        $result = $this->pm->query($constraint);
        
        //var_dump($result);
        
        $this->assertEquals(0, $result->size());
    }
    
    /**
     * @covers ParrotDb\Core\PPersistanceManager::query
     */
    public function testSuperclasses() {
        
        $author = $this->createTestAuthor();
        $author->setAge(53);
        
        $author2 = $this->createTestAuthor();
        $author2->setName("Mrs Satan");
        $author2->setAge(23);
        
        $this->pm->persist($author);
        $this->pm->persist($author2);
        
        $this->pm->commit();

        $parser = new Parser($this->session->getDatabase());
        $constraint = $parser->parse('get Human');
        
        $result = $this->pm->query($constraint);

        $this->assertEquals(2, $result->size());
        
       
    }
    
    /**
     * @covers ParrotDb\Core\PPersistanceManager::query
     */
    public function testDeleteCascade() {
        
        $author = $this->createTestAuthor();
        $author->setAge(53);
        
        $author2 = $this->createTestAuthor();
        $author2->setName("Mrs Satan");
        $author2->setAge(23);
        
        $this->pm->persist($author);
        $this->pm->persist($author2);
        
        $this->pm->commit();

        $parser = new Parser($this->session->getDatabase());
        $constraint = $parser->parse('get Publication name = "Star Wars"');
        
        $result = $this->pm->query($constraint);

        $this->assertEquals(4, $result->size());
        
        $constraint = $parser->parse('get Author name = "Mr Satan"');
        
       $result = $this->pm->deleteCascade($constraint);
        
        $constraint2 = $parser->parse('get Author');
        
        $result2 = $this->pm->query($constraint2);

        $this->assertEquals(1, $result2->size());

        $this->assertEquals(6, $result);
        
        $constraint = $parser->parse('get Publication name = "Star Wars"');
        
        $result = $this->pm->query($constraint);

        $this->assertEquals(2, $result->size());
        
       
    }
    
    public function testAutoPersist() {
        $author = $this->createTestAuthor();
        $this->pm->persist($author);
        
        $this->pm->commit();

        $parser = new Parser($this->session->getDatabase());
        $constraint = $parser->parse('get Author');
        $result = $this->pm->query($constraint);
        
        
        $this->assertEquals(1, $result->size());
        $fetchedAuthor = $result->first();
        
        $fetchedAuthor->setName("Piccolo");
        
        $this->pm->commit();
        
        $result = $this->pm->query($constraint);
        $this->assertEquals(1, $result->size());
        
        $this->assertEquals("Piccolo", $result->first()->getName());

    }
    
    public function testAutoPersistOff() {
        $author = $this->createTestAuthor();
        $this->pm->persist($author);
        
        $this->pm->commit();

        $parser = new Parser($this->session->getDatabase());
        $constraint = $parser->parse('get Author');
        $result = $this->pm->query($constraint, false);
        
        
        $this->assertEquals(1, $result->size());
        $fetchedAuthor = $result->first();
        
        $fetchedAuthor->setName("Piccolo");
        
        $this->pm->commit();
        
        $result = $this->pm->query($constraint);
        $this->assertEquals(1, $result->size());
        
        $this->assertEquals("Mr Satan", $result->first()->getName());

    }
    
    public function testQueryShortcut() {
        $author = $this->createTestAuthor();
        $this->pm->persist($author);
        
        $this->pm->commit();

        $parser = new Parser($this->session->getDatabase());
        $constraint = $parser->parse('get Author');
        $result = $this->pm->query($constraint);
        
        $result2 = $this->pm->query('get Author');
        
        $this->assertEquals($result->size(), $result2->size());
        
       
    }
    
    
    public function testActivationDepth() {
        $this->pm->setConfigValue('activationDepth', 0);
        $author = $this->createTestAuthor();
        $this->pm->persist($author);
        
        $this->pm->commit();

        $parser = new Parser($this->session->getDatabase());
        $constraint = $parser->parse('get 1 Author');
        $result = $this->pm->query($constraint);
        
        $this->assertTrue($result->first()->publication instanceof PObjectId);
    }
    
    public function testEscaping() {
        $author = $this->createTestAuthor();
        $author->setName("Mr [Satan]");
        $this->pm->persist($author);
        
        $this->pm->commit();

        $parser = new Parser($this->session->getDatabase());
        $constraint = $parser->parse('get Author');
        $result = $this->pm->query($constraint);
        
        $this->assertEquals("Mr [Satan]", $result->first()->getName());
        
       
    }
    
    
    /**
     * @covers ParrotDb\Core\PPersistanceManager::query
     */
    public function testSerializeClass() {
        
//        $author = $this->createTestAuthor();
//        $author->setAge(53);
//       
////        $objectMapper = new ObjectMapper($this->session);
////        $objectMapper->
//        
//        $serialoze
//        $pClass = $this->classMapper->createClass($author);
        
       
    }
    
 
}
