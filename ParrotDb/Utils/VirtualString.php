<?php

namespace ParrotDb\Utils;

use ParrotDb\Core\PException;

/**
 * This class allows to handle the contents of a text file like a string
 *
 * @author J. Baum
 */
class VirtualString
{

    protected $fileName;
    protected $file;
    private $chunkSize;
    private $windowStart;
    private $windowEnd;
    public $window;

    /**
     * @param string $fileName
     * @param int $chunkSize
     */
    public function __construct($fileName, $chunkSize)
    {
        $this->fileName = $fileName;
        $this->chunkSize = $chunkSize;
        $this->windowStart = -1;
        $this->windowEnd = -1;
    }

    /**
     * Open file
     */
    public function open()
    {
        $this->file = fopen($this->fileName, 'r');

        if (!$this->file) {
            throw new PException("Could not open file: " . $this->fileName);
        }
    }

    /**
     * @param int $pos position of the file to read
     * @return string read character
     */
    public function get($pos)
    {

        if (!$this->isInWindow($pos)) {
            $this->loadWindow($pos);
        }

        return $this->window[$pos - $this->windowStart];
    }

    /**
     * @param int $start
     * @param int $stop
     *
     * @return string Substring in the specified range
     */
    public function substr($start, $stop)
    {
        $substr = '';

        $pos = $start;
        while ($pos < $stop) {
            try {
                $substr .= $this->get($pos);
                $pos++;
            } catch (PException $e) {
                break;
            }
        }

        return $substr;
    }

    private function isInWindow($pos)
    {
        return ($this->windowStart <= $pos && $this->windowEnd >= $pos);
    }

    public function getWindow()
    {
        return $this->window;
    }

    private function loadWindow($pos)
    {
        fseek($this->file, $pos, SEEK_SET);
        $this->window = fread($this->file, $this->chunkSize);

        if ($this->window) {
            $this->windowStart = $pos;
            $this->windowEnd = $pos + strlen($this->window) - 1;
        } else {
            $this->windowsStart = -1;
            $this->windowEnd = -1;
            throw new PException(
                "The requested position exceeds the file length."
            );
        }
    }

    /**
     * @param string $needle
     * @param int offset
     *
     * @return int Position of $needle; -1 if not found
     */
    public function findFirst($needle, $offset = 0)
    {
        $length = mb_strlen($needle);

        $pos = $offset;

        // :performance
        // make this faster by not loading the whole substring for every position
        while (true) {

            $substr = $this->substr($pos, $pos + $length);

            if (strlen($substr) < $length) {
                return -1;
            }

            if ($needle == $substr) {
                return $pos;
            }

            $pos++;
        }

    }

    /**
     * @param string $needle
     * @param int offset
     *
     * @return int Position of $needle; -1 if not found
     */
    public function findFirstUnescaped($needle, $offset = 0)
    {
        $length = mb_strlen($needle);

        $pos = $offset;

        // :performance
        // make this faster by not loading the whole substring for every position
        while (true) {

            if ($pos > 0) {
                $first = $this->substr($pos - 1, $pos);
                $substr = $this->substr($pos, $pos + $length);
                if ($first == '\\') {
                    $pos++;
                    continue;
                }
            } else {
                $substr = $this->substr($pos, $pos + $length);
            }

            if (strlen($substr) < $length) {
                return -1;
            }

            if ($needle == $substr) {
                return $pos;
            }

            $pos++;
        }

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
    public function getNextInterval($start, $leftBorder, $rightBorder)
    {

        $leftPos = $this->findFirstUnescaped($leftBorder, $start);
        $rightPos = $this->findFirstUnescaped($rightBorder, $leftPos + 1);

        if ($leftPos == (-1) || $rightPos == (-1)) {
            throw new PException("Borders not found.");
        }

        return $this->substr($leftPos + 1, $rightPos);

    }

    /**
     * @param int $start
     * @param string $leftBorder
     * @param string $rightBorder
     *
     * @return boolean
     *
     * @throws PException
     */
    public function hasNextInterval($start, $leftBorder, $rightBorder)
    {

        $leftPos = $this->findFirstUnescaped($leftBorder, $start);
        $rightPos = $this->findFirstUnescaped($rightBorder, $leftPos + 1);

        if ($leftPos == (-1) || $rightPos == (-1)) {
            return false;
        }

        return true;

    }

    /**
     * Close file
     */
    public function close()
    {
        fclose($this->file);
    }

}
