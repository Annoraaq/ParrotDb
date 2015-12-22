<?php

namespace ParrotDb\Persistence\Xml;

use \ParrotDb\Persistence\Serializer;
use \ParrotDb\ObjectModel\PObject;
use \ParrotDb\ObjectModel\PClass;
use \ParrotDb\Persistence\Xml\XmlClassSerializer;

/**
 * The XmlDeserializer handles the deserialization of an xml file into
 * PObject and PClass objects
 *
 * @author J. Baum
 */
class XmlDeserializer implements Deserializer {
    
//    /**
//     * @var XmlClassSerializer
//     */
//    protected $classSerializer;
//    
//    /**
//     *
//     * @var XmlObjectSerializer
//     */
//    protected $objectSerializer;
//    
//    /**
//     * @param \DOMDocument $domDocument DOMDocument to serialize into.
//     */
//    public function __construct(\DOMDocument $domDocument = null) {
//        if ($domDocument == null) {
//            $domDocument = new \DOMDocument;
//        }
//      
//        $this->classSerializer = new XmlClassSerializer($domDocument);
//        $this->objectSerializer = new XmlObjectSerializer($domDocument);
//    }
//    
//    /**
//     * @param \DOMDocument $domDocument DOMDocument to serialize into.
//     */
//    public function setDomDocument(\DOMDocument $domDocument) {
//         $this->classSerializer->setDomDocument($domDocument);
//         $this->objectSerializer->setDomDocument($domDocument);
//    }
//    
//    /**
//     * Serializes the given PObject into XML
//     * 
//     * @param PObject $pObject PObject to serialize
//     * @return \DOMDocument
//     */
//    public function serialize(PObject $pObject) {
//        $this->objectSerializer->setPObject($pObject);
//        return $this->objectSerializer->createObjectElement();
//    }
//    
//    /**
//     * Serializes a PClass as XML.
//     * 
//     * @param PClass $pClass
//     * @return \DOMDocument
//     */
//    public function serializeClass(PClass $pClass) {
//        $this->classSerializer->setPClass($pClass);
//        return $this->classSerializer->serialize();
//    } 
//    
//    
//    
   

}
