<?php
/**
 * File containing SQLIImageDiffHandler class
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage content
 * @subpackage diffhandlers
 */

/**
 * Diff handler for ezimage attributes.
 * Only takes file name into account.
 */
class SQLIImageDiffHandler implements ISQLIDiffHandler
{
    /**
     * (non-PHPdoc)
     * @see extension/sqliimport/classes/content/diffhandlers/ISQLIDiffHandler::contentIsModified()
     */
    public static function contentIsModified( $data, eZContentObjectAttribute $attribute )
    {
        $isModified = false;
        
        $imageHandler = $attribute->attribute( 'content' );
        $originalFilename = $imageHandler->attribute( 'original_filename' );
        $newImageFilename = basename( (string)$data );
        if( $newImageFilename != $originalFilename )
            $isModified = true;
            
        return $isModified;
    }
}
