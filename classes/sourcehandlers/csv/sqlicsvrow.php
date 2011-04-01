<?php
/**
 * File containing SQLICSVRow class
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage sourcehandlers
 * @subpackage csv
 */

class SQLICSVRow implements Iterator
{
    /**
     * Fields for this row.
     * Associative array, indexed by camelized field name.
     * @var array()
     */
    protected $fields = array();
    
    /**
     * Internal iterator pointer
     * @internal
     * @var array
     */
    protected $iteratorPointer = array();
    
    /**
     * Constructor
     */
    public function __construct( array $fields = array() )
    {
        $this->fields = $fields;
        $this->initIterator();
    }
    
    /**
     * Getter. Returns CSV field content identified by its camelized field name
     * @param string $name
     * @return string
     */
    public function __get( $name )
    {
        $ret = null;
        
        switch( $name )
        {
            default:
                if( isset( $this->fields[$name] ) )
                    $ret = $this->fields[$name];
                else
                    throw new SQLICSVException( "Invalid '$name' field for current SQLICSVRow" );
        }
        
        return $ret;
    }
    
    /**
     * Initializes internal iterator pointer
     * @internal
     */
    protected function initIterator()
    {
        // Inits iterator pointer from internal data (ie. a property $this->rows)
        $this->iteratorPointer = array_keys( $this->fields );
    }
    
    /**
     * (non-PHPdoc)
     * @see Iterator::current()
     */
    public function current()
    {
        return $this->fields[current( $this->iteratorPointer )];
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
        return isset( $this->fields[current( $this->iteratorPointer )] );
    }
}
