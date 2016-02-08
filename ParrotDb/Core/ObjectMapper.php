<?php

namespace ParrotDb\Core;

use \ParrotDb\ObjectModel\PObject;
use \ParrotDb\ObjectModel\PClass;
use \ParrotDb\ObjectModel\PObjectId;
use \ParrotDb\Utils\PUtils;
use \ParrotDb\Utils\PReflectionUtils;

/**
 * Description of ObjectMapper
 *
 * @author J. Baum
 */
class ObjectMapper {

    /**
     * @var array Holds all persisted objects in memory
     */
    private $oIdToPHPId = array();
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
    
    public function getOIdToPhpId() {
        return $this->oIdToPHPId;
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

    public function addToPersistedMemory($object, PObject $pObject) {
        $this->oIdToPHPId[spl_object_hash($object)] = $pObject;
    }

    /**
     * Creates a PObject from an arbitrary PHP-object
     * 
     * @param mixed $object
     * @param PClass $pClass
     * @return PObject
     */
    public function createObject($object, $pClass,$oid) {
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

            $pObject->addAttribute(
             $property->getName(), $this->createObjectValue($object, $property)
            );
            

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
     * @param ReflectionProperty $property
     * @return mixed
     */
    private function createObjectValue($object, $property) {

        $value = $property->getValue($object);

        
        if (PUtils::isObject($value)) {
            $value = $this->makePersistanceReady($value);
        } else if (PUtils::isArray($value)) {
            $value = $this->persistArray($value);
        }

        return $value;
    }

    /**
     * Persists an array recursively and returns a persisted array.
     * 
     * @param array $value
     * @return array
     */
    private function persistArray($value) {
        $newArr = array();

        foreach ($value as $key => $val) {
            $newArr = $this->persistValue($key, $val, PUtils::isAssoc($value),
             $newArr);
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
    private function persistValue($key, $val, $assoc, $arr) {
        if (PUtils::isObject($val)) {
            if ($assoc) {
                $arr[$key] = $this->makePersistanceReady($val);
            } else {
                $arr[] = $this->makePersistanceReady($val);
            }
        } else if (PUtils::isArray($val)) {
            if ($assoc) {
                $arr[$key] = $this->persistArray($val);
            } else {
                $arr[] = $this->persistArray($val);
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
            //return $this->oIdToPHPId[spl_object_hash($object)]->getObjectId();
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

    public function commit() {
        foreach ($this->oIdToPHPId as $pObject) {
            $this->session->getDatabase()->insert($pObject);
        }
    }

    /**
     * Instantiates a PHP object from the given PObject
     * 
     * @param PObject $pObject
     * @return Object
     */
    public function instantiate(PObject $pObject) {
        $pClass = $pObject->getClass();
        
        $reflectionClass = new \ReflectionClass("\\" . $pClass->getName());

        $instance = $reflectionClass->newInstanceWithoutConstructor();
        
        $this->instantiationLocks[$pObject->getObjectId()->getId()] = $instance;

        $this->setProperties($instance, $pObject);

        return $instance;
    }

    /**
     * Adds the attribue-values from $pObject to the given instance
     * 
     * @param Object $instance
     * @param PObject $pObject
     */
    private function setProperties($instance, PObject $pObject) {
        $pClass = $pObject->getClass();
        
        

        foreach ($pClass->getFields() as $field) {
            
            $property = $this->findProperty($pClass, $field);
            $property->setAccessible(true);

            $value = $pObject->getAttributes()[$field]->getValue();

            if ($value instanceof PObjectId) {
                $value = $this->fromPObject(
                    $this->session->getDatabase()->fetch($value)
                );
            } else if (PUtils::isArray($value)) {
                $value = $this->fromArray($value);
            }

            $property->setValue(
             $instance, $value
            );
        }
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
     * @return array
     */
    private function fromArray($arr) {
        $newArr = array();

        foreach ($arr as $key => $val) {

            if (PUtils::isAssoc($arr)) {
                $newArr[$key] = $this->mapAttribute($val);
            } else {
                $newArr[] = $this->mapAttribute($val);
            }
        }

        return $newArr;
    }

    /**
     * Maps an entry of an array depending on it's type.
     * 
     * @param mixed $attribute
     * @return mixed
     */
    public function mapAttribute($attribute) {
        if (PUtils::isObject($attribute)) {
            return $this->fromPObject(
                $this->session->getDatabase()->fetch($attribute)
            );
        } else if (PUtils::isArray($attribute)) {
            return $this->fromArray($attribute);
        }
    }
    
    /**
     * Maps a PObject to a PHP object
     * 
     * @param PObject $pObject
     * @return Object
     * @throws PException
     */
    public function fromPObject(PObject $pObject) {

        if (isset($this->instantiationLocks[$pObject->getObjectId()->getId()])) {
            //return $pObject->getObjectId();
            return $this->instantiationLocks[$pObject->getObjectId()->getId()];
        }
        
        
        
        $instance = $this->instantiate($pObject);


        $this->addToPersistedMemory($instance, $pObject);

        //$this->instantiationLocks[$pObject->getObjectId()->getId()] = false;
        unset($this->instantiationLocks[$pObject->getObjectId()->getId()]);
        
        return $instance;
    }

}
