<?php
/**
 * File containing SQLICSVOptions class
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage sourcehandlers
 * @subpackage csv
 */

class SQLICSVOptions extends SQLIImportOptions
{
    /**
     * Constructor.
     * Available options are :
     *  - csv_path (Path to CSV file)
     *  - delimiter (Field delimiter, one character only)
     *  - enclosure (Field enclosure character, one character only)
     *  - csv_line_length (CSV line length. Must be higher than the longest line in CSV file)
     * @param $options
     */
    public function __construct( array $options = array() )
    {
        // Define some default values
        $this->properties = array(
            'csv_path'         => null, // Path to CSV file
            'delimiter'        => ';', // Field delimiter (one character only)
            'enclosure'        => '"', // Field enclosure character (one character only)
            'csv_line_length'  => 100000, // CSV line length. Must be higher than the longest line in CSV file
        );
        
        parent::__construct( $options );
    }
    
    /**
     * (non-PHPdoc)
     * @see extension/sqliimport/classes/options/SQLIImportOptions::__set()
     */
    public function __set( $optionName, $optionValue )
    {
        parent::__set( $optionName, $optionValue );
    }
}
