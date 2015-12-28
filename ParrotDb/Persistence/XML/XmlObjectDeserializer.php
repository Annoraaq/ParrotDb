<?php

namespace ParrotDb\Persistence\Xml;

use \ParrotDb\Persistence\Deserializer;
use \ParrotDb\ObjectModel\PClass;
use \ParrotDb\ObjectModel\PObject;
use \ParrotDb\ObjectModel\PObjectId;
use \ParrotDb\Utils\PXmlUtils;

/**
 * The XmlClassDeserializer handles the deserialization of an xml file into
 * a PObject object
 *
 * @author J. Baum
 */
class XmlObjectDeserializer implements Deserializer {

    private $object;
    private $pClass;

    /**
     * @param \DOMElement $object
     * @param PClass $pClass
     */
    public function __construct(\DOMElement $object, PClass $pClass) {
        $this->object = $object;
        $this->pClass = $pClass;
    }

    /**
     * @return PObject
     */
    public function deserialize() {
        $id = PXmlUtils::firstElemByTagName($this->object, "id")->nodeValue;
        $pObject = new PObject(new PObjectId($id));
        $pObject->setClass($this->pClass);


        $attributes = PXmlUtils::firstElemByTagName($this->object, "attributes")
         ->getElementsByTagName("attribute");

        foreach ($attributes as $attribute) {
            $valElem = PXmlUtils::firstElemByTagName($attribute, "value");
            if (PXmlUtils::equalsFirstChildName($valElem, "objectId")) {
                $value = new PObjectId($valElem->firstChild->nodeValue);
            } else if (PXmlUtils::equalsFirstChildName($valElem, "array")) {
                $value = $this->parseArray($valElem->firstChild);
            } else {
                $value = $valElem->nodeValue;
            }

            $pObject->addAttribute(
             PXmlUtils::firstElemByTagName($attribute, "name")->nodeValue,
             $value
            );
        }

        return $pObject;
    }

    private function parseArray(\DOMElement $arrayElem) {
        $array = array();
        
        foreach (PXmlUtils::childsByTagName($arrayElem, "elem") as $elemElem) {
            $key = PXmlUtils::firstElemByTagName($elemElem, "key")->nodeValue;
            $valElem = PXmlUtils::firstElemByTagName($elemElem, "value");
            if (PXmlUtils::equalsFirstChildName($valElem, "objectId")) {
                $val = new PObjectId($valElem->firstChild->nodeValue);
            } else if (PXmlUtils::equalsFirstChildName($valElem, "array")) {
                $val = $this->parseArray($valElem->firstChild);
            } else {
                $val = $valElem->nodeValue;
            }

            $array[$key] = $val;
        }

        return $array;
    }

}
