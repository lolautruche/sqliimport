<?php
/**
 * Abstract class for all import handlers
 * All import handlers must inherit from this class
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage sourcehandlers
 */

abstract class SQLIImportAbstractHandler
{
    /**
     * eZINI instance for sqliimport.ini
     * @var eZINI
     */
    protected $importINI;
    
    /**
     * eZCLI instance
     * @var eZCLI
     */
    protected $cli;
    
    /**
     * RemoteID prefix to be used for content created by current handler
     * @var string
     */
    protected $remoteIDPrefix;
    
    /**
     * Current remote ID prefix. Can be useful when creating content hierarchy
     * @var string
     */
    protected $currentRemoteIDPrefix;
    
    /**
     * Flag indicating if debug is activated or not. Default is false
     * @var bool
     */
    protected $debug = false;
    
    /**
     * Flag indicating if at least one error has been detected during import for this handler.
     * Default is false
     * @var bool
     */
    protected $hasError = false;
    
    /**
     * Array containing configuration for current handler
     * as defined in sqliimport.ini.
     * Contains only settings defined in INI block dedicated to the handler
     * @var array
     */
    public $handlerConfArray = array();
    
    /**
     * Data source (CSV, XML...)
     * @var SQLICSVDoc|DOMDoc|SimpleXMLIterator
     */
    protected $dataSource;
    
    /**
     * Options provided from command line
     * @var SQLIImportHandlerOptions
     */
    protected $options;
    
    /**
     * Progression notes
     * @see extension/sqliimport/classes/sourcehandlers/ISQLIImportHandler::getProgressionNotes()
     * @var string
     */
    protected $progressionNotes;
    
    /**
     * Constructor
     */
    public function __construct( SQLIImportHandlerOptions $options = null )
    {
        $this->importINI = eZINI::instance( 'sqliimport.ini' );
        $this->cli = eZCLI::instance();
        $this->options = $options;
    }
    
    /**
     * Downloads a remote file in the temp folder defined in site.ini.
     * Returns the local path of the downloaded file.
     * @param string $url File URL
     * @param array $httpAuth Array (numerical indexed) containing HTTP authentication infos. Provide it only if needed (default is null)
     *                          - First element is username.
     *                          - Second element is password.
     * @return string
     * @see SQLIContentUtils::getRemoteFile()
     */
    protected function getRemoteFile( $url, array $httpAuth = null )
    {
        return SQLIContentUtils::getRemoteFile( $url, $httpAuth, $this->debug );
    }
    
    /**
     * Returns eZXML content to insert into XML blocks (ezxmltext datatype)
     * eZXML is generated from HTML content provided as argument
     * @param string $htmlContent Input HTML string
     * @return string Generated eZXML string
     * @see SQLIContentUtils::getRichContent()
     */
    protected function getRichContent( $htmlContent )
    {
        return SQLIContentUtils::getRichContent( $htmlContent );
    }
}