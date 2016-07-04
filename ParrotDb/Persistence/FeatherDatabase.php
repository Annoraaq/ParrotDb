<?php

namespace ParrotDb\Persistence;

use \ParrotDb\ObjectModel\PObject;
use \ParrotDb\ObjectModel\PObjectId;
use \ParrotDb\Query\Constraint\PConstraint;
use \ParrotDb\Query\Constraint\PXmlConstraintProcessor;
use \ParrotDb\Utils\PUtils;
use \ParrotDb\Core\PException;
use \ParrotDb\Core\PConfig;
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
    private $path;
    private $name;
    private $latestObjectId;
    private $config;
    private $refByManager;


    /**
     * @param string $path
     */
    public function __construct($path, $configPath = null)
    {
        $this->config = new PConfig($configPath);

        $this->constraintProcessor = new PXmlConstraintProcessor();
        $this->fileManager = new FeatherFileManager($path, $this->config);
        $this->path = $path;
        $this->refByManager = new RefByManager($this);

        $this->name = substr(strrchr($path, '/'),1);

        $dbPath = $path . '/' . $this->name . FeatherFileManager::DB_INFO_FILE_ENDING;

        // check directory
        if (!is_dir($path)) {
            mkdir ($path, 0777, true);
        }


        if (!file_exists($dbPath)) {
            $file = fopen($dbPath, "w");
            fwrite($file, 0);
            fclose($file);
        }
        $this->readLatestObjectId();
    }


    private function getDbPath()
    {
        return $this->path
            . '/' . $this->name . FeatherFileManager::DB_INFO_FILE_ENDING;
    }
    
    private function readLatestObjectId()
    {
        $file = fopen($this->getDbPath(), "r");
        $this->latestObjectId = (int) fread($file, 1000);
        fclose($file);
    }

    private function writeLatestObjectId()
    {
        $file = fopen($this->getDbPath(), "w");
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
        return $this->fileManager->fetchConstraint($constraint);
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
     * @inheritdoc
     */
    public function delete(PConstraint $constraint, $forceDelete = false)
    {
        $resultSet = $this->fileManager->fetchConstraint($constraint);

        if ($forceDelete === false) {
            $oids = [];
            foreach ($resultSet as $pObj) {
                $oids[$pObj->getObjectId()->getId()] = $pObj->getObjectId()->getId();
            }

            foreach ($resultSet as $pObj) {
                $refBy = $this->refByManager->getRefBy($pObj->getObjectId());
                foreach ($refBy as $ref) {
                    if (!isset($oids[$ref])) {
                        throw new ReferentialIntegrityException(
                            "Object '" . $pObj->getObjectId()
                            . "'' can not be deleted. It is still referenced by object '" . $ref . "'."
                        );
                    }
                }
            }
        }

        $this->fileManager->deleteArray($resultSet);
        foreach ($resultSet as $pObj) {
            $this->refByManager->removeRefByRelations($pObj->getObjectId());
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
        $this->fileManager->delete($className, $objectId);
        $this->refByManager->removeRefByRelations($objectId);
    }

    /**
     * @inheritDoc
     */
    public function deleteCascade(PConstraint $constraint)
    {
        $this->markForDeletion = [];

        $resultSet = $this->fileManager->fetchConstraint($constraint);

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

    public function getConfig()
    {
        //$this->config = new PConfig();
        return $this->config;
        //return new PConfig();
    }


    public function getRefByManager()
    {
        return $this->refByManager;
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
