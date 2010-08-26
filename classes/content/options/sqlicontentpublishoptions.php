<?php
/**
 * File containing SQLIContentPublishOptions class
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage content.options
 */

class SQLIContentPublishOptions extends SQLIImportOptions
{
    /**
     * Constructor
     * @param array $options
     */
    public function __construct( array $options = array() )
    {
        // Define some default values
        $this->properties = array(
            'parent_node_id'            => null,
            'modification_check'        => true,
            'copy_prev_version'         => true,
            'update_null_field'         => false, // If true, will update any field in DB, even if data is not set (null)
        );
        
        parent::__construct( $options );
    }
    
    /**
     * (non-PHPdoc)
     * @see lib/ezc/Base/src/ezcBaseOptions::__set()
     */
    public function __set( $name, $value )
    {
        parent::__set( $name, $value );
    }
}
