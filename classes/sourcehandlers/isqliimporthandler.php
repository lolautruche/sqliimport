<?php
/**
 * Interface for import handlers
 * Must be implemented by all import handlers
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @package sqliimport
 * @subpackage sourcehandlers
 */
interface ISQLIImportHandler
{
    /**
     * Constructor
     */
    public function __construct( SQLIImportHandlerOptions $options = null );
    
    /**
     * Main method called to configure/initialize handler.
     * Here you may read your data to import
     */
    public function initialize();
    
    /**
     * Get the number of iterations needed to complete the process.
     * For example, if you have 150 XML nodes to process, you may return 150.
     * This is needed to display import progression in admin interface
     * @return int
     */
    public function getProcessLength();
    
    /**
     * Must return next row to process.
     * In an iteration over several XML nodes, you'll return the current node (like current() function for arrays)
     * @return SimpleXMLElement|SimpleXMLIterator|DOMNode|SQLICSVRow
     */
    public function getNextRow();
    
    /**
     * Main method to process current row returned by getNextRow() method.
     * You may throw an exception if something goes wrong. It will be logged but won't break the import process
     * @param mixed $row Depending on your data format, can be DOMNode, SimpleXMLIterator, SimpleXMLElement, CSV row...
     */
    public function process( $row );
    
    /**
     * Final method called at the end of the handler process.
     */
    public function cleanup();
    
    /**
     * Returns full handler name
     * @return string
     */
    public function getHandlerName();
    
    /**
     * Returns handler identifier, as in sqliimport.ini
     * @return string
     */
    public function getHandlerIdentifier();
    
    /**
     * Returns notes for import progression. Can be any string (an ID, a reference...)
     * Can be for example ID of row your import handler has just processed
     * @return string
     */
    public function getProgressionNotes();
}