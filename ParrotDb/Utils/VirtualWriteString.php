<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ParrotDb\Utils;


/**
 * Description of VirtualWriteString
 *
 * @author J. Baum
 */
class VirtualWriteString extends VirtualString
{
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
     * @param int $start
     * 
     * @return string Substring in the specified range
     */
    public function replace($start)
    {
        fseek($this->file, $start, SEEK_SET);

        fwrite($this->file, "i");

    }

    /**
     * @param int $start
     * @param string $string
     *
     * @return string Substring in the specified range
     */
    public function replaceStr($start, $string)
    {
        fseek($this->file, $start, SEEK_SET);

        fwrite($this->file, $string);

    }

    /**
     * @param string $string
     */
    public function append($string)
    {
        fseek($this->file, 0, SEEK_END);

        fwrite($this->file, $string);

    }

}
