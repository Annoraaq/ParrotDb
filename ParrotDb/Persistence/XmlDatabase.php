<?php

namespace ParrotDb\Persistence;

use \ParrotDb\ObjectModel\PObject;
use \ParrotDb\ObjectModel\PObjectId;
use \ParrotDb\Query\Constraint\PConstraint;
use \ParrotDb\Query\Constraint\PXmlConstraintProcessor;
use \ParrotDb\Utils\PUtils;
use \ParrotDb\Core\PException;
use \ParrotDb\Core\PConfig;
use \ParrotDb\Persistence\XML\XmlFileManager;

/**
 * Implements a database based on XML files.
 *
 * @author J. Baum
 */
class XmlDatabase implements Database
{

    /**
     * Processes a given constraint for a query.
     * 
     * @var PConstraintProcessor
     */
    protected $constraintProcessor;
    protected $markedForDeletion;
    protected $fileManager;
    private $config;
    private $path;
    private $name;
    private $latestObjectId;


    /**
     * @param string $path
     */
    public function __construct($path, $configPath = null)
    {

        $this->config = new PConfig($configPath);
        $this->constraintProcessor = new PXmlConstraintProcessor();
        $this->fileManager = new XmlFileManager($path);
        $this->path = $path;

        $this->name = substr(strrchr($path, '/'),1);
        
        if (!file_exists($path . '/' . $this->name . XmlFileManager::DB_INFO_FILE_ENDING)) {
            $file = fopen($path . '/' . $this->name . XmlFileManager::DB_INFO_FILE_ENDING,"w");
            fwrite($file, 0);
            fclose($file);
        }
        
        $this->readLatestObjectId();

    }
    
    private function readLatestObjectId()
    {
        $file = fopen($this->path
            . '/' . $this->name . XmlFileManager::DB_INFO_FILE_ENDING,"r");
        $this->latestObjectId = (int)fread($file, 1000);
        fclose($file);
    }
    
    private function writeLatestObjectId()
    {
        $file = fopen($this->path
            . '/' . $this->name . XmlFileManager::DB_INFO_FILE_ENDING,"w");
        fwrite($file, $this->latestObjectId);
        fclose($file);
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
    public function isPersisted(PObjectId $oid)
    {
        return $this->fileManager->isObjectStored($oid);
    }

    /**
     * @inheritDoc
     */
    public function query(PConstraint $constraint)
    {

        $this->constraintProcessor->setPersistedObjects($this->fileManager->fetchAll());

        return $this->constraintProcessor->process($constraint);
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
    public function delete(PConstraint $constraint, $forceDelete = false)
    {
        $this->constraintProcessor->setPersistedObjects($this->fileManager->fetchAll());
        $resultSet = $this->constraintProcessor->process($constraint);
       

        $toDelete = array();
        foreach ($resultSet as $elem) {
            $this->deleteSingle($elem->getClass()->getName(),
             $elem->getObjectId());
            $toDelete[$elem->getObjectId()->getId()] = $elem->getObjectId()->getId();
        }

        $this->writeLatestObjectId();
        return $resultSet;
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

    public function getRefByManager()
    {
        return new MemoryRefByManager();
    }

    public function getPath() {
        return $this->path;
    }

    /**
     * @inheritDoc
     */
    public function getFileManager() {
        return $this->fileManager;
    }

}
