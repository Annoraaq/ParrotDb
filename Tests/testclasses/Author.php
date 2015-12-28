<?php

require_once "Person.php";

/**
 * Description of Author
 *
 * @author J. Baum
 */
class Author extends Person {

    protected $name;
    protected $age;
    public $size;
    public $publication;
    public $allPublications;
    public $orderedPublications;
    public $nestedPublications;
    
    public $partner;

    public function __construct($name = "", $age = 0) {
        $this->name = $name;
        $this->age = $age;
        $this->partner = $this;
    }

    public function getName() {
        return $this->name;
    }
    
    public function setName($name) {
        $this->name = $name;
    }

    public function getAge() {
        return $this->age;
    }
    
    public function setAge($age) {
        $this->age = $age;
    }

    public function equals($object) {

        if (!parent::equals($object)) {
            return false;
        }

        if (!($object instanceof Author)) {
            return false;
        }

        if ($this->name != $object->getName()) {
            return false;
        }

        if ($this->age != $object->getAge()) {
            return false;
        }
        
        if ($this->size != $object->size) {
            return false;
        }

        if (!$this->publication->equals($object->publication)) {
            return false;
        }
        
        if ($this->partner != $this && !$this->partner->equals($object->partner)) {
            return false;
        }

        if (count($this->allPublications) != count($object->allPublications)) {
            return false;
        }

        
        foreach ($this->allPublications as $key => $val) {
            if (!isset($object->allPublications[$key])) {
                return false;
            }

            if (!$object->allPublications[$key]->equals($val)) {
                return false;
            }
        }

        foreach ($this->orderedPublications as $key => $val) {
            if (!isset($object->orderedPublications[$key])) {
                return false;
            }

            if (!$object->orderedPublications[$key]->equals($val)) {
                return false;
            }
        }

        if (count($this->nestedPublications) != count($object->nestedPublications)) {
            return false;
        }

        foreach ($this->nestedPublications as $key => $val) {
            if (!isset($object->nestedPublications[$key])) {
                return false;
            }
            
            if (count($val) != count($object->nestedPublications[$key])) {
                return false;
            }

            foreach ($val as $key2 => $val2) {
         
                //var_dump(($object->nestedPublications[$key]));
                if (!$object->nestedPublications[$key][$key2]->equals($val2)) {
                    return false;
                }
            }
        }

        return true;
    }

}
