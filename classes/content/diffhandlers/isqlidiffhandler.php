<?php
/**
 * File containing ISQLIDiffHandler class
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage content
 * @subpackage diffhandlers
 */

/**
 * Interface for diff handlers.
 * Diff handlers are used to check if a content field really needs to be updated or not.
 * If a content object is strictly the same, then it shouldn't be published (avoids useless time consumption).
 * By default, datatype toString() method is used to compare input content with current content, but it can be insufficient
 * with complex datatypes (ie. for images, binary files...).
 * By defining a diff handler, you extend the SQLIImport content system allowing it to check for content modification more accurately.
 *
 * Every diff handler needs to be declared in sqliimport.ini and each declared class must implement this interface.
 * <i>sqliimport.ini</i>
 * <code>
 *
 * </code>
 */
interface ISQLIDiffHandler
{
    /**
     * Checks if content has been really modified for $field
     * Returns true if content has been modified, false otherwise
     * @param string $data "New" data that needs to be compared with published data
     * @param eZContentObjectAttribute $attribute Published attribute, for reference
     * @return bool
     */
    public static function contentIsModified( $data, eZContentObjectAttribute $attribute );
}
