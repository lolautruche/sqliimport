<?php
/**
 * Interface for file import handlers
 * May be implemented by import handlers
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Benjamin Choquet <benjamin.choquet@heliopsis.net>
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage sourcehandlers
**/
interface ISQLIFileImportHandler extends ISQLIImportHandler
{

    /**
     * Checks if file is in a valid format for $option
     * Returns true or throws an SQLIImportInvalidFileFormatException
     *
     * @param string $option 	File option alias
     * @param string $filePath	File to validate. Must be a valid local file (fetched from cluster if needed)
     * @return boolean
     * @throws SQLIImportInvalidFileFormatException
     */
    public function validateFile( $option, $filePath );
}