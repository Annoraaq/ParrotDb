<?php

namespace ParrotDb\Utils;

/**
 * Description of PReflectionUtils
 *
 * @author J. Baum
 */
class PReflectionUtils {

    /**
     * Checks whether the given reflection property is private or protected
     * 
     * @param \ReflectionProperty $property
     * @return bool
     */
    public static function isUnaccessible(\ReflectionProperty $property) {
        return ($property->isPrivate() || $property->isProtected());
    }

}
