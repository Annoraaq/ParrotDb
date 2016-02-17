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

    }
    
    /**
     * @param int $start
     * @param string $leftBorder
     * @param string $rightBorder
     * 
     * @return string The substring between the first occurrences
     * of $leftBorder and $rightBorder from $offset
     * 
     * @throws PException
     */
    public function replaceInterval($start, $leftBorder, $rightBorder)
    {
        $leftPos = $this->findFirst($leftBorder, $start);
        $rightPos = $this->findFirst($rightBorder, $leftPos);

        if ($leftPos == (-1) || $rightPos == (-1)) {
            throw new PException("Borders not found.");
        }

        return $this->substr($leftPos + 1, $rightPos);
    }
    
    /**
     * @param int $start
     * @param int $stop
     * 
     * @return string Substring in the specified range
     */
    public function replace($start, $stop)
    {
//        $substr = '';
//
//        $pos = $start;
//        while ($pos < $stop) {
//            try {
//                $substr .= $this->get($pos);
//                $pos++;
//            } catch (PException $e) {
//                break;
//            }
//        }
//
//        return $substr;
        
        //echo "\nfseek $start, stop: $stop\n";
        fseek($this->file, $start, SEEK_SET);
        
        
        $res = fread($this->file, 10);
        
        echo "\nread from()$start: $res\n";
                echo "\nINBETWEEN\n";
                
        fseek($this->file, $start, SEEK_SET); 
        fwrite($this->file, "i");
                //echo "\nWINDOW: " . $this->window . "\n";
        

        
    }

}
