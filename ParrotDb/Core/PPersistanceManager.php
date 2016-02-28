<?php

namespace ParrotDb\Core;

use \ParrotDb\ObjectModel\PObjectId;
use \ParrotDb\Query\Constraint\PConstraint;
use \ParrotDb\Query\Constraint\POrConstraint;
use \ParrotDb\Query\Constraint\PClassConstraint;
use \ParrotDb\Query\PResultSet;

/**
 * This class is used to initiate all of the database operations and is the
 * interface to the user.
 *
 * @author J. Baum
 */
class PPersistanceManager
{

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
     * @var array PHP-objects to be deleted on commit
     */
    private $toDelete;

    /**
     * @param PSession $session
     */
    public function __construct($session)
    {
        $this->session = $session;
        $this->classMapper = new ClassMapper();
        $this->objectMapper = new ObjectMapper($session);
        $this->toDelete = array();
    }

    /**
     * Add PHP object to list of objects to be persisted on commit
     * 
     * @param mixed $object
     * @return int object-id
     */
    public function persist($object)
    {
        $this->toPersist[spl_object_hash($object)] = $object;
    }

    /**
     * Makes all "to persist" PHP objects persistance ready
     * and persists them.
     */
    public function commit()
    {
        
        $counter = 0;
        foreach ($this->toDelete as $className => $arr) {
            $temp = $arr[0];
            $arr[0] = $temp->getConstraint();
            $constr = new POrConstraint($arr);
            $temp->setConstraint($constr);
            
            $counter += $this->session->getDatabase()->delete($temp);
            unset($this->toDelete[$className]);
        }
        
        
        foreach ($this->toPersist as $key => $obj) {
            $this->objectMapper->makePersistanceReady($obj);
            unset($this->toPersist[$key]);
        }

        $this->objectMapper->commit();
        
        return $counter;
    }

    /**
     * Fetches the object with the given object id from database.
     * 
     * @param PObjectId $objectId
     * @return object
     */
    public function fetch(PObjectId $objectId)
    {
        $pObject = $this->session->getDatabase()->fetch($objectId);
        $res = $this->objectMapper->fromPObject($pObject);
        $this->persist($res);
        $this->objectMapper->addToPersistedMemory($res, $pObject);
        return $res;
    }

    /**
     * Queries the database.
     * 
     * @param PConstraint $constraint
     * @return PResultSet
     */
    public function query(PConstraint $constraint)
    {
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
    public function delete(PClassConstraint $constraint)
    {
        //return $this->session->getDatabase()->delete($constraint);
        $this->toDelete[$constraint->getClassName()][] = $constraint;
    }

    /**
     * Queries and deletes from the database where the deletion cascades
     * through all connected objects.
     * 
     * @param PConstraint $constraint
     * @return int
     */
    public function deleteCascade(PConstraint $constraint)
    {
        return $this->session->getDatabase()->deleteCascade($constraint);
    }

    /**
     * @return PObjectMapper
     */
    public function getObjectMapper()
    {
        return $this->objectMapper;
    }

}
