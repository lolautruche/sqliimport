<?php
/**
 * File containing SQLIImportItem class
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 */

/**
 * Class handling items
 * Objects of this class are stored in ezpending_actions table and are normally processed by CRON to trigger an import.
 */
class SQLIImportItem extends eZPersistentObject
{
    const ACTION_PENDING_IMPORT = 'sqliimport_pending';
    
    const STATUS_PENDING = 0,
          STATUS_RUNNING = 1,
          STATUS_FAILED = 2,
          STATUS_COMPLETED = 3,
          STATUS_CANCELED = 4,
          STATUS_INTERRUPTED = 5;
          
    const TYPE_IMMEDIATE = 1,
          TYPE_SCHEDULED = 2;
    
    /**
     * Internal eZPendingActions object
     * @var eZPendingActions
     * @internal
     */
    protected $pendingAction;
    
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
    
    public static function definition()
    {
        return array( 'fields'       => array( 'id'                   => array( 'name'     => 'id',
                                                                                'datatype' => 'integer',
                                                                                'default'  => null,
                                                                                'required' => true ),

                                               'handler'             => array( 'name'     => 'handler',
                                                                               'datatype' => 'string',
                                                                               'default'  => null,
                                                                               'required' => true ),

                                               'options_serialized'  => array( 'name'     => 'options_serialized',
                                                                               'datatype' => 'string',
                                                                               'default'  => null,
                                                                               'required' => false ),

                                               'user_id'             => array( 'name'     => 'user_id',
                                                                               'datatype' => 'integer',
                                                                               'default'  => null,
                                                                               'required' => true ),

                                               'requested_time'      => array( 'name'     => 'requested_time',
                                                                               'datatype' => 'integer',
                                                                               'default'  => time(),
                                                                               'required' => false ),

                                               'status'              => array( 'name'     => 'status',
                                                                               'datatype' => 'integer',
                                                                               'default'  => 0,
                                                                               'required' => false ),

                                               'percentage_int'      => array( 'name'     => 'percentage',
                                                                               'datatype' => 'integer',
                                                                               'default'  => 0,
                                                                               'required' => false),
        
                                               'type'                => array( 'name'     => 'type',
                                                                               'datatype' => 'integer',
                                                                               'default'  => 1,
                                                                               'required' => true),
        
                                               'progression_notes'   => array( 'name'     => 'progression_notes',
                                                                               'datatype' => 'string',
                                                                               'default'  => null,
                                                                               'required' => false ),
        
                                               'process_time'        => array( 'name'     => 'process_time',
                                                                               'datatype' => 'integer',
                                                                               'default'  => 0,
                                                                               'required' => true ),
                                            ),
                                            
                      'keys'                 => array( 'id' ),
                      'increment_key'        => 'id',
                      'class_name'           => 'SQLIImportItem',
                      'name'                 => 'sqliimport_item',
                      'function_attributes'  => array( 'options'                    => 'getOptions',
                                                       'user'                       => 'getUser',
                                                       'handler_name'               => 'getHandlerName',
                                                       'status_string'              => 'getStatusString',
                                                       'percentage'                 => 'getPercentage',
                                                       'type_string'                => 'getTypeString',
                                                       'user_has_access'            => 'userHasAccess',
                                                       'process_time_formated'      => 'getFormatedProcessTime' ),
                      'set_functions'        => array( 'options'        => 'setOptions',
                                                       'user'           => 'setUser',
                                                       'percentage'     => 'setPercentage' )
        );
    }
    
    /**
     * Sets attributes directly from an associative array.
     * Key is the attribute name
     * @param array $attributes
     */
    public function fromArray( array $attributes )
    {
        foreach( $attributes as $attrName => $attrValue )
        {
            if( isset( $this->attributesHolder[$attrName] ) )
                $this->attributesHolder[$name] = $value;
            else
                throw new SQLIImportRuntimeException( SQLIImportUtils::translate( 'extension/sqliimport/error',
                                                                                  'SQLIPendingImport : Unknown attribute "%attribute"',
                                                                                   null,
                                                                                   $name ) );
        }
    }
    
    /**
     * Universal getter
     * @param $name
     */
    public function __get( $name )
    {
        $ret = null;
        if( $this->hasAttribute( $name ) )
            $ret = $this->attribute( $name );
            
        return $ret;
    }
    
