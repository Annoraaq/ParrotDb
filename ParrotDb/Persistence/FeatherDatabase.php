<?php

namespace ParrotDb\Persistence;

use \ParrotDb\ObjectModel\PObject;
use \ParrotDb\ObjectModel\PObjectId;
use \ParrotDb\Query\Constraint\PConstraint;
use \ParrotDb\Query\Constraint\PXmlConstraintProcessor;
use \ParrotDb\Utils\PUtils;
use \ParrotDb\Core\PException;
use \ParrotDb\Persistence\XML\XmlFileManager;
use \ParrotDb\Persistence\Feather\FeatherFileManager;

/**
 * Implements a database based on Feather files.
 *
 * @author J. Baum
 */
class FeatherDatabase implements Database
{

    /**
     * Processes a given constraint for a query.
     * 
     * @var PConstraintProcessor
     */
    protected $constraintProcessor;
    protected $markedForDeletion;
    protected $fileManager;
    
    private $name;
    private $latestObjectId;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->constraintProcessor = new PXmlConstraintProcessor();
        $this->fileManager = new FeatherFileManager($name);
        $this->name = $name;
        
        if (!file_exists(FeatherFileManager::DB_PATH . $name . '/' . $name . FeatherFileManager::DB_FILE_ENDING)) {
            $file = fopen(FeatherFileManager::DB_PATH . $name . '/' . $name . FeatherFileManager::DB_FILE_ENDING,"w");
            fwrite($file, 0);
            fclose($file);
        }
        
        $this->readLatestObjectId();

    }
    
    private function readLatestObjectId()
    {
        $file = fopen(FeatherFileManager::DB_PATH . $this->name . '/' . $this->name . FeatherFileManager::DB_FILE_ENDING,"r");
        $this->latestObjectId = (int)fread($file, 1000);
        fclose($file);
    }
    
    private function writeLatestObjectId()
    {
        $file = fopen(FeatherFileManager::DB_PATH . $this->name . '/' . $this->name . FeatherFileManager::DB_FILE_ENDING,"w");
        if ($file) {
            fwrite($file, $this->latestObjectId);
            fclose($file);
        }
    }

    /**
     * @inheritDoc
     */
    public function fetch(PObjectId $oid)
    {
        if ($this->isPersisted($oid)) {
            return $this->fileManager->fetch($oid);
        } else {
            throw new PException("Object with id " . $oid->getId() . " not persisted.");
        }
    }

    /**
     * @inheritDoc
     */
    public function insert(PObject $pObject)
    {
        $this->fileManager->storeObject($pObject);
        $this->writeLatestObjectId();
    }
    
    /**
     * @inheritDoc
     */
    public function insertArray($arr)
    {
//        foreach ($arr as $temp) {
//            foreach ($temp as $te) {
//                $this->insert($te);
//            }
//            
//        }
        
//        echo "\ncounter:$counter\n";
        $this->fileManager->storeObjects($arr);
        $this->writeLatestObjectId();
    }

    /**
     * @inheritDoc
     */
    public function isPersisted(PObjectId $oid)
    {
        $res = $this->fileManager->isObjectStored($oid);
        return $res;
    }

    /**
     * @inheritDoc
     */
    public function query(PConstraint $constraint)
    {

        // :performance
        // do not fetch all!
       // $this->constraintProcessor->setPersistedObjects($this->fileManager->fetchAll());
        return $this->fileManager->fetchConstraint($constraint);

        //return $this->constraintProcessor->process($constraint);
    }

    /**
     * Returns the amount of saved objects in the database.
     * 
     * @return int
     */
    public function size()
    {
        return count($this->persistedObjects);
    }

    /**
     * @inheritDoc
     */
    public function delete(PConstraint $constraint)
    {
        
        // :performance
        // do not fetch all!
        $this->constraintProcessor->setPersistedObjects($this->fileManager->fetchAll());
        $resultSet = $this->constraintProcessor->process($constraint);

        $toDelete = array();
        foreach ($resultSet as $elem) {
            $this->deleteSingle($elem->getClass()->getName(),
             $elem->getObjectId());
            $toDelete[$elem->getObjectId()->getId()] = $elem->getObjectId()->getId();
        }

        $this->writeLatestObjectId();
        return count($toDelete);
    }

    /**
     * Deletes a single object from the database having the given object id.
     * 
     * @param string $className
     * @param PObjectId $objectId
     */
    public function deleteSingle($className, PObjectId $objectId)
    {
        if ($this->isPersisted($objectId)) {
            $this->fileManager->delete($className, $objectId);
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteCascade(PConstraint $constraint)
    {
        $this->constraintProcessor->setPersistedObjects($this->fileManager->fetchAll());
        $this->markForDeletion = [];
        $resultSet = $this->constraintProcessor->process($constraint);
        foreach ($resultSet as $elem) {
            $this->deleteCascadeSingle($elem);
        }

        $amount = count($this->markedForDeletion);

        foreach ($this->markedForDeletion as $pObject) {
            $this->deleteSingle($pObject->getClass()->getName(),
             $pObject->getObjectId());
        }

        return $amount;
    }

    /**
     * Deletes a single object from the database having the given object id and
     * performs a cascading delete.
     * 
     * @param PObject $pObject
     */
    private function deleteCascadeSingle(PObject $pObject)
    {
        if (isset($this->markedForDeletion[$pObject->getObjectId()->getId()])) {
            return;
        }
        
        $this->markedForDeletion[$pObject->getObjectId()->getId()] = $pObject;

        foreach ($pObject->getAttributes() as $attr) {
            if ($attr->getValue() instanceof PObjectId) {
                $this->deleteCascadeSingle($this->fetch($attr->getValue()));
            } else if (PUtils::isArray($attr->getValue())) {
                $this->deleteCascadeArray($attr->getValue());
            }
        }
    }

    /**
     * Performs a cascading delete on an array.
     * 
     * @param array $arr
     */
    private function deleteCascadeArray($arr)
    {
        foreach ($arr as $val) {
            if (PUtils::isArray($val)) {
                $this->deleteCascadeArray($val);
            } else if ($val instanceof PObjectId) {
                $this->deleteCascadeSingle($this->fetch($val));
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function assignObjectId()
    {
        $objectId = $this->latestObjectId;
        $this->latestObjectId++;
        return new PObjectId($objectId);
    }

    public function addIndex($className, $attributeName)
    {
        
    }

}
