<?php
namespace ParrotDb\Persistence;

/**
 * This interface is implemented for all deserializers.
 *
 * @author J. Baum
 */
interface Deserializer {
    
    public function deserialize();
    
}
