<?php

namespace ParrotDb\Persistence;

use \ParrotDb\ObjectModel\PObject;
use \ParrotDb\ObjectModel\PObjectId;
use \ParrotDb\Query\Constraint\PConstraint;
use \ParrotDb\Query\Constraint\PResultSet;

/**
 * Description of Database
 *
 * @author J. Baum
 */
interface Database {
 
    /**
     * Fetches an object by object id
     * 
     * @param PObjectId $oid
     * @return PObject
     * @throws PException
     */
    public function fetch(PObjectId $oid);

    /**
     * Inserts an object into the database.
     * 
     * @param PObject $pObject
     */
    public function insert(PObject $pObject);

    /**
     * Checks, whether an object with the given object id is in the database.
     * 
     * @param PObjectId $oid
     * @return bool
     */
    public function isPersisted(PObjectId $oid);
    
    /**
     * Queries the database.
     * 
     * @param PConstraint $constraint
     * @return PResultSet
     */
    public function query(PConstraint $constraint);
    
    /**
     * Queries and deletes from the database and returns the amount
     * of deleted objects.
     * 
     * @param PConstraint $constraint
     * @return int
     */
    public function delete(PConstraint $constraint);
    

    /**
     * Queries and deletes from the database where the deletion cascades
     * through all connected objects.
     * 
     * @param PConstraint $constraint
     */
    public function deleteCascade(PConstraint $constraint);
    
    /**
     * Returns the current latest object ID and increases it by one.
     * 
     * @return \ParrotDb\ObjectModel\PObjectId
     */
    public function assignObjectId();

    
}
