<?php
/**
 * File containing SQLIDefaultDiffHandler class
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage content
 * @subpackage diffhandlers
 */

/**
 * Default diff handler for content fields.
 * Note : It is HIGHLY RECOMMENDED for all datatypes to implement toString() methods (see eZDatatype::toString()).
 *        Attribute content will be indeed compared with toString() method or data_text field !
 */
class SQLIDefaultDiffHandler implements ISQLIDiffHandler
{
    /**
     * (non-PHPdoc)
     * @see extension/sqliimport/classes/content/diffhandlers/ISQLIDiffHandler::contentIsModified()
     */
    public static function contentIsModified( $data, eZContentObjectAttribute $attribute )
    {
        $isModified = false;
        $attributeValue = SQLIContentField::convertAttributeToString( $attribute );
        
        if( (string)$data != $attributeValue )
            $isModified = true;
        
        return $isModified;
    }
}
