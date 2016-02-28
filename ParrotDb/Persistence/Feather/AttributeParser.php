<?php

namespace ParrotDb\Persistence\Feather;

use ParrotDb\ObjectModel\PObjectId;

/**
 * This class is used to parse an attribute of a feather string
 *
 * @author J. Baum
 */
class AttributeParser
{

    private $input;
    private $offset;

    /**
     * @param string $input
     */
    public function setInput($input)
    {
        $this->input = $input;
        $this->offset = 0;
    }

    private function attributeOffset()
    {
        return mb_strlen(",") + mb_strlen("'");
    }

    private function attrNameLen()
    {
        return mb_strpos($this->input, ":", $this->offset) - $this->offset - 1;
    }

    private function attrName($nameLen)
    {
        return mb_substr($this->input, $this->offset, $nameLen);
    }

    private function offsetIncreasing($nameLen)
    {
        return $nameLen + mb_strlen(":") * 2;
    }

    private function valueLength($lengthEndPos)
    {
        return mb_substr(
          $this->input, $this->offset, $lengthEndPos - $this->offset
         ) + 2;
    }

    private function lengthEndPos()
    {
        return mb_strpos($this->input, ":", $this->offset) + 1;
    }

    private function value($lengthEndPos, $valLen)
    {
        return mb_substr($this->input, $lengthEndPos, $valLen);
    }

    /**
     * @param int $globalOffset
     * @return string
     */
    public function parse($globalOffset)
    {
        $this->offset = $globalOffset;
        $this->offset += $this->attributeOffset();
        $nameLen = $this->attrNameLen();
        $name = $this->attrName($nameLen);

        $this->offset += $this->offsetIncreasing($nameLen);
        $lengthEndPos = $this->lengthEndPos();
        $valLen = $this->valueLength($lengthEndPos);

        $value = $this->value($lengthEndPos, $valLen);

        return array(
         'name' => $name,
         'offset' => $lengthEndPos + $valLen,
         'value' => $this->parseAttributeValue($value)
        );
    }

    private function parseAttributeValue($value)
    {
        $cleaned = mb_substr($value, 1, mb_strlen($value) - 2);

        switch ($value[0]) {
            case "'":
                if (mb_strlen($value) <= 2) {
                    return '';
                }
                return $cleaned;
            case "(":
                if (mb_strlen($value) <= 2) {
                    return null;
                }
                return new PObjectId($cleaned);
            case "{":
                if (mb_strlen($value) <= 2) {
                    return array();
                }
                return $this->parseArray($value);
        }
        
        return $value;
    }

    private function parseArray($arrayString)
    {

        $arrayLen = mb_strlen($arrayString);
        $this->offset = 0;
        $outArr = array();

        while ($this->isOffsetInRange($arrayLen)) {

            $this->input = $arrayString;
            $attribute = $this->parse($this->offset);

            $outArr[$attribute['name']] = $attribute['value'];
            $this->offset = $attribute['offset'];
        }

        return $outArr;
    }

    private function isOffsetInRange($arrayLen)
    {
        return $this->offset < ($arrayLen - 2);
    }

}
