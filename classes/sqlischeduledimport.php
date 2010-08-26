<?php
/**
 * File containing SQLIScheduledImport class
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 */

class SQLIScheduledImport extends eZPersistentObject
{
    const FREQUENCY_NONE = 'none',
          FREQUENCY_HOURLY = 'hourly',
          FREQUENCY_DAILY = 'daily',
          FREQUENCY_WEEKLY = 'weekly',
          FREQUENCY_MONTHLY = 'monthly';
    
    /**
     * Options for import
     * @var SQLIImportHandlerOptions
     */
    protected $options;
    
    /**
     * User who requested the import
     * @var eZUser
     */
    protected $user;
    
    /**
     * Constructor
     * @param array $row
     */
    public function __construct( array $row = array() )
    {
        parent::eZPersistentObject( $row );
    }
    
    /**
     * Schema definition
     * eZPersistentObject implementation for sqliimport_scheduled table
     * @see kernel/classes/eZPersistentObject::definition()
     * @return array
     */
    public static function definition()
    {
        return array( 'fields'       => array( 'id'                      => array( 'name'     => 'id',
                                                                                   'datatype' => 'integer',
                                                                                   'default'  => null,
                                                                                   'required' => true ),

                                               'label'                   => array( 'name'     => 'label',
                                                                                   'datatype' => 'string',
                                                                                   'default'  => null,
                                                                                   'required' => true ),
        
                                               'handler'                 => array( 'name'     => 'handler',
                                                                                   'datatype' => 'string',
                                                                                   'default'  => null,
                                                                                   'required' => true ),

                                               'options_serialized'      => array( 'name'     => 'options_serialized',
                                                                                   'datatype' => 'string',
                                                                                   'default'  => null,
                                                                                   'required' => false ),
        
                                               'frequency'               => array( 'name'     => 'frequency',
                                                                                   'datatype' => 'string',
                                                                                   'default'  => null,
                                                                                   'required' => true ),
        
                                               'next'                    => array( 'name'     => 'next',
                                                                                   'datatype' => 'integer',
                                                                                   'default'  => 0,
                                                                                   'required' => false ),
        
                                               'user_id'             => array( 'name'     => 'user_id',
                                                                               'datatype' => 'integer',
                                                                               'default'  => null,
                                                                               'required' => true ),

                                               'requested_time'      => array( 'name'     => 'requested_time',
                                                                               'datatype' => 'integer',
                                                                               'default'  => time(),
                                                                               'required' => false ),
        
                                               'is_active'           => array( 'name'     => 'is_active',
                                                                               'datatype' => 'integer',
                                                                               'default'  => 0,
                                                                               'required' => false ),
                                            ),
                                            
                      'keys'                 => array( 'id' ),
                      'increment_key'        => 'id',
                      'class_name'           => 'SQLIScheduledImport',
                      'name'                 => 'sqliimport_scheduled',
                      'function_attributes'  => array( 'options'            => 'getOptions',
                                                       'user'               => 'getUser',
                                                       'handler_name'       => 'getHandlerName',
                                                       'status_string'      => 'getStatusString',
                                                       'user_has_access'    => 'userHasAccess' ),
                      'set_functions'        => array( 'options'        => 'setOptions',
                                                       'user'           => 'setUser' )
        );
    }
    
    /**
     * Universal getter
     * @param $name
     */
    public function __get( $name )
    {
        return $this->attribute( $name );
    }
    
    /**
     * Generic toString method
     */
    public function __toString()
    {
        return $this->attribute( 'label' );
    }
    
    /**
     * Get options
     * @return SQLIImportHandlerOptions
     */
    public function getOptions()
    {
        if ( !$this->options instanceof SQLIImportHandlerOptions && $this->attribute( 'options_serialized' ) )
            $this->options = unserialize( $this->attribute( 'options_serialized' ) );
        else
            $this->options = new SQLIImportHandlerOptions();
            
        return $this->options;
    }
    
