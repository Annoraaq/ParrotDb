<?php

namespace ParrotDb\Persistence;

use \ParrotDb\ObjectModel\PObject;
use \ParrotDb\ObjectModel\PObjectId;
use \ParrotDb\Query\Constraint\PConstraint;
use \ParrotDb\Query\Constraint\PMemoryConstraintProcessor;
use \ParrotDb\Utils\PUtils;
use \ParrotDb\Core\PException;
use \ParrotDb\Core\PConfig;
use \ParrotDb\Persistence\RefInt\MemoryRefManager;

/**
 * Description of PMemoryDatabase
 *
 * @author J. Baum
 */
class PMemoryDatabase implements Database {
    
    /**
     * Holds all persisted objects and simulates the database in memory.
     * Will be replaced when persistence is implemented.
     * 
     * @var array 
     */
    protected $persistedObjects; 
    
    /**
     * Processes a given constraint for a query.
     * 
     * @var PConstraintProcessor
     */
    protected $constraintProcessor;
    
    protected $markedForDeletion;
    
    private $latestObjectId;
    
    private $indexes;
    
    private $config;
    
    public function __construct($configPath = null) {
        $this->config = new PConfig($configPath);
        $this->constraintProcessor = new PMemoryConstraintProcessor();
        $this->latestObjectId = 0;
        $this->indexes = array();
    }
    
    /**
     * @inheritDoc
     */
    public function fetch(PObjectId $oid) {
        if ($this->isPersisted($oid)) {
           return $this->persistedObjects[$oid->getId()];
        } else {
            throw new PException("Object with id " . $oid->getId() . " not persisted.");
        }
    }
    
    /**
     * @inheritDoc
     */
    public function insert(PObject $pObject) {
        $this->persistedObjects[$pObject->getObjectId()->getId()] = $pObject;
    }
    
    /**
     * @inheritDoc
     */
    public function isPersisted(PObjectId $oid) {
        return (isset($this->persistedObjects[$oid->getId()]));
    }

    /**
     * @inheritDoc
     */
    public function query(PConstraint $constraint) {
        
        $this->constraintProcessor->setPersistedObjects($this->persistedObjects);
        
        return $this->constraintProcessor->process($constraint);
    }
    
    /**
     * Returns the amount of saved objects in the database.
     * 
     * @return int
     */
    public function size() {
        return count($this->persistedObjects);
    }

    /**
     * @inheritDoc
     */
    public function delete(PConstraint $constraint, $forceDelete = false) {
        $this->constraintProcessor->setPersistedObjects($this->persistedObjects);
        
        $resultSet = $this->constraintProcessor->process($constraint);
        
        foreach ($resultSet as $elem) {
            $this->deleteSingle($elem->getObjectId());
        }
        
        return $resultSet;
    }

    /**
     * Deletes a single object from the database having the given object id.
     * 
     * @param PObjectId $objectId
     */
    public function deleteSingle(PObjectId $objectId) {
        if ($this->isPersisted($objectId)) {
            unset($this->persistedObjects[$objectId->getId()]);
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteCascade(PConstraint $constraint) {
        $this->constraintProcessor->setPersistedObjects($this->persistedObjects);
        $this->markForDeletion = [];
        $resultSet = $this->constraintProcessor->process($constraint);
        foreach ($resultSet as $elem) {
            $this->deleteCascadeSingle($elem);
        }
     
        $amount = count($this->markedForDeletion);
        
        foreach ($this->markedForDeletion as $oid) {
            $this->deleteSingle($oid);
        }
            
        
        return $amount;
    }

    /**
     * Deletes a single object from the database having the given object id and
     * performs a cascading delete.
     * 
     * @param PObject $pObject
     */
    private function deleteCascadeSingle(PObject $pObject) {
        
        if (isset($this->markedForDeletion[$pObject->getObjectId()->getId()])) {
            return;
        }
        $this->markedForDeletion[$pObject->getObjectId()->getId()] = $pObject->getObjectId();

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
    private function deleteCascadeArray($arr) {
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
    public function assignObjectId() {
        $objectId = $this->latestObjectId;
        $this->latestObjectId++;
        return new PObjectId($objectId);
    }

    public function addIndex($className, $attributeName)
    {
        $index = array();
        
        foreach ($this->persistedObjects as $id => $pObject) {
            if ($pObject->getClass()->getName() == $className) {
                $index[$pObject->getAttribute($attributeName)->getValue()][] = $pObject->getObjectId()->getId();
            }
        }
        
        $this->indexes[] = array(
         'className' => $className,
         'attributeName' => $attributeName,
         'index' => $index
        );
    }

    public function insertArray($arr)
    {
        foreach ($arr as $temp) {
            foreach ($temp as $te) {
                $this->insert($te);
            }
        }
    }
    
    public function getConfig()
    {
        return $this->config;
    }

    public function getRefManager()
    {
        return new MemoryRefManager();
    }

    public function getPath() {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getFileManager() {
        return null;
    }


}
