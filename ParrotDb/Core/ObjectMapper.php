<?php

namespace ParrotDb\Core;

use \ParrotDb\ObjectModel\PObject;
use \ParrotDb\ObjectModel\PClass;
use \ParrotDb\ObjectModel\PObjectId;
use \ParrotDb\Utils\PUtils;
use \ParrotDb\Utils\PReflectionUtils;

/**
 * This class is responsible for creating a PObject from a given PHP object
 *
 * @author J. Baum
 */
class ObjectMapper {

    /**
     * @var array Holds all persisted objects in memory
     */
    private $oIdToPHPId = array();
    private $phpIdToOId = array();
    private $session;
    private $classMapper;
    
    private $instantiationLocks;

    /**
     * 
     * @param type $session
     */
    public function __construct($session) {
        $this->session = $session;
        $this->classMapper = new ClassMapper();
    }
    
    /**
     * @return array All persisted objects in memory
     */
    public function getOIdToPhpId() {
        return $this->oIdToPHPId;
    }
    
    /**
     * @return array All persisted objects in memory
     */
    public function getPhpIdToOId() {
        return $this->phpIdToOId;
    }

    /**
     * Checks, whether $object is already persisted in memory. It does not
     * check the database. This method is used to avoid infinite recursion due
     * to "persistence by reachability".
     * 
     * @param mixed $object
     */
    public function isAlreadyPersistedInMemory($object) {
        return isset($this->oIdToPHPId[spl_object_hash($object)]);
    }
    

    /**
     * @param object $object
     * @param PObject $pObject
     */
    public function addToPersistedMemory($object, PObject $pObject) {
        $this->oIdToPHPId[spl_object_hash($object)] = $pObject;
        $this->phpIdToOId[$pObject->getObjectId()->getId()] = spl_object_hash($object);
    }

    /**
     * Creates a PObject from an arbitrary PHP-object
     * 
     * @param mixed $object
     * @param PClass $pClass
     * @param PObjectId $oid
     * @return PObject
     */
    private function createObject($object, PClass $pClass, PObjectId $oid) {
        $pObject = new PObject($oid);
        $pObject->setClass($pClass);
        $this->addAttributes(
            new \ReflectionClass(get_class($object)),
            $object,
            $pObject
        );

        $pClass->addExtentMember($pObject->getObjectId(), $pObject);

        return $pObject;
    }

    /**
     * Adds all attributes of $object to $pObject
     * 
     * @param ReflectionClass $reflector
     * @param mixed $object
     * @param PObject $pObject
     */
    private function addAttributes($reflector, $object, $pObject) {

        $properties = $reflector->getProperties();

        foreach ($properties as $property) {

            if (PReflectionUtils::isUnaccessible($property)) {
                $property->setAccessible(true);
            }
            
            if (!$this->session->getDatabase()->getConfig()->isIgnoreStatic() ||
                !$property->isStatic()) {
                    $pObject->addAttribute(
                     $property->getName(), $this->createObjectValue($object, $property, $pObject)
                    );
            
            }
        }

        if ($reflector->getParentClass()) {
            $this->addAttributes(
             new \ReflectionClass(
             $reflector->getParentClass()->getName()
             ), $object, $pObject
            );
        }
    }

    /**
     * Returns a persistent-ready version of an object property.
     * 
     * @param mixed $object
     * @param \ReflectionProperty $property
     * @return mixed
     */
    private function createObjectValue($object, \ReflectionProperty $property, PObject $parentObject) {

        $value = $property->getValue($object);

        if ($value === $object) {
            $value = $this->oIdToPHPId[spl_object_hash($object)]->getObjectId();
        } else if (PUtils::isObject($value)) {

            $value = $this->makePersistanceReady($value);

            $this->session->getDatabase()->getRefByManager()->addRefByRelation(
                $parentObject->getObjectId(),
                $value
            );

        } else if (PUtils::isArray($value)) {
            $value = $this->persistArray($value, $parentObject->getObjectId());
        } else if (PUtils::isString($value)) {
            $value = PUtils::escape($value);
        }

        return $value;
    }

    /**
     * Persists an array recursively and returns a persisted array.
     *
     * @param array $value
     * @param PObjectId $parentObjectId
     * @return array
     */
    private function persistArray($value, PObjectId $parentObjectId) {
        $newArr = array();

        foreach ($value as $key => $val) {
            $newArr = $this->persistValue($key, $val, PUtils::isAssoc($value),
             $newArr, $parentObjectId);
        }

        return $newArr;
    }

    /**
     * Persists a value recursively and saves it in the given array
     * which is returned at the end.
     * 
     * @param mixed $key
     * @param mixed $val
     * @param bool $assoc
     * @param array $arr
     * @return array
     */
    private function persistValue($key, $val, $assoc, $arr, PObjectId $parentObjectId) {
        if (PUtils::isObject($val)) {
            $pObject = $this->makePersistanceReady($val);
            if ($assoc) {
                $arr[$key] = $pObject;
            } else {
                $arr[] = $pObject;
            }

            $this->session->getDatabase()->getRefByManager()->addRefByRelation(
                $parentObjectId,
                $pObject
            );
        } else if (PUtils::isArray($val)) {
            if ($assoc) {
                $arr[$key] = $this->persistArray($val, $parentObjectId);
            } else {
                $arr[] = $this->persistArray($val, $parentObjectId);
            }
        }

        return $arr;
    }

