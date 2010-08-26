<?php
/**
 * File containing SQLIXMLParser class
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage sourchandlers
 * @subpackage xml
 */

class SQLIXMLParser
{
    /**
     * @var SQLIXMLOptions
     */
    protected $options;
    
    /**
     * XML String
     * @var string
     */
    protected $xmlString;
    
    /**
     * XML Stream URL
     * @var string
     */
    protected $xmlStreamURL;
    
    /**
     * Flag indicating if XMLStream URL is HTTP or not (ie. for proxy use)
     * @var bool
     */
    protected $isHTTP;
    
    /**
     * Constructor
     * @param SQLIXMLOptions $options
     */
    public function __construct( SQLIXMLOptions $options )
    {
        $this->options = $options;
        
        if( $options['xml_path'] )
        {
            $this->xmlString = $this->getXMLString( $options['xml_path'] );
        }
        else if( $options['xml_string'] )
        {
            $this->xmlString = $options['xml_string'];
        }
        else
        {
            throw new SQLIXMLException( __METHOD__ . ' => Neither "xml_path" or "xml_string" option provided !' );
        }
    }
    
    /**
     * Returns the XML string located at the provided path with timeout management
     * @param string $path XML Path
     * @throws SQLIXMLException
     * @return string
     * @uses cURL
     */
    protected function getXMLString( $path )
    {
        $timeout = $this->options['timeout'];
        
        // File path starts with "/" or with c:\ (for ex.), so we add "file://" as cURL needs it
        if ( strpos( $path, '/' ) == 0 || preg_match( '`^[a-z]:.+`i', $path ) )
            $path = 'file://'.realpath( $path );
        
        $this->xmlStreamURL = $path;
        $this->isHTTP = stripos( $this->xmlStreamURL, 'http' ) !== false;
        
        if( !function_exists( 'curl_init' ) )
            throw new SQLIXMLException( 'cURL extension not detected ! This extension is mandatory for SQLI Import' );
        
        $ch = curl_init( $path );
        curl_setopt( $ch, CURLOPT_HEADER, false );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, (int) $timeout );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        
        // Now check proxy settings
        $ini = eZINI::instance();
        $proxy = $ini->variable( 'ProxySettings', 'ProxyServer' );
        
        if( $proxy && $this->isHTTP ) // cURL proxy support is only for HTTP
        {
            curl_setopt( $ch, CURLOPT_PROXY , $proxy );
            $userName = $ini->variable( 'ProxySettings', 'User' );
            $password = $ini->variable( 'ProxySettings', 'Password' );
            if ( $userName )
            {
                curl_setopt( $ch, CURLOPT_PROXYUSERPWD, "$userName:$password" );
            }
        }
        
        $xmlString = curl_exec( $ch );
        if( $xmlString === false )
        {
            $errMsg = curl_error( $ch );
            $errNum = curl_errno( $ch );
            curl_close( $ch );
            throw new SQLIXMLException( __METHOD__ . ' => Error with stream '.$path.' ('.$errMsg.')', $errNum );
        }
        
        curl_close( $ch );
        return $xmlString;
    }
    
    /**
     * Parses the $xmlString member variable.
     * If any parse error occurs, a {@link SQLIXMLException} will be thrown.
     * @return DOMDocument|SimpleXMLElement
     * @throws SQLIXMLException
     */
    public function parse()
    {
        if( empty( $this->xmlString ) )
            throw new SQLIXMLException( "Given stream $this->xmlStreamURL is empty", SQLIXMLException::XML_STREAM_EMPTY );
        
        // TODO : XSD validation handling
        switch( $this->options['xml_parser'] )
        {
            case SQLIXMLOptions::XML_PARSER_SIMPLEXML :
                // SimpleXML can either raise an error or throw an exception. So we mix error handling and exception catching ;-)
                try
                {
                    set_error_handler( array( 'SQLIXMLException', 'HandleSimpleXMLError' ) ); // Handling SimpleXML errors by SQLIXMLException::HandleSimpleXMLError
                    $domDoc = new SimpleXMLElement( $this->xmlString );
                    restore_error_handler();
                }
                catch ( Exception $e )
                {
                    throw new SQLIXMLException( "An error occured with stream $this->xmlStreamURL : ".$e->getMessage(), SQLIXMLException::DOM_PARSE_ERROR );
                }
                
                break;
            
            case SQLIXMLOptions::XML_PARSER_DOM :
            default :
                set_error_handler( array( 'SQLIXMLException', 'HandleDOMLoadError' ) ); // Handling DOM errors by SQLIXMLException::HandleDOMLoadError
                $domDoc = new DOMDocument();
                $domDoc->preserveWhiteSpace = false;
                $domDoc->loadXML( $this->xmlString );
                restore_error_handler();
                
                break;
        }

        return $domDoc;
    }
    
    /**
     * Sets XML path
     * $path can be a stream (http://, ftp://, file://) or a path in file system
     * @param string $path
     * @return void
     */
    public function setXMLPath( $path )
    {
        $this->xmlString = $this->getXMLString( $path );
    }
    
    /**
     * Sets XML string
     * @param string $xmlString
     * @return void
     */
    public function setXMLString( $xmlString )
    {
        $this->xmlString = $xmlString;
    }
    
    /**
     * Checks if XML stream is HTTP
     * @return bool
     */
    public function isHTTP()
    {
        return $this->isHTTP;
    }
}
