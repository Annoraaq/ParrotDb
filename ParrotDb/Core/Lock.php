<?php

namespace ParrotDb\Core;


use \ParrotDb\Persistence\Feather\FeatherFileManager;

/**
 * Description of Lock
 *
 * @author J. Baum
 */
class Lock
{

    const FILENAME = "lock.lck";

    private $lock = null;

    /**
     * Lock constructor.
     * @param $path Path of the directory to create the lock
     */
    public function __construct($path) {
        $this->lock = fopen($path . self::FILENAME, 'a');
    }


    /**
     * Grabs the lock
     * @return bool Success
     */
    public function lock()
    {
        if (is_resource($this->lock)) {
            return flock($this->lock, LOCK_EX | LOCK_NB);
        }
        return false;
    }

    /**
     * Release the lock
     * @return bool Success
     */
    public function release()
    {
        $result = false;
        if (is_resource($this->lock));
        {
            $result = flock($this->lock, LOCK_UN);
            $result &= fclose($this->lock);
            $this->lock = null;
        }
        return $result;
    }

}
