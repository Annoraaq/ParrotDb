<?php

namespace ParrotDb\ObjectModel;

use \ParrotDb\Core\Comparable;

/**
 * This class represents PHP objects in the ParrotDB object model.
 *
 * @author J. Baum
 */
class PObject implements Comparable
{

    protected $objectId;
    protected $persistent;
    protected $dirty;
    protected $class;
    protected $attributes = array();

    /**
     * @param \ParrotDb\ObjectModel\PObjectId $objectId
     */
    public function __construct(PObjectId $objectId)
    {
        $this->objectId = $objectId;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return boolean
     */
    public function getPersistent()
    {
        return $this->persistent;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @return boolean
     */
    public function isDirty()
    {
        return $this->dirty;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function addAttribute($name, $value)
    {
        $this->attributes[$name] = new PAttribute($name, $value);
    }

    /**
     * @return \ParrotDb\ObjectModel\PClass
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param \ParrotDb\ObjectModel\PClass $class
     */
    public function setClass(PClass $class)
    {
        $this->class = $class;
    }

    /**
     * @param mixed $object
     * @return boolean
     */
    public function isIdentical($object)
    {
        if ($object instanceof PObject) {
            return $object->getObjectId() === $this->objectId;
        }

        return false;
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function hasAttribute($name)
    {
        return isset($this->attributes[$name]);
    }

    /**
     * @param string $name
     * @return \ParrotDb\ObjectModel\PAttribute
     * @throws \ParrotDb\Core\PException
     */
    public function getAttribute($name)
    {
        if (!$this->hasAttribute($name)) {
            throw new \ParrotDb\Core\PException(
             "Attribute " . $name . " not found."
            );
        }
        return $this->attributes[$name];
    }

    /**
     * Checks if the given object equals the current object ignoring the object
     * id.
     * 
     * @param mixed $object
     * @return boolean
     */
    public function equalsWithoutId($object)
    {

        if (!$this->equalsGeneral($object)) {
            return false;
        }

        if (!$this->equalsAttributes($object, false)) {
            return false;
        }
        return true;
    }

    private function equalsGeneral($object)
    {
        if (!($object instanceof PObject)) {
            return false;
        }

        if ($this->persistent != $object->getPersistent()) {
            return false;
        }

        if ($this->dirty != $object->isDirty()) {
            return false;
        }

        if ($this->class->getName() != $object->getClass()->getName()) {
            return false;
        }

        if (count($this->attributes) != count($object->getAttributes())) {
            return false;
        }

        return true;
    }

    private function isObjectId($object)
    {
        return ($object instanceof PObjectId);
    }

    private function equalsAttributes($object, $withObjectId)
    {

        foreach ($this->attributes as $attribute) {

            $value = $object->getAttributes()[$attribute->getName()]->getValue();
            if (!$object->hasAttribute($attribute->getName())) {

                return false;
            } else if ($this->isObjectId($attribute->getValue()) && $withObjectId) {
                if (!$this->isObjectId($value)) {
                    return false;
                }

                if ($attribute->getValue()->getId() != $value->getId()) {
                    return false;
                }
            } else if ($value != $attribute->getValue()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function equals($object)
    {

        if (!$this->equalsGeneral($object)) {
            return false;
        }

        if (!$this->equalsAttributes($object, true)) {
            return false;
        }
        return true;

        if ($this->objectId->getId() != $object->getObjectId()->getId()) {
            return false;
        }
        
        return true;
    }

}
