<?php
/**
 * File containing SQLICSVDoc class
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage sourcehandlers
 * @subpackage csv
 */

/**
 * Utility class for CSV file handling.
 * First row of CSV file will be used for rows as labels.
 * All special characters will be removed in labels
 */
class SQLICSVDoc
{
    /**
     * Pointer to CSV file
     * @var resource
     */
    protected $csvFile;
    
    /**
     * @var SQLICSVOptions
     */
    protected $options;
    
    /**
     * Rows of CSV File
     * @var SQLICSVRowSet
     */
    public $rows;
    
    /**
     * Constructor.
     * Will throw an exception if CSV file does not exist or is invalid
     * @param SQLICSVOptions $options Options for CSV file (See {@link SQLICSVOptions::__construct()})
     * @throws SQLICSVException
     */
    public function __construct( SQLICSVOptions $options )
    {
        $this->options = $options;
        $csvPath = $options['csv_path'];
        
        if( !file_exists( $csvPath ) )
            throw new SQLICSVException( "CSV file $csvPath does not exist");
        
    }
    
    /**
     * Parses CSV File
     * @throws SQLICSVException
     * @return SQLICSVDoc
     */
    public function parse()
    {
        eZDebug::accumulatorStart( 'sqlicsvdoc_loading', 'sqlicsvdoc', 'Loading CSV file in memory' );
        $this->csvFile = @fopen( $this->options['csv_path'], 'r' );
        if( !$this->csvFile )
            throw new SQLICSVException( "Cannot open CSV file '{$this->options['csv_path']}' for reading" );
        
        $this->rows = SQLICSVRowSet::fromCSVFile( $this->csvFile, $this->options );
        eZDebug::accumulatorStop( 'sqlicsvdoc_loading' );
        
        return $this;
    }
}
