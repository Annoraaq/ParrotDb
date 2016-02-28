<?php

namespace ParrotDb\Persistence\Feather;

use \ParrotDb\ObjectModel\PClass;
use \ParrotDb\Utils\PUtils;

/**
 * Feather-serializer for objects of PClass.
 *
 * @author J. Baum
 */
class FeatherClassSerializer
{

    /**
     *
     * @var PClass PClass object to serialize.
     */
    protected $pClass;

    /**
     * @param PClass $pClass PClass object to serialize.
     */
    public function setPClass(PClass $pClass)
    {
        $this->pClass = $pClass;
    }

    /**
     * Serializes a PClass as feather.
     * 
     * @return string
     */
    public function serialize()
    {
        $output = 'c[' . "'" . $this->pClass->getName() . "',";
        $output .= $this->serializeFields();
        $output .= ',';
        $output .= $this->serializeSuperclasses();
        $output .= ']';

        return $output;
    }

    private function serializeFields()
    {
        $output = 'attr{';

        $hasAttributes = false;
        foreach ($this->pClass->getFields() as $field) {
            $output .= "'" . $field . "',";
            $hasAttributes = true;
        }

        if ($hasAttributes) {
            $output = PUtils::cutLastChar($output);
        }

        $output .= '}';

        return $output;
    }

    private function serializeSuperclasses()
    {
        $output = 'sc{';

        $hasSuperclasses = false;
        foreach ($this->pClass->getSuperclasses() as $superclass) {
            $output .= "'" . $superclass . "',";
            $hasSuperclasses = true;
        }

        if ($hasSuperclasses) {
            $output = PUtils::cutLastChar($output);
        }

        $output .= '}';

        return $output;
    }

}
