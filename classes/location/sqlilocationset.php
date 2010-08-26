<?php
/**
 * File containing SQLILocationSet class
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage location
 */

class SQLILocationSet implements ArrayAccess, Iterator
{
    /**
     * Locations.
     * Array of SQLILocation, indexed by NodeID
     * @var array( SQLILocation )
     */
    protected $locations = array();
        
    /**
     * Internal iterator pointer for Iterator implementation
     * @internal
     * @var array
     */
    protected $iteratorPointer = array();
    
    /**
     * Constructor
     */
    public function __construct()
    {
        
    }
    
    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists( $offset )
    {
        return isset( $this->locations[$offset] );
    }
    
    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet( $offset )
    {
        if( !isset( $this->locations[$offset] ) )
            throw new SQLILocationException( __METHOD__." => Location with NodeID #$offset not defined in this location set" );
        
        return $this->locations[$offset];
    }
    
    /**
     * Fieldsets are readonly. Will throw an exception when trying to directly assign a value
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet( $offset, $value )
    {
        throw new SQLILocationException( __METHOD__.' => Fieldset direct value assignment not allowed' );
    }
    
    /**
     * Systematically throws an exception. Fieldset unset is not allowed
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset( $offset )
    {
         throw new SQLILocationException( __METHOD__.' => Fieldset unset not allowed' );
    }
    
    /**
     * (non-PHPdoc)
     * @see Iterator::current()
     */
    public function current()
    {
        return $this->locations[current( $this->iteratorPointer )];
    }
    
    /**
     * (non-PHPdoc)
     * @see Iterator::key()
     */
    public function key()
    {
        return current( $this->iteratorPointer );
    }
    
    /**
     * (non-PHPdoc)
     * @see Iterator::next()
     */
    public function next()
    {
        next( $this->iteratorPointer );
    }
    
    /**
     * (non-PHPdoc)
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        reset( $this->iteratorPointer );
    }
    
    /**
     * (non-PHPdoc)
     * @see Iterator::valid()
     */
    public function valid()
    {
        return isset( $this->locations[current( $this->iteratorPointer )] );
    }
    
    /**
     * Initializes internal iterator pointer
     * @internal
     */
    protected function initIterator()
    {
        $this->iteratorPointer = array_keys( $this->locations );
    }
    
    /**
     * Adds a new location to the location set
     * @param SQLILocation $location
     */
    public function addLocation( SQLILocation $location )
    {
        $this->locations[$location->attribute( 'node_id' )] = $location;
        $this->initIterator();
    }
}

