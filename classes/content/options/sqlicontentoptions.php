<?php
/**
 * SQLIContentOptions
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage content
 * @subpackage options
 */

/**
 * Class containing basic options for SQLIContent
 */
class SQLIContentOptions extends SQLIImportOptions
{
    public function __construct( array $options = array() )
    {
        // Define some default values
        $this->properties = array(
            'remote_id'         => null,
            'section_id'        => 1,
            'creator_id'        => false,
            'language'          => eZContentObject::defaultLanguage(),
            'class_identifier'  => null
            // TODO : Implement states
        );
        
        parent::__construct( $options );
    }
    
    /**
     * Sets the option $optionName to $optionValue.
     * Supported options for content are :
     *  - class_identifier
     *  - remote_id => ID you can define to easily retrieve an object
     *  - section_id => Section ID for object
     *  - creator_id => Object creator UserID
     *  - language => Language for object
     * @see lib/ezc/Base/src/ezcBaseOptions::__set()
     */
    public function __set( $optionName, $optionValue )
    {
        parent::__set( $optionName, $optionValue );
    }
}
