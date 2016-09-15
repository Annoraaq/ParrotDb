<?php
/**
 * Created by PhpStorm.
 * User: Annoraaq
 * Date: 09.09.2016
 * Time: 12:33
 */

namespace ParrotDb\Datastructures;


class VirtualArray
{
    const INT_LENGTH = 10;
    const PAYLOAD_LENGTH = 10;

    protected $fileName;
    protected $file;

    public function __construct($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * Open file
     */
    public function open()
    {
        $this->file = fopen($this->fileName, 'c+');

        if (!$this->file) {
            throw new PException("Could not open file: " . $this->fileName);
        }

    }

    /**
     * Close file
     */
    public function close()
    {
        fclose($this->file);
    }

    public function access($index) {
        $offset = ($index-1)*(1 + self::PAYLOAD_LENGTH);

        fseek($this->file, $offset, SEEK_SET);
        $char = fread($this->file, 1);


        if ($char != "[") {
            return false;
        }

        // skip leading '§'
        $offset++;

        fseek($this->file, $offset, SEEK_SET);
        $value = fread($this->file, self::PAYLOAD_LENGTH);

        return $value;


    }

    public function put($index, $value) {
        $offset = ($index-1)*(1 + self::PAYLOAD_LENGTH);
        fseek($this->file, $offset, SEEK_SET);
        fwrite($this->file, "[" . $this->convertString($value));
    }

    private function convertInt($int) {
        return str_pad($int, self::INT_LENGTH, '0', STR_PAD_LEFT);
    }

    private function convertString($input) {

        while (mb_strlen($input) < self::PAYLOAD_LENGTH) {
            $input = " " . $input;
        }

        $input = mb_substr($input, 0, self::PAYLOAD_LENGTH);

        return $input;

    }

    public function remove($index) {
        $offset = ($index-1)*(1 + self::PAYLOAD_LENGTH);
        fseek($this->file, $offset, SEEK_SET);
        fwrite($this->file, "0" . $this->convertString(""));
    }


}