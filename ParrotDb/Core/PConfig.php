<?php

namespace ParrotDb\Core;


/**
 * Description of PConfig
 *
 * @author J. Baum
 */
class PConfig
{

    /**
     * @var int memory limit in bytes
     */
    private $memoryLimit;

    /**
     * @var int
     */
    private $activationDepth;

    /**
     * @var bool ignore static fields
     */
    private $ignoreStatic;

    private $path;


    /**
     * PConfig constructor.
     * @param null $path
     * @throws PException
     */
    public function __construct($path = null) {

        $this->path = $path;

        // default value is ~10 mb
        $this->memoryLimit = 10000000;
        
        // default is infinity
        $this->activationDepth = -1;
        
        // persist static values by default
        $this->ignoreStatic = false;

        $this->loadConfigFile();

    }

    private function loadConfigFile() {

        if ($this->path !== null && file_exists($this->path)) {
            $cfgFile = include($this->path);
            if (isset($cfgFile["memoryLimit"])) {
                $this->memoryLimit = $cfgFile["memoryLimit"];
            }

            if (isset($cfgFile["activationDepth"])) {
                $this->activationDepth = $cfgFile["activationDepth"];
            }

            if (isset($cfgFile["ignoreStatic"])) {
                $this->ignoreStatic = $cfgFile["ignoreStatic"];
            }
        } else if ($this->path !== null && !file_exists($this->path)) {
            throw new PException("Config file not found: '" . $this->path . "'");
        }
    }


    /**
     * @return int memory limit in bytes
     */
    public function getMemoryLimit()
    {
        return $this->memoryLimit;
    }

    /**
     * @param int $memoryLimit memory limit in bytes
     */
    public function setMemoryLimit($memoryLimit)
    {
        $this->memoryLimit = $memoryLimit;
    }

    /**
     * @return int
     */
    public function getActivationDepth()
    {
        return $this->activationDepth;
    }

    /**
     * @param int $activationDepth
     */
    public function setActivationDepth($activationDepth)
    {
        $this->activationDepth = $activationDepth;
    }

    /**
     * @return boolean ignore static fields
     */
    public function isIgnoreStatic()
    {
        return $this->ignoreStatic;
    }

    /**
     * @param boolean $ignoreStatic ignore static fields
     */
    public function setIgnoreStatic($ignoreStatic)
    {
        $this->ignoreStatic = $ignoreStatic;
    }




}
