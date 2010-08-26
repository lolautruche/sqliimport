<?php
/**
 * File containing SQLICSVRowSet class
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage sourcehandlers
 * @subpackage csv
 */

class SQLICSVRowSet implements ArrayAccess, Iterator, Countable
{
    /**
     * CSV Rows
     * Array with numeric index
     * @var array( SQLICSVRow )
     */
    protected $rows;
    
    /**
     * Headers for CSV document (first line with columns titles).
     * All special characters and spaces are removed
     * @var array
     */
    protected $headers;
    
    /**
     * Original headers as written originally in CSV file (with all special chars and spaces)
     * @var array
     */
    protected $headersOriginal;
    
    /**
     * Internal iterator pointer
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
     * Initializes rows and fields from CSV file
     * @param resource $fp File pointer, obtained with {@link fopen()}
     * @param SQLICSVOptions $options
     * @return SQLICSVRowSet
     */
    public static function fromCSVFile( $fp, SQLICSVOptions $options = null )
    {
        if ( !$options instanceof SQLICSVOptions )
            $options = new SQLICSVOptions();
        
        $set = new self();
        $i = 0;
        while( $data = fgetcsv( $fp, $options['csv_line_length'] , $options['delimiter'], $options['enclosure'] ) )
        {
            //echo "Working on CSV row #$i\n";
            
            if( $i == 0 ) // First line, handle headers
            {
                $set->setRowHeaders( $data );
                $i++;
                unset( $data );
                continue;
            }
            
            $aRowData = array();
            $headers = $set->getHeaders();
            for( $j=0, $jMax=count( $headers ); $j<$jMax; ++$j )
            {
                $aRowData[$headers[$j]] = $data[$j];
            }
            
            unset( $headers, $data );
            $row = new SQLICSVRow( $aRowData );
            $set->rows[] = $row;
            unset( $aRowData );
            $i++;
        }
        
        $set->initIterator();
        return $set;
    }
    
    /**
     * Initializes row headers for CSV document.
     * Headers must be in the same order as they appear in CSV file
     * @param array $headers
     */
    protected function setRowHeaders( array $headers )
    {
        $this->headers = array();
        $this->headersOriginal = array();
        
        foreach( $headers as $header )
        {
            $this->headersOriginal[] = $header; // Store "original" header, just in case
            
            $header = $this->cleanHeader( $header );
            $this->headers[] = $header;
        }
    }
    
    /**
     * Cleans provided header.
     * It removes every special chars and camelizes
     * @param string $header
     * @return string Cleant header
     */
    protected function cleanHeader( $header )
    {
        $header = (string)$header;
        
        /*
         * ##### First camelize #####
         * 1. Already camelized items => Add a space before an uppercase letter that has a lower case before it
         * 2. Lower case
         * 3. Replace special chars by spaces
         * 3. Trim
         * 4. Uppercase the first character of each word
         * 5. Lower case the first letter
         * 6. Remove spaces
         */
        $header = preg_replace( '#(?<=[a-z0-9])([A-Z])#', ' $1', $header );
        $header = strtolower( $header );
        $header = preg_replace( '#[^a-z0-9]#i', ' ', $header );
        $header = trim( $header );
        $header = ucwords( $header );
        $header{0} = strtolower( $header{0} );
        $header = str_replace( ' ', '', $header );
        
        return $header;
    }
    
    /**
     * Returns cleant CSV headers (camelized, no special chars)
     * @see SQLICSVRowSet::cleanHeaders()
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
    
    /**
     * Returns CSV headers as provided in original CSV file (not cleant)
     * @return array
     */
    public function getRawHeaders()
    {
        return $this->headersOriginal;
    }
    
    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetExists()
     */
    public function offsetExists( $offset )
    {
        return isset( $this->rows[$offset] );
    }
    
    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetGet()
     */
    public function offsetGet( $offset )
    {
        return $this->rows[$offset];
    }
    
    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetSet()
     */
    public function offsetSet( $offset, $value )
    {
        $this->rows[$offset] = $value;
        $this->initIterator();
    }
    
    /**
     * (non-PHPdoc)
     * @see ArrayAccess::offsetUnset()
     */
    public function offsetUnset( $offset )
    {
        unset( $this->rows[$offset] );
        $this->initIterator();
    }
    
    /**
     * Initializes internal iterator pointer
     * @internal
     */
    protected function initIterator()
    {
        $this->iteratorPointer = array_keys( $this->rows );
    }
    
    /**
     * (non-PHPdoc)
     * @see Iterator::current()
     */
    public function current()
    {
        $pos = current( $this->iteratorPointer );
        return $this->rows[current( $this->iteratorPointer )];
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
        $valid = false;
        $pos = current( $this->iteratorPointer );
        if( $pos !== false && isset( $this->rows[current( $this->iteratorPointer )] ) )
            $valid = true;
        
        return $valid;
    }
    
    /**
     * (non-PHPdoc)
     * @see Countable::count()
     */
    public function count()
    {
        return count( $this->rows );
    }
}
