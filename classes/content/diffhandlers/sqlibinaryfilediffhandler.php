<?php
/**
 * File containing SQLIBinaryFileDiffHandler class
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage content
 * @subpackage diffhandlers
 */

/**
 * Diff handler for ezbinaryfile attributes.
 * Only takes file name into account.
 */
class SQLIBinaryFileDiffHandler implements ISQLIDiffHandler
{
    /**
     * (non-PHPdoc)
     * @see extension/sqliimport/classes/content/diffhandlers/ISQLIDiffHandler::contentIsModified()
     */
    public static function contentIsModified( $data, eZContentObjectAttribute $attribute )
    {
        $isModified = false;
        
        $originalFilename = $attribute->attribute( 'content' )->attribute( 'original_filename' );
        $newFilename = basename( (string)$data );
        if( $newFilename != $originalFilename )
            $isModified = true;
            
        return $isModified;
    }
}
