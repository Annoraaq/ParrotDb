<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
  <head>
    <meta charset="UTF-8">
    <title></title>
  </head>
  <body>
      <?php
      
        use \ParrotLight\Core\PAutoloader;
        use \ParrotLight\Core\PSessionFactory;
        use \ParrotLight\Core\PDebug;
        use \ParrotLight\ObjectModel\PObjectId;
        use \Author;
        

        define( 'ROOT_PATH', dirname( dirname( __FILE__ ) ) . '/ParrotLight/' );
        define( 'CORE_PATH', ROOT_PATH . 'ParrotLight/Core/' );
        
        require_once CORE_PATH . 'PAutoloader.php';

        $autoloader = new PAutoloader('ParrotLight');
        $autoloader->register();

        
        //$definingScope = new PMetaDefiningScope("Schema");
        $author = new Author("Mr Satan", 53);
        $author->publication = new Publication("Test");
        $author->allPublications = array();
        
        $author->allPublications[] = new Publication("Lord Of The Rings");
        $author->allPublications[] = new Publication("Star Wars");
        
        $author->orderedPublications[17] = new Publication("Lord Of The Rings");
        $author->orderedPublications[21] = new Publication("Star Wars");
        
        PDebug::dump($author);
        
//        $session = PSessionFactory::createSession("Testfile.db");
//        $pm = $session->createPersistenceManager();
//        $pm->persist($author);
//        
//        $pm->fetch(new PObjectId(0));
        
      ?>
  </body>
</html>
