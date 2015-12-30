<?php

namespace ParrotDb\Utils;

/**
 * Description of Utils
 *
 * @author J. Baum
 */
class PUtils {

    /**
     * Checks, whether the given array is associative or not.
     * 
     * @param array $arr
     * @return boolean
     */
    public static function isAssoc($arr) {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Checks, whether the given variable is of type object or not.
     * 
     * @param mixed $value
     * @return boolean
     */
    public static function isObject($value) {
        return (gettype($value) == "object");
    }

    /**
     * Checks, whether the given variable is of type array or not.
     * 
     * @param mixed $value
     * @return boolean
     */
    public static function isArray($value) {
        return (gettype($value) == "array");
    }
    
    /**
     * Checks, whether given string is a single digit number.
     * 
     * @param string $word
     * @return boolean
     */
    public static function isNumber($word) {
        if ($word == '0' ||
            $word == '1' ||
            $word == '2' ||
            $word == '3' ||
            $word == '4' ||
            $word == '5' ||
            $word == '6' ||
            $word == '7' ||
            $word == '8' ||
            $word == '9') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Cuts the first $cutLength elements of given array and
     * returns the new array.
     * 
     * @param array $arr
     * @param int $cutLength
     * @return array
     */
    public static function cutArrayFront($arr, $cutLength) {
        $counter = 0;
        $newArr = [];
        foreach ($arr as $elem) {
            if (!($counter < $cutLength)) {
                $newArr[] = $elem;
            }
            $counter++;
        }

        return $newArr;
    }
    
    /**
     * Cuts the last element of given array and
     * returns the new array.
     * 
     * @param array $arr
     * @param int $cutLength
     * @return array
     */
    public static function cutArrayTail($arr) {
        array_pop($arr);
        return $arr;
    }
    
    /**
     * Checks, if $haystack ends with $needle
     * 
     * @param string $haystack
     * @param string $needle
     * @return boolean
     */
    public static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

}