    /**
     * Makes an object persistence ready
     * 
     * @param mixed $object
     * @return int object-id
     */
    public function makePersistanceReady($object) {

        $hasUsedObjectId = false;
        
        if ($this->isAlreadyPersistedInMemory($object)) {
            $hasUsedObjectId = true;
            $id = $this->oIdToPHPId[spl_object_hash($object)]->getObjectId();
        }

        
        if (!$hasUsedObjectId) {
            $id = $this->session->assignObjectId();
        }
        $this->addToPersistedMemory($object, new PObject($id));

        $pClass = $this->classMapper->createClass($object);

        $pObject = $this->createObject(
            $object,
            $pClass,
            $id
        );
        

        $this->addToPersistedMemory($object, $pObject);

        return $pObject->getObjectId();
    }

    /**
     * Commits changes to persisted objects to the database.
     */
    public function commit() {
        $arr= array();
        foreach ($this->oIdToPHPId as $key => $pObject) {     
            $arr[$pObject->getClass()->getName()][] = $pObject;
            unset($this->oIdToPHPId[$key]);
            unset($this->phpIdToOId[$pObject->getObjectId()->getId()]);
        }
        
        $this->session->getDatabase()->insertArray($arr);
    }

    /**
     * Instantiates a PHP object from the given PObject
     * 
     * @param PObject $pObject
     * @param int $depth
     * @return Object
     */
    public function instantiate(PObject $pObject, $depth) {
        $pClass = $pObject->getClass();
        
        $reflectionClass = new \ReflectionClass("\\" . $pClass->getName());

        $instance = $reflectionClass->newInstanceWithoutConstructor();
        
        $this->instantiationLocks[$pObject->getObjectId()->getId()] = $instance;

        $this->setProperties($instance, $pObject, $depth);

        return $instance;
    }

    /**
     * Adds the attribue-values from $pObject to the given instance
     * 
     * @param Object $instance
     * @param PObject $pObject
     * @param int $depth
     */
    private function setProperties($instance, PObject $pObject, $depth) {
        $pClass = $pObject->getClass();
        
        foreach ($pClass->getFields() as $field) {
            
            $property = $this->findProperty($pClass, $field);
            $property->setAccessible(true);
            
            if ($this->session->getDatabase()->getConfig()->isIgnoreStatic()
                && $property->isStatic()) {
                continue;
            }

            $value = $pObject->getAttributes()[$field]->getValue();

            if ($value instanceof PObjectId) {
                
                if ($this->isInActivationDepth($depth)) {
                    $value = $this->fromPObject(
                        $this->session->getDatabase()->fetch($value), $depth + 1
                    );
                }
            } else if (PUtils::isArray($value)) {
                $value = $this->fromArray($value, $depth + 1);
            } else if (PUtils::isString($value)) {
                $value = PUtils::unescape($value);
            }

            $property->setValue(
             $instance, $value
            );
        }
    }
    
    private function isInActivationDepth($depth) {
        $activationDepth = $this->session->getDatabase()
                    ->getConfig()->getActivationDepth();
        
        return (($activationDepth == -1) || ($depth < $activationDepth));
    }

    /**
     * Returns the Reflection-property of $pClass with name $field. The
     * superclasses of $pClass are searched as well.
     * 
     * @param PClass $pClass
     * @param String $field
     * @return \ReflectionProperty
     */
    private function findProperty(PClass $pClass, $field) {
        $instanceReflector = new \ReflectionClass($pClass->getName());

        if (!$instanceReflector->hasProperty($field)) {

            foreach ($pClass->getSuperclasses() as $superclass) {
                $newInstanceReflector = new \ReflectionClass($superclass);
                if ($newInstanceReflector->hasProperty($field)) {
                    return $newInstanceReflector->getProperty($field);
                }
            }
        }

        return $instanceReflector->getProperty($field);
    }

    /**
     * Recursively map all entries of the given array.
     * 
     * @param array $arr
     * @param int $depth
     * @return array
     */
    private function fromArray($arr, $depth = 0) {
        $newArr = array();

        foreach ($arr as $key => $val) {

            if (PUtils::isAssoc($arr)) {
                $newArr[$key] = $this->mapAttribute($val, $depth+1);
            } else {
                $newArr[] = $this->mapAttribute($val, $depth+1);
            }
        }

        return $newArr;
    }

    /**
     * Maps an entry of an array depending on it's type.
     * 
     * @param mixed $attribute
     * @param int $depth
     * @return mixed
     */
    public function mapAttribute($attribute, $depth = 0) {
        if (PUtils::isObject($attribute)) {
            
            if ($this->isInActivationDepth($depth)) {
                return $this->fromPObject(
                    $this->session->getDatabase()->fetch($attribute), $depth+1
                );
            } else {
                return $attribute;
            }
        } else if (PUtils::isArray($attribute)) {
            return $this->fromArray($attribute, $depth + 1);
        } else if (PUtils::isString($attribute)) {
            return PUtils::unescape($attribute);
        }
    }
    
    /**
     * Maps a PObject to a PHP object
     * 
     * @param PObject $pObject
     * @return Object
     * @throws PException
     */
    public function fromPObject(PObject $pObject, $depth = 0) {

        if (isset($this->instantiationLocks[$pObject->getObjectId()->getId()])) {
            return $this->instantiationLocks[$pObject->getObjectId()->getId()];
        } 

        $instance = $this->instantiate($pObject, $depth);
        unset($this->instantiationLocks[$pObject->getObjectId()->getId()]);
        
        return $instance;
    }

}
