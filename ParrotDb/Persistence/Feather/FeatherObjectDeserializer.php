<?php

namespace ParrotDb\Persistence\Feather;

use \ParrotDb\Persistence\Deserializer;
use \ParrotDb\ObjectModel\PClass;
use \ParrotDb\ObjectModel\PObject;
use \ParrotDb\ObjectModel\PObjectId;

/**
 * This class handles the deserialization of a feather file into
 * a PObject object
 *
 * @author J. Baum
 */
class FeatherObjectDeserializer implements Deserializer
{

    private $pClass;
    private $input;
    private $length;

    /**
     * @param PClass $pClass
     */
    public function __construct(PClass $pClass)
    {
        $this->pClass = $pClass;
    }

    /**
     * @param string $input
     */
    public function setInput($input)
    {
        $this->input = $input;
        $this->length = mb_strlen($input);
    }

    private function getObjectId()
    {
        $idEndPos = mb_strpos($this->input, ",");

        return mb_substr($this->input, 1, $idEndPos - 1);
    }

    
    private function parseAttribute($offset)
    {
        $attrParser = new AttributeParser();
        $attrParser->setInput($this->input);
        return $attrParser->parse($offset);
    }

    /**
     * @return PObject
     */
    public function deserialize()
    {

        $pObject = new PObject(new PObjectId($this->getObjectId()));
        $pObject->setClass($this->pClass);

        $idEndPos = strpos($this->input, ",");
        $lengthEndPos = strpos($this->input, ",", $idEndPos + 1);


        $offset = $lengthEndPos;
        while (($offset < ($this->length - 2))) {

            $attribute = $this->parseAttribute($offset);

            $pObject->addAttribute($attribute['name'], $attribute['value']);
            $offset = $attribute['offset'];
        }

        return $pObject;
    }

}
