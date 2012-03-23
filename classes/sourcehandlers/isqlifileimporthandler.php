<?php
/**
 * Interface for file import handlers
 * May be implemented by import handlers
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Benjamin Choquet
 * @package sqliimport
 * @subpackage sourcehandlers
 */
interface ISQLIFileImportHandler extends ISQLIImportHandler
{

    /**
     * Checks if file is in a valid format for $option
     * Returns true or throws an SQLIImportInvalidFileFormatException
     *
     * @param string $option 	File option alias
     * @param string $filePath	File to validate
     * @return boolean
     * @throws SQLIImportInvalidFileFormatException
     */
    public function validateFile( $option, $filePath );
}