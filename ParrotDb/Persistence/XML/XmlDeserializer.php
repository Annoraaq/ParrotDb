<?php

namespace ParrotDb\Persistence\Xml;

use \ParrotDb\Persistence\Deserializer;
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
    
    private $domDocument;
    
    public function __construct(\DomDocument $domDocument) {
        $this->domDocument = $domDocument;
    }
    
    public function deserialize() {
        
    }

}
