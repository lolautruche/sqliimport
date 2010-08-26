<?php
/**
 * File containing SQLIXMLOptions class
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage sourcehandlers
 * @subpackage xml
 */

class SQLIXMLOptions extends SQLIImportOptions
{
    const XML_PARSER_DOM = 'dom',
          XML_PARSER_SIMPLEXML = 'simplexml';
    
    /**
     * Constructor.
     * Available options are :
     *  - xml_path (Path to XML file or stream)
     *  - xml_string (Raw XML string. Used if xml_path is not provided)
     *  - xml_parser (XML Parser, may be "dom" or "simplexml". DOM is default. If simplexml is demanded, SimpleXMLIterator will be used)
     *  - timeout (Timeout in seconds for the distant call)
     *  - xsd (XSD file allowing to validate XML stream)
     * @param array $options
     */
    public function __construct( array $options = array() )
    {
        // Define some default values
        $this->properties = array(
            'xml_path'          => null, // Path to XML file or stream
            'xml_string'        => null, // XML String. May be used if xml_path is not provided
            'xml_parser'        => 'dom', // XML Parser, may be "dom" or "simplexml". DOM is default
            'timeout'           => eZINI::instance( 'sqliimport.ini' )->variable( 'ImportSettings', 'StreamTimeout' ), // Timeout in seconds for the distant call
            'xsd'               => null, // XSD file allowing to validate XML stream
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