    /**
     * Get options
     * @return SQLIImportHandlerOptions
     */
    public function getOptions()
    {
        if ( !$this->options instanceof SQLIImportHandlerOptions && $this->attribute( 'options_serialized' ) )
            $this->options = unserialize( $this->attribute( 'options_serialized' ) );
            
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
     * Fetches pending imports
     * @return array( SQLIImportItem )
     */
    public static function fetchPendingList()
    {
        $conds = array(
            'status'    => self::STATUS_PENDING
        );
        $sort = array( 'requested_time' => 'asc' );
        $aFinalPending = self::fetchObjectList( self::definition(), null, $conds, $sort );
        
        return $aFinalPending;
    }
    
    /**
     * Fetches import items
     * @param int $offset Offset. Default is 0.
     * @param int $limit Limit. Default is null. If null, all imports items will be returned
     * @param array $conds Additional conditions for fetch. See {@link eZPersistentObject::fetchObjectList()}. Default is null
     * @return array( SQLIImportItem )
     */
    public static function fetchList( $offset = 0, $limit = 0, $conds = null )
    {
        if( !$limit )
            $aLimit = null;
        else
            $aLimit = array( 'offset' => $offset, 'length' => $limit );
        
        $sort = array( 'requested_time' => 'desc' );
        $aImports = self::fetchObjectList( self::definition(), null, $conds, $sort, $aLimit );
        
        return $aImports;
    }
    
    /**
     * Fetches an Import Item by its ID
     * @param int $importID
     */
    public static function fetch( $importID )
    {
        $import = self::fetchObject( self::definition(), null, array( 'id' => $importID ) );
        return $import;
    }
    
    /**
     * Fetches all running imports
     * @return array( SQLIImportItem )
     */
    public static function fetchRunning( $handlerIdentifier = null )
    {
        $conds = array(
            'status'    => self::STATUS_RUNNING
        );
        if( $handlerIdentifier )
            $conds['handler'] = $handlerIdentifier;
        
        $runningImports = self::fetchObjectList( self::definition(), null, $conds );
        return $runningImports;
    }
    
    /**
     * Returns intelligible status as string
     * @return string
     */
    public function getStatusString()
    {
        $statusString = '';
        switch( $this->attribute( 'status' ) )
        {
            case self::STATUS_PENDING:
                $statusString = 'Pending';
                break;
                
            case self::STATUS_RUNNING:
                $statusString = 'Running';
                break;
            
            case self::STATUS_FAILED:
                $statusString = 'Failed';
                break;
                
            case self::STATUS_COMPLETED:
                $statusString = 'Completed';
                break;
                
            case self::STATUS_CANCELED:
                $statusString = 'Canceled';
                break;
                
            case self::STATUS_INTERRUPTED:
                $statusString = 'Interrupted';
                break;
        }
        
        return $statusString;
    }
    
    /**
     * Updates import progress
     * @param float $advancement Percentage of advancement
     * @param string $progressionNotes Notes for import item progression. Can be anything (an ID, a reference...)
     *                                 Can be for example ID of row your import handler has just processed
     */
    public function updateProgress( $progressPercentage, $progressionNotes )
    {
        $percentage = $this->attribute( 'percentage' ) + $progressPercentage;
        $this->setAttribute( 'percentage', $percentage );
        $this->setAttribute( 'progression_notes', $progressionNotes );
        $this->store( array( 'percentage_int', 'progression_notes' ) ); // Only stores those fields in order not to overwrite others (like 'status')
    }
    
    /**
     * Returns real progress percentage as float.
     * Percentage is stored as INT in DB (real percentage * 100), for convenience.
     * @return float
     */
    public function getPercentage()
    {
        return $this->attribute( 'percentage_int' ) / 100;
    }
    
    /**
     * Updates percentage
     * Will round $percentage (precision = 2) and multiply result by 100.
     * Result will be stored in percentage_int field
     * @param float $percentage
     */
    public function setPercentage( $percentage )
    {
        $percentage = round( $percentage, 2 );
        $this->setAttribute( 'percentage_int', $percentage * 100 );
    }
    
    /**
     * Get intelligible type string
     * @return string
     */
    public function getTypeString()
    {
        $typeString = '';
        switch( $this->attribute( 'type' ) )
        {
            case self::TYPE_IMMEDIATE:
                $typeString = 'Immediate';
                break;
                
            case self::TYPE_SCHEDULED:
                $typeString = 'Scheduled';
                break;
        }
        
        return $typeString;
    }
    
    /**
     * Creates a new pending import from a scheduled one
     * @param SQLIScheduledImport $scheduledImport
     */
    public static function fromScheduledImport( SQLIScheduledImport $scheduledImport )
    {
        $pendingImport = new self( array(
            'handler'               => $scheduledImport->attribute( 'handler' ),
            'user_id'               => $scheduledImport->attribute( 'user_id' ),
            'options_serialized'    => $scheduledImport->attribute( 'options_serialized' ),
            'type'                  => self::TYPE_SCHEDULED
        ) );
        $pendingImport->store();
        
        return $pendingImport;
    }
    
    /**
     * Checks if current import has been interrupted by a user in the admin.
     * @return bool
     */
    public function isInterrupted()
    {
        $isInterrupted = false;
        $conds = array( 'id' => $this->attribute( 'id' ) );
        $ret = parent::fetchObject( self::definition(), array( 'status' ), $conds, false );
        if( $ret && $ret['status'] == self::STATUS_INTERRUPTED )
            $isInterrupted = true;
        
        return $isInterrupted;
    }
    
    /**
     * Purges import history
     */
    public static function purgeImportHistory()
    {
        $db = eZDB::instance();
        $conds = array(
            'status'        => array( array(
                self::STATUS_COMPLETED,
                self::STATUS_FAILED,
                self::STATUS_CANCELED,
                self::STATUS_INTERRUPTED
            ) )
        );
        $db->begin();
        self::removeObject( self::definition(), $conds );
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
    
    public function getFormatedProcessTime()
    {
        $aTime = array(
            'hour'      => (int)( $this->attribute( 'process_time' ) / eZTime::SECONDS_AN_HOUR ),
            'minute'    => (int)( ( $this->attribute( 'process_time' ) % eZTime::SECONDS_AN_HOUR ) / eZTime::SECONDS_A_MINUTE ),
            'second'    => (int)( $this->attribute( 'process_time' ) % eZTime::SECONDS_A_MINUTE )
        );
        return $aTime;
    }
}