<?php

namespace ParrotDb\ObjectModel;

use \ParrotDb\Core\Comparable;

/**
 * Description of PObject
 *
 * @author J. Baum
 */
class PObject implements Comparable {

    protected $objectId;
    protected $persistent;
    protected $dirty;
    protected $class;
    protected $attributes = array();

    public function __construct($objectId) {
        $this->objectId = $objectId;
    }

    public function getAttributes() {
        return $this->attributes;
    }

    public function getPersistent() {
        return $this->persistent;
    }

    public function getObjectId() {
        return $this->objectId;
    }

    public function getDirty() {
        return $this->dirty;
    }

    public function addAttribute($name, $value) {
        $this->attributes[$name] = new PAttribute($name, $value);
    }

    public function getClass() {
        return $this->class;
    }

    public function setClass(PClass $class) {
        $this->class = $class;
    }

    public function isIdentical($object) {
        if ($object instanceof PObject) {
            return $object->getObjectId() === $this->objectId;
        }

        return false;
    }

    public function hasAttribute($name) {
        return isset($this->attributes[$name]);
    }
    
    public function getAttribute($name) {
        if (!$this->hasAttribute($name)) {
            throw new \ParrotDb\Core\PException("Attribute " . $name . " not found.");
        }
        return $this->attributes[$name];
    }

    public function equalsWithoutId($object) {

        if (!$this->equalsGeneral($object)) {
            return false;
        }

        if (!$this->equalsAttributes($object, false)) {
            return false;
        }
        return true;
    }

    private function equalsGeneral($object) {
        if (!($object instanceof PObject)) {
            return false;
        }

        if ($this->persistent != $object->getPersistent()) {
            return false;
        }

        if ($this->dirty != $object->getDirty()) {
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

    private function isObjectId($object) {
        return ($object instanceof PObjectId);
    }

    private function equalsAttributes($object, $withObjectId) {

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

    public function equals($object) {

        if (!$this->equalsGeneral($object)) {
            return false;
        }

        if (!$this->equalsAttributes($object, true)) {
            return false;
        }
        return true;

        if ($this->objectId->getId() != $object->getObjectId()->getId()) {
            echo "(" . $object->getObjectId()->getId() . ")";
            return false;
        }



        return true;
    }

}
