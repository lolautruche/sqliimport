<?php
/**
 * File containing demo import handler SQLIRSSImportHandler
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage sourcehandlers
 */

class SQLIRSSImportHandler extends SQLIImportAbstractHandler implements ISQLIImportHandler
{
    protected $rowIndex = 0;
    
    protected $rowCount;
    
    protected $currentGUID;
    
    /**
     * Constructor
     */
    public function __construct( SQLIImportHandlerOptions $options = null )
    {
        parent::__construct( $options );
        $this->remoteIDPrefix = $this->getHandlerIdentifier().'-';
        $this->currentRemoteIDPrefix = $this->remoteIDPrefix;
    }
    
    /**
     * (non-PHPdoc)
     * @see extension/sqliimport/classes/sourcehandlers/ISQLIImportHandler::initialize()
     */
    public function initialize()
    {
        $rssFeedUrl = $this->handlerConfArray['RSSFeed'];
        $xmlOptions = new SQLIXMLOptions( array(
            'xml_path'      => $rssFeedUrl,
            'xml_parser'    => 'simplexml'
        ) );
        $xmlParser = new SQLIXMLParser( $xmlOptions );
        $this->dataSource = $xmlParser->parse();
    }
    
    /**
     * (non-PHPdoc)
     * @see extension/sqliimport/classes/sourcehandlers/ISQLIImportHandler::getProcessLength()
     */
    public function getProcessLength()
    {
        if( !isset( $this->rowCount ) )
        {
            $this->rowCount = count( $this->dataSource->channel->item );
        }
        return $this->rowCount;
    }
    
    /**
     * (non-PHPdoc)
     * @see extension/sqliimport/classes/sourcehandlers/ISQLIImportHandler::getNextRow()
     */
    public function getNextRow()
    {
        if( $this->rowIndex < $this->rowCount )
        {
            $row = $this->dataSource->channel->item[$this->rowIndex];
            $this->rowIndex++;
        }
        else
        {
            $row = false; // We must return false if we already processed all rows
        }
        
        return $row;
    }
    
    /**
     * (non-PHPdoc)
     * @see extension/sqliimport/classes/sourcehandlers/ISQLIImportHandler::process()
     */
    public function process( $row )
    {
        // $row is a SimpleXMLElement object
        $this->currentGUID = $row->guid;
        $contentOptions = new SQLIContentOptions( array(
            'class_identifier'      => 'article',
            'remote_id'             => (string)$row->guid
        ) );
        $content = SQLIContent::create( $contentOptions );
        $content->fields->title = (string)$row->title;
        $content->fields->author = (string)$row->author.'|noreply@sqli.com|-1'; // @see eZAuthorType::fromString()
        
        // Handle HTML content
        $htmlMessage = (string)$row->description;
        $parser = new SQLIXMLInputParser();
        $parser->setParseLineBreaks( true );
        $document = $parser->process( $htmlMessage ); // Result is a DOM Document
        $content->fields->intro = eZXMLTextType::domString( $document );
        
        // Now publish content
        $content->addLocation( SQLILocation::fromNodeID( $this->handlerConfArray['DefaultParentNodeID'] ) );
        $publisher = SQLIContentPublisher::getInstance();
        $publisher->publish( $content );
        
        // Free some memory. Internal methods eZContentObject::clearCache() and eZContentObject::resetDataMap() will be called
        // @see SQLIContent::__destruct()
        unset( $content );
    }
    
    /**
     * (non-PHPdoc)
     * @see extension/sqliimport/classes/sourcehandlers/ISQLIImportHandler::cleanup()
     */
    public function cleanup()
    {
        // Nothing to clean up
        return;
    }
    
    /**
     * (non-PHPdoc)
     * @see extension/sqliimport/classes/sourcehandlers/ISQLIImportHandler::getHandlerName()
     */
    public function getHandlerName()
    {
        return 'RSS Import Handler';
    }
    
    /**
     * (non-PHPdoc)
     * @see extension/sqliimport/classes/sourcehandlers/ISQLIImportHandler::getHandlerIdentifier()
     */
    public function getHandlerIdentifier()
    {
        return 'rssimporthandler';
    }
    
    /**
     * (non-PHPdoc)
     * @see extension/sqliimport/classes/sourcehandlers/ISQLIImportHandler::getProgressionNotes()
     */
    public function getProgressionNotes()
    {
        return 'Currently importing : '.$this->currentGUID;
    }
}
