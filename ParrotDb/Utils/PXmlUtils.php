<?php

namespace ParrotDb\Utils;

/**
 * Utils for XML-parsing
 *
 * @author J. Baum
 */
class PXmlUtils {

    /**
     * @param \DOMElement $dom
     * @param string $name
     * @return \DOMElement
     */
    public static function firstElemByTagName(\DOMElement $dom, $name) {
        foreach ($dom->getElementsByTagName($name) as $objects) {
            return $objects;
        }
        
        return null;
    }
    
    /**
     * Returns a list of direct child nodes with the given tag name
     * 
     * @param \DOMElement $domElem
     * @param string $name
     * @return array
     */
    public static function childsByTagName(\DOMElement $domElem, $name) {
        
        $outputList = array();
        foreach ($domElem->childNodes as $childNode) {
            if ($childNode->nodeName == $name) {
                $outputList[] = $childNode;
            }
        }
        
        return $outputList;
    }
    
    
    /**
     * Checks, if the first child of the given element has the given name
     * 
     * @param \DOMElement $domElem
     * @param string $name
     * @return boolean
     */
    public static function equalsFirstChildName(\DOMElement $domElem, $name) {
        if ($domElem->firstChild == null) {
            return false;
        }
        
        return ($domElem->firstChild->nodeName == $name);
    }


}
