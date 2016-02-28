<?php

namespace ParrotDb\ObjectModel;

/**
 * This class represents a PHP class.
 *
 * @author J. Baum
 */
class PClass
{

    protected $name;
    protected $fields = array();
    protected $extent = array();
    protected $superclasses = array();

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $name
     */
    public function addField($name)
    {
        $this->fields[$name] = $name;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getExtent()
    {
        return $this->extent;
    }

    /**
     * Deletes the current extent from memory.
     */
    public function resetExtent()
    {
        $this->extent = array();
    }

    /**
     * @param \ParrotDb\ObjectModel\PObjectId $id
     * @param object $member
     */
    public function addExtentMember(PObjectId $id, $member)
    {
        $this->extent[$id->getId()] = $member;
    }

    /**
     * @param \ParrotDb\ObjectModel\PObjectId $id
     * @return boolean
     */
    public function isInExtent(PObjectId $id)
    {
        if (isset($this->extent[$id->getId()])) {
            return true;
        }

        return false;
    }

    /**
     * @param string $superclass
     */
    public function addSuperclass($superclass)
    {
        $this->superclasses[$superclass] = $superclass;
    }

    /**
     * @return array
     */
    public function getSuperclasses()
    {
        return $this->superclasses;
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function hasSuperclass($name)
    {
        return isset($this->superclasses[$name]);
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function hasField($name)
    {
        return isset($this->fields[$name]);
    }

    /**
     * @inheritdoc
     */
    public function equals($object)
    {
        if (!($object instanceof PClass)) {
            return false;
        }

        if ($object->getName() != $this->getName()) {
            return false;
        }

        foreach ($this->getFields() as $field) {
            $found = false;
            foreach ($object->getFields() as $field2) {
                if ($field == $field2) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return false;
            }
        }

        foreach ($this->getSuperclasses() as $superclass) {
            $found = false;
            foreach ($object->getSuperclasses() as $superclass2) {
                if ($superclass == $superclass2) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return false;
            }
        }

        return true;
    }

}
