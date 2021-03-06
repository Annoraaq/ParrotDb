<?php

namespace ParrotDb\Persistence;

use \ParrotDb\ObjectModel\PObject;
use \ParrotDb\ObjectModel\PObjectId;
use \ParrotDb\Query\Constraint\PConstraint;
use \ParrotDb\Query\Constraint\PResultSet;

/**
 * Interface for the different types of databases.
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
     * Inserts an array of objects into the database.
     * 
     * @param array $arr
     */
    public function insertArray($arr);

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
     * @param bool $forceDelete Force deletion even if referential integrity is violated
     * @return int
     */
    public function delete(PConstraint $constraint, $forceDelete = false);
    

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
    
    /**
     * Adds an index to the database.
     * 
     * @param string $className
     * @param string $attributeName
     */
    public function addIndex($className, $attributeName);


    /**
     * Returns the database config
     *
     * @return \ParrotDb\Core\PConfig
     */
    public function getConfig();

    /**
     * Returns the RefManagers
     *
     * @return \ParrotDb\Persistence\RefManager
     */
    public function getRefManager();

    /**
     * Returns the directory of the database
     *
     * @return string
     */
    public function getPath();

    /**
     * Returns the file manager of the database
     *
     * @return mixed
     */
    public function getFileManager();
    
    
}
