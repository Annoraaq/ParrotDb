<?php

namespace ParrotDb\Core;

use \ParrotDb\ObjectModel\PClass;
use \ParrotDb\Utils\PReflectionUtils;


/**
 * Description of Mapper
 *
 * @author J. Baum
 */
class ClassMapper {
    
    private $pClass;
    
    public function createClass($object) {
        $reflector = new \ReflectionClass(get_class($object));

        $this->pClass = new PClass($reflector->getName());

        $this->addParentClasses($reflector);
        $this->addFields($reflector);

        return $this->pClass;
    }
    
    private function addParentClasses($reflector) {

        while ($parent = $reflector->getParentClass()) {
            $this->pClass->addSuperclass($parent->getName());
            $reflector = $parent;
        }
    }

    private function addFields($reflector) {
        $properties = $reflector->getProperties();

        foreach ($properties as $property) {

            if (PReflectionUtils::isUnaccessible($property)) {
                $property->setAccessible(true);
            }

            $this->pClass->addField($property->getName());
        }
 
        if ($reflector->getParentClass()) {
            $this->addFields(
             new \ReflectionClass(
             $reflector->getParentClass()->getName()
             ), $this->pClass
            );
        }
    }
  
}
