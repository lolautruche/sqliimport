<?php
/**
 * Token class for SQLI Import
 * Handles import status
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 */

class SQLIImportToken extends eZPersistentObject
{
    const IMPORT_STATUS_PENDING = 1,
          IMPORT_STATUS_RUNNING = 2;

    const STATUS_FIELD_NAME = 'sqliimport_status',
          CURRENT_HANDLER_FIELD_NAME = 'sqliimport_current_handler';
    
    /**
     * Flag indicating if current script is launched by SQLIImport or not.
     * Useful to identify import scripts in workflow eventtypes
     * @var bool
     */
     private static $isImportScript = false;
    
    /**
     * Schema definition
     * eZPersistentObject implementation for native table ezsite_data
     * @see kernel/classes/ezpersistentobject.php
     * @return array
     */
    public static function definition()
    {
        return array( 'fields'       => array( 'name'               => array( 'name'     => 'name',
                                                                              'datatype' => 'string',
                                                                              'default'  => null,
                                                                              'required' => true ),
        
                                               'value'              => array( 'name'     => 'value',
                                                                              'datatype' => 'string',
                                                                              'default'  => null,
                                                                              'required' => true ),
        
                                            ),
                                            
                     'keys'                 => array( 'name' ),
                     'class_name'           => 'SQLIImportToken',
                     'name'                 => 'ezsite_data',
                     'function_attributes'  => array()
        );
    }
    
    /**
     * Checks if an import is already running
     * @return bool
     */
    public static function importIsRunning()
    {
        $conds = array(
            'name'  => self::STATUS_FIELD_NAME,
            'value' => self::IMPORT_STATUS_RUNNING
        );
        $statusCount = parent::count( self::definition(), $conds );
        
        return $statusCount > 0;
    }
    
    /**
     * Checks if an import is pending (waiting to start)
     * @return bool
     */
    public static function importIsPending()
    {
        $conds = array(
            'name'  => self::STATUS_FIELD_NAME,
            'value' => self::IMPORT_STATUS_PENDING
        );
        $statusCount = parent::count( self::definition(), $conds );
        
        return $statusCount > 0;
    }
    
    /**
     * Registers a new import process. Must be called before running an import to avoid several imports running at a time
     * @param int $status Status of the import. Default is SQLIImportToken::IMPORT_STATUS_RUNNING
     * @return SQLIImportToken
     */
    public static function registerNewImport( $status = self::IMPORT_STATUS_RUNNING )
    {
        $row = array(
            'name'  => self::STATUS_FIELD_NAME,
            'value' => $status
        );
        
        $token = new self( $row );
        $token->store();
        
        return $token;
    }
    
    /**
     * Fetches current token
     * @return SQLIImportToken or null if none registered
     */
    public static function fetchToken()
    {
        $token = parent::fetchObject( self::definition(), null, array( 'name' => self::STATUS_FIELD_NAME ) );
        return $token;
    }
    
    /**
     * Registers import handler that is currently processed
     * @param string $handlerName
     */
    public static function setCurrentHandler( $handlerName )
    {
        // First check if a current handler is already registered
        $handlerToken = parent::fetchObject( self::definition(), null, array( 'name' => self::CURRENT_HANDLER_FIELD_NAME ) );
        
        if( $handlerToken instanceof SQLIImportToken )
        {
            $handlerToken->setAttribute( 'value', $handlerName );
        }
        else // No current handler registered, create it with requested handler name
        {
            $row = array(
                'name'  => self::CURRENT_HANDLER_FIELD_NAME,
                'value' => $handlerName
            );
            $handlerToken = new self( $row );
            $handlerToken->store();
        }
    }
    
    /**
     * Cleans current import handler (when all import processes has been executed for example)
     */
    public static function cleanCurrentHandler()
    {
        $conds = array(
            'name'  => self::CURRENT_HANDLER_FIELD_NAME
        );
        parent::removeObject( self::definition(), $conds );
    }
    
    /**
     * Cleans import token
     */
    public static function cleanImportToken()
    {
        $conds = array(
            'name'  => self::STATUS_FIELD_NAME
        );
        parent::removeObject( self::definition(), $conds );
    }
    
    /**
     * Cleans up tokens
     */
    public static function cleanAll()
    {
        self::cleanImportToken();
    }
    
    /**
     * Indicates if current script is launched by SQLIImport or not.
     * Useful to identify import scripts in workflow eventtypes
     * @return bool
     */
    public static function isImportScript()
    {
        return self::$isImportScript;
    }
    
    /**
     * Sets {@link self::$isImportScript}
     * @param bool $val
     */
    public static function setIsImportScript( $val )
    {
        self::$isImportScript = (bool)$val;
    }
}