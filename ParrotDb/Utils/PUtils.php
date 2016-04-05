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
     * Checks, whether the given variable is of type string or not.
     * 
     * @param mixed $value
     * @return boolean
     */
    public static function isString($value) {
        return (gettype($value) == "string");
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

    
    /**
     * Removes the last character of the given string.
     * 
     * @param string $input
     * @return string
     */
    public static function cutLastChar($input)
    {
        $length = mb_strlen($input);
        
        if ($length >= 1) {
           return substr($input, 0, $length - 1); 
        }
        
        return $input;
        
    }
    
    /**
     * Removes the first character of the given string.
     * 
     * @param string $input
     * @return string
     */
    public static function cutFirstChar($input)
    {
        $length = mb_strlen($input);
        
        if ($length >= 1) {
           return mb_substr($input, 1); 
        }
        
        return $input;
        
    }
    
    /**
     * Cuts the first and the last char from a string
     * 
     * @param string $input
     * @return string
     */
    public static function cutHeadAndTail($input)
    {
        return self::cutLastChar(self::cutFirstChar($input));
    }
    
    /**
     * Escapes special characters with a backslash
     * 
     * @param mixed $value
     * @return boolean
     */
    public static function escape($value) {
        
        $toEscape = "[],c\\";
        
        $output = "";
        
        for ($i=0; $i<mb_strlen($value); $i++) {
            if (self::contains($toEscape, $value[$i])) {
                $output .= "\\";
            }
            
            $output .= $value[$i];
        }
        
        return $output;
    }
    
    /**
     * Unescapes special characters with a backslash
     * 
     * @param mixed $value
     * @return boolean
     */
    public static function unescape($value) {
        
        $output = "";
        
        $deleted = false;
        for ($i=0; $i<mb_strlen($value); $i++) {
            
            if ($value[$i] == "\\") {
                if (!$deleted) {
                    $deleted = true;
                } else {
                    $deleted = false;
                    $output .= $value[$i];
                }
            } else {
                $deleted = false;
                $output .= $value[$i];
            }
        }
        
        return $output;
    }
    
    public static function contains($haystack, $needle) {
        return (strpos($haystack, $needle) !== false);
    }

}
