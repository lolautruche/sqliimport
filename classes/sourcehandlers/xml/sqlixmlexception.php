<?php
/**
 * File containing SQLIXMLException class
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage sourcehandlers
 * @subpackage xml
 */

class SQLIXMLException extends SQLIImportBaseException
{
    const DOM_PARSE_ERROR = -1,
          XML_STREAM_EMPTY = -2;
    
    /**
     * DOM parse error handling
     * @param int $errno PHP error number
     * @param string $errstr Error message
     * @param string $errfile
     * @param int $errline
     * @return bool
     * @throws SQLIXMLException
     */
    public static function HandleDOMLoadError( $errno, $errstr, $errfile, $errline )
    {
        if ( substr_count( $errstr,"DOMDocument::loadXML()") > 0 )
            throw new self( $errstr, self::DOM_PARSE_ERROR );
        else
            return false;
    }
    
    /**
     * Dom parse error handling for SimpleXML
     * @param int $errno PHP error number
     * @param string $errstr Error message
     * @param string $errfile
     * @param int $errline
     * @return bool
     * @throws SQLIXMLException
     */
    public static function HandleSimpleXMLError( $errno, $errstr, $errfile, $errline )
    {
        if ( substr_count( $errstr,"SimpleXMLElement::__construct()" ) > 0 )
            throw new self( $errstr, self::DOM_PARSE_ERROR );
        else
            return false;
    }
    
    /**
     * (non-PHPdoc)
     * @see Exception::__toString()
     */
    public function __toString()
    {
        return __CLASS__ . " [$this->code] => $this->message\n";
    }
}
