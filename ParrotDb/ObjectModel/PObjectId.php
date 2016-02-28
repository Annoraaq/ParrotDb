<?php

namespace ParrotDb\ObjectModel;

use \ParrotDb\Core\Comparable;

/**
 * This class represents a unique object identifier.
 *
 * @author J. Baum
 */
class PObjectId implements Comparable
{

    private $id;

    /**
     * @param int $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function equals($object)
    {
        if ($object instanceof PObjectId) {
            if ($object->getId() == $this->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function __toString()
    {
        if ($this->id == null) {
            return "null";
        }
        return "" . $this->id;
    }

}