    /**
     * Options setter
     * @param SQLIImportHandlerOptions $options
     */
    public function setOptions( SQLIImportHandlerOptions $options )
    {
        $this->options = $options;
        $this->setAttribute( 'options_serialized', serialize( $options ) );
    }
    
    /**
     * Returns user who requested the import
     * @return eZUser
     */
    public function getUser()
    {
        if ( !$this->user instanceof eZUser )
            $this->user = eZUser::fetch( $this->attribute( 'user_id' ) );
        
        return $this->user;
    }
    
    /**
     * User setter
     * @param eZUser $user
     */
    public function setUser( eZUser $user )
    {
        $this->user = $user;
        $this->setAttribute( 'user_id', $user->attribute( 'contentobject_id' ) );
    }
    
    /**
     * Returns import handler intelligible name as set in sqliimport.ini
     * @return string
     */
    public function getHandlerName()
    {
        $importINI = eZINI::instance( 'sqliimport.ini' );
        $handlerSection = $this->attribute( 'handler' ).'-HandlerSettings';
        $handlerName = $importINI->hasVariable( $handlerSection, 'Name' ) ? $importINI->variable( $handlerSection, 'Name' ) : $this->attribute( 'handler' );
        
        return $handlerName;
    }
    
    /**
     * Fetches scheduled import
     * @param int $offset Offset. Default is 0.
     * @param int $limit Limit. Default is null. If null, all imports items will be returned
     * @param array $conds Additional conditions for fetch. See {@link eZPersistentObject::fetchObjectList()}. Default is null
     * @return array( SQLIScheduledImport )
     */
    public static function fetchList( $offset = 0, $limit = null, $conds = null )
    {
        if( !$limit )
            $aLimit = null;
        else
            $aLimit = array( 'offset' => $offset, 'length' => $limit );
        
        $sort = array( 'requested_time' => 'asc' );
        $aImports = self::fetchObjectList( self::definition(), null, $conds, $sort, $aLimit );
        
        return $aImports;
    }
    
    /**
     * Fetches a scheduled import by its ID
     * @param int $importID
     */
    public static function fetch( $importID )
    {
        $import = self::fetchObject( self::definition(), null, array( 'id' => $importID ) );
        return $import;
    }
    
    /**
     * Sets attributes from an associative array (key = attribute name)
     * @param array $attributes
     */
    public function fromArray( array $attributes )
    {
        foreach( $attributes as $attributeName => $attribute )
        {
            if( $this->hasAttribute( $attributeName ) )
                $this->setAttribute( $attributeName, $attribute );
        }
    }
    
    /**
     * Updates next import date depending on frequency.
     * If frequency is set to 'none', entry will be removed
     */
    public function updateNextImport()
    {
        $db = eZDB::instance();
        $db->begin();
        
        if( $this->attribute( 'frequency' ) == self::FREQUENCY_NONE )
        {
            $this->remove();
        }
        else
        {
            $nextTime = null;
            // Determine next import interval
            switch( $this->attribute( 'frequency' ) )
            {
                case self::FREQUENCY_HOURLY :
                    $nextTime = '+1 hour';
                    break;
                
                case self::FREQUENCY_DAILY :
                    $nextTime = '+1 day';
                    break;
    
                case self::FREQUENCY_WEEKLY :
                    $nextTime = '+1 week';
                    break;
    
                case self::FREQUENCY_MONTHLY :
                    $nextTime = '+1 month';
                    break;
    
                default :
                    return false;
                    break;
            }
            
            $nextDate = strtotime( $nextTime, time() );
            $this->setAttribute( 'next', $nextDate );
            $this->store( array( 'next' ) );
        }
        
        $db->commit();
    }
    
    /**
     * Checks if current user has access to import item management (edit, remove...)
     * @return bool
     */
    public function userHasAccess()
    {
        // Check if user has access to handler alteration
        $aLimitation = array( 'SQLIImport_Type' => $this->attribute( 'handler' ) );
        $userHasAccess = SQLIImportUtils::hasAccessToLimitation( 'sqliimport', 'manageimports', $aLimitation );
        
        return $userHasAccess;
    }
}
