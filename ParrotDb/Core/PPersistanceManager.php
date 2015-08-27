<?php

namespace ParrotDb\Core;

use \ParrotDb\ObjectModel\PObjectId;
use \ParrotDb\Query\Constraint\PConstraint;
use \ParrotDb\Query\PResultSet;

/**
 * Description of PersistanceManager
 *
 * @author J. Baum
 */
class PPersistanceManager {

    /**
     *
     * @var PSession database session
     */
    private $session;
    
    /**
     *
     * @var ClassMapper Maps PHP objects to PClass objects
     */
    private $classMapper;
    
    /**
     *
     * @var ObjectMapper Maps PHP objects to PObject objects and back
     */
    private $objectMapper;
    
    /**
     *
     * @var array PHP-objects to be persisted on commit
     */
    private $toPersist;

    /**
     * @param PSession $session
     */
    public function __construct($session) {
        $this->session = $session;
        $this->classMapper = new ClassMapper();
        $this->objectMapper = new ObjectMapper($session);
    }

    /**
     * Add PHP object to list of objects to be persisted on commit
     * 
     * @param mixed $object
     * @return int object-id
     */
    public function persist($object) {
        $this->toPersist[spl_object_hash($object)] = $object;
    }
    
    /**
     * Makes all "to persist" PHP objects persistance ready
     * and persists them.
     */
    public function commit() {
        
        foreach ($this->toPersist as $obj) {
            $this->objectMapper->makePersistanceReady($obj);
        }
        $this->objectMapper->commit();
    }

    /**
     * Fetches the object with the given object id from database.
     * 
     * @param PObjectId $objectId
     * @return object
     */
    public function fetch(PObjectId $objectId) {
        
        return $this->objectMapper->fromPObject(
            $this->session->getDatabase()->fetch($objectId)
        );
    }
    
//    /**
//     * Queries the database.
//     * 
//     * @param PQuery $query
//     * @return PResultSet
//     */
//    public function query(PQuery $query) {
//        $resultSet = $this->session->getDatabase()->query($query);
//        $newResultSet = new PResultSet();
//        
//        foreach ($resultSet as $result) {
//            $newResultSet->add(
//                $this->objectMapper->fromPObject($result)
//            );
//        }
//        
//        return $newResultSet;
//    }
    
    /**
     * Queries the database.
     * 
     * @param PConstraint $constraint
     * @return PResultSet
     */
    public function query(PConstraint $constraint) {
        $resultSet = $this->session->getDatabase()->query($constraint);
        $newResultSet = new PResultSet();
        
        foreach ($resultSet as $result) {
            $newResultSet->add(
                $this->objectMapper->fromPObject($result)
            );
        }
        
        return $newResultSet;
    }
    
    /**
     * Queries and deletes from the database and returns amount of
     * deleted objects.
     * 
     * @param PConstraint $constraint
     * @return int
     */
    public function delete(PConstraint $constraint) {
        return $this->session->getDatabase()->delete($constraint);
    }
    
    /**
     * Queries and deletes from the database where the deletion cascades
     * through all connected objects.
     * 
     * @param PConstraint $constraint
     * @return int
     */
    public function deleteCascade(PConstraint $constraint) {
        return $this->session->getDatabase()->deleteCascade($constraint);
    }


   
}
