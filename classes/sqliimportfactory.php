<?php
/**
 * Import factory
 * Will trigger import for all declared and activated import handlers
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 */

final class SQLIImportFactory
{
    /**
     * Object Instance
     * @var SQLIImport
     */
    private static $_instance;
    
    /**
     * @var eZINI
     */
    private $importINI;
    
    /**
     * @var eZCLI
     */
    private $cli;
    
    /**
     * Available source handlers, as declared in sqliimport.ini
     * @var array
     */
    private $aAvailableSourceHandlers;
    
    /**
     * eZ Publish user used to do the import
     * @var eZUser
     */
    private $robotUser;
    
    /**
     * Import token
     * @var SQLIImportToken
     */
    private $token;
    
    /**
     * Options for import handlers.
     * @var SQLIImportHandlerOptions
     */
    private $handlerOptions;
    
    /**
     * @var ezcConsoleOutput
     */
    private $output;
    
    /**
     * Flag indicating if ViewCaching was enabled before import process
     * @var bool
     */
    private $viewCachingEnabledGlobal;
    
    /**
     * Flag indicating if DelayedIndexing was enabled before import process
     * @var bool
     */
    private $delayedIndexingEnabledGlobal;
    
    /**
     * @var SQLIImportItem
     */
    private $currentImportItem;
    
    /**
     * Array of scheduled imports to be processed.
     * Kept to update next import date
     * @var array( SQLIScheduledImport )
     */
    private $scheduledImports;
    
    /**
     * Private constructor.
     * To instantiate factory, please use Singleton pattern : <code>$factory = SQLIImportFactory::instance();</code>
     * @see self::instance()
     */
    private function __construct()
    {
        $this->importINI = eZINI::instance( 'sqliimport.ini' );
        $this->cli = eZCLI::instance();
        
        $this->aAvailableSourceHandlers = $this->importINI->variable( 'ImportSettings', 'AvailableSourceHandlers' );
        $this->robotUser = $this->setLoggedInUser();
        $this->output = new ezcConsoleOutput();
        
        SQLIImportToken::setIsImportScript( true );
    }
    
    /**
     * Singleton
     * @return SQLIImportFactory
     */
    public static function instance()
    {
        if( !self::$_instance instanceof SQLIImportFactory )
            self::$_instance = new self();
            
        return self::$_instance;
    }
    
    /**
     * Logs in the right user to do the import.
     * RobotUserID in sqliimport.ini is taken as a param if provided. Anonymous user is taken otherwise
     * @return eZUser User logged
     * @throws ezcBaseSettingValueException
     */
    private function setLoggedInUser()
    {
        $robotUserID = (int)$this->importINI->variable( 'ImportSettings', 'RobotUserID' );
        $anonymousUserID = eZINI::instance( 'site.ini' )->variable( 'UserSettings', 'AnonymousUserID' );
        if( !$robotUserID ) // If no user id provided, take AnonymousUserID as a default value
        {
            $robotUserID = $anonymousUserID;
        }
        else if( !is_int( $robotUserID ) ) // Invalid value
        {
            throw new ezcBaseSettingValueException( 'RobotUserID', $robotUserID, 'A valid UserID as integer' );
        }
        
        $robotUser = eZUser::fetch( $robotUserID );
        if( !$robotUser instanceof eZUser && $robotUserID != $anonymousUserID )
            throw new ezcBaseSettingValueException( 'RobotUserID', $robotUserID, 'A valid UserID as integer' );
            
        eZUser::setCurrentlyLoggedInUser( $robotUser, $robotUser->attribute( 'contentobject_id' ) );
        
        return $robotUser;
    }
    
    /**
     * Starts to run the import
     * @param array( SQLIImportItem ) $aImportItems
     * @throws SQLIImportBaseException
     * @throws ezcConfigurationNoConfigException
     */
    public function runImport( array $aImportItems )
    {
        // First check if an import is already running
        if( SQLIImportToken::importIsRunning() )
            throw new SQLIImportBaseException( 'Another import is already running. Aborting...', SQLIImportBaseException::IMPORT_ALREADY_RUNNING );
        
        $this->token = SQLIImportToken::registerNewImport();
        $this->handlePerformanceSettings();
        
        if( empty( $aImportItems ) ) // If no source handler is provided, consider processing all source handlers available
            $aImportItems = $this->importINI->variable( 'ImportSettings', 'AvailableSourceHandlers');
        
        // Process import items one by one
        for( $i=0, $iMax=count( $aImportItems ); $i<$iMax; ++$i )
        {
            try
            {
                if ( !$aImportItems[$i] instanceof SQLIImportItem )
                    throw new SQLIImportRuntimeException( 'Invalid import item !' );
                
                // Update status for import item
                $aImportItems[$i]->setAttribute( 'status', SQLIImportItem::STATUS_RUNNING );
                $aImportItems[$i]->store();
                $this->currentImportItem = $aImportItems[$i];
                
                // First check if this handler has all needed configuration
                $handler = $aImportItems[$i]->attribute( 'handler' );
                $handlerSection = $handler.'-HandlerSettings';
                if( !$this->importINI->hasSection( $handlerSection ) ) // Check INI Section
                    throw new ezcConfigurationNoConfigException( 'Error : Handler "'.$handler.'" does not have proper config section in sqliimport.ini !' );
                
                if( !$this->importINI->hasVariable( $handlerSection, 'ClassName' ) ) // Check if ClassName is properly defined
                    throw new ezcConfigurationNoConfigException( 'Error : ClassName not defined for "'.$handler.'" in sqliimport.ini !' );
                
                // Default values
                $handlerClassName = $this->importINI->variable( $handlerSection, 'ClassName' );
                $handlerEnabled = true;
                $debug = false;
                $defaultParentNodeID = $this->importINI->variable( 'ImportSettings', 'DefaultParentNodeID' );
                $streamTimeout = $this->importINI->variable( 'ImportSettings', 'StreamTimeout' );
                
                if( $this->importINI->hasVariable( $handlerSection, 'Enabled' ) )
                    $handlerEnabled = $this->importINI->variable( $handlerSection, 'Enabled' ) === 'true';
                    
                if( $this->importINI->hasVariable( $handlerSection, 'Debug' ) )
                    $debug = $this->importINI->variable( $handlerSection, 'Debug' ) === 'enabled';
                    
                if( $this->importINI->hasVariable( $handlerSection, 'DefaultParentNodeID' ) )
                {
                    $localParentNodeID = $this->importINI->variable( $handlerSection, 'DefaultParentNodeID' );
                    $defaultParentNodeID = is_int( $localParentNodeID ) ? (int)$localParentNode : $defaultParentNodeID;
                }
                    
                if( $this->importINI->hasVariable( $handlerSection, 'StreamTimeout' ) )
                    $streamTimeout = (int)$this->importINI->variable( $handlerSection, 'StreamTimeout' );

                // Check $defaultParentNodeID and throw an exception if not consistent
                $parentNode = eZContentObjectTreeNode::fetch( $defaultParentNodeID, false, false );
                if( !$parentNode )
                    throw new SQLIImportRuntimeException( 'Error : invalid DefaultParentNodeID ( '.$defaultParentNodeID.' )' );
                unset( $parentNode );
                
                // Check handler class validity
                if( !class_exists( $handlerClassName ) )
                    throw new SQLIImportRuntimeException( 'Error : invalid handler class "'.$handlerClassName.'". Did you regenerate autolads ?' );
                    
                // ####################################
                // ##### IMPORT HANDLER PROCESSING
                // ####################################
                // Instantiate the handler with appropriate options and process it.
                // Handler must implement ISQLIImportHandler and extend SQLIImportAbstractHandler
                $handlerOptions = $aImportItems[$i]->attribute( 'options' );
                $importHandler = new $handlerClassName( $handlerOptions );
                if( !$importHandler instanceof ISQLIImportHandler || !$importHandler instanceof SQLIImportAbstractHandler )
                    throw new SQLIImportRuntimeException( 'Error : invalid handler "'.$handlerClassName.'". Must implement ISQLIImportHandler and extend SQLIImportAbstractHandler.' );
                
                $importHandler->handlerConfArray = $this->importINI->group( $handlerSection );
                $importHandler->initialize();
                // Get process length to calculate advancement percentage to track advancement
                $processLength = $importHandler->getProcessLength();
                if( $processLength > 0 )
                {
                    $percentageAdvancementStep = 100 / $processLength;
                }
                else
                {
                    $percentageAdvancementStep = 0;
                }
                $handlerName = $importHandler->getHandlerName();
                $handlerIdentifier = $importHandler->getHandlerIdentifier();
                
                // Progress bar implementation
                $progressBarOptions = array(
                    'emptyChar'         => ' ',
                    'barChar'           => '='
                );
                $progressBar = new ezcConsoleProgressbar( $this->output, $processLength, $progressBarOptions );
                $progressBar->start();
                $this->cli->warning( 'Now processing "'.$handlerName.'" handler.' );
                
                $isInterrupted = false;
                while( $row = $importHandler->getNextRow() )
                {
                    try
                    {
                        $progressBar->advance();
                        $startTime = time();
                        $importHandler->process( $row );
                    }
                    catch( Exception $e )
                    {
                        SQLIImportLogger::logError( 'An error occurred during "'.$handlerIdentifier.'" import process : '.$e->getMessage() );
                    }
                    
                    $aImportItems[$i]->updateProgress( $percentageAdvancementStep, $importHandler->getProgressionNotes() );
                    
                    // Now calculate process time for this iteration
                    $endTime = time();
                    $diffTime = $endTime - $startTime;
                    $oldProcessTime = $aImportItems[$i]->attribute( 'process_time' );
                    $aImportItems[$i]->setAttribute( 'process_time', $oldProcessTime + $diffTime );
                    $aImportItems[$i]->store( array( 'process_time' ) );
                    
                    // Interruption handling
                    if( $aImportItems[$i]->isInterrupted() )
                    {
                        $this->cli->notice();
                        SQLIImportLogger::logNotice( 'Interruption has been requested for current import ! Cleaning and aborting process...' );
                        $isInterrupted = true;
                        break;
                    }
                }
                
                $importHandler->cleanup();
                $progressBar->finish();
                $this->cli->notice();
                unset( $importHandler );
                
                
                if( !$isInterrupted )
                {
                    $aImportItems[$i]->setAttribute( 'status', SQLIImportItem::STATUS_COMPLETED );
                    $aImportItems[$i]->setAttribute( 'percentage', 100 ); // Force percentage to 100%
                    $aImportItems[$i]->store();
                }
                
                // ####################################
                // ##### END IMPORT HANDLER PROCESSING
                // ####################################
            }
            catch( Exception $e )
            {
                SQLIImportLogger::logError( $e->getMessage() );
                $aImportItems[$i]->setAttribute( 'status', SQLIImportItem::STATUS_FAILED );
                $aImportItems[$i]->store();
                if( isset( $importHandler ) )
                {
                    $importHandler->cleanup();
                    unset( $importHandler );
                }
                continue;
            }
        }
    }
    
    /**
     * Cleans up import process
     */
    public function cleanup()
    {
        SQLIImportLogger::writeNotice( 'Now cleaning the import process' );
        $this->token = null;
        SQLIImportToken::cleanAll();
        $this->restorePerformanceSettings();
        
        // Update scheduled imports
        if( is_array( $this->scheduledImports ) )
        {
            foreach( $this->scheduledImports as $scheduledImport )
            {
                if ( !$scheduledImport instanceof SQLIScheduledImport )
                {
                    SQLIImportLogger::logError( __METHOD__.'$scheduledImport is not an instance of SQLIScheduledImport !' );
                    continue;
                }
                
                $scheduledImport->updateNextImport();
            }
        }
        
        unset( $this->scheduledImports );
    }
    
    /**
     * Checks if given handlers are declared in sqliimport.ini
     * If at least one of the handlers is not defined, this method will throw a RuntimeException Exception
     *
     * @param array $aHandlers
     * @return bool
     * @throws RuntimeException
     */
    public static function checkExistingHandlers( array $aHandlers )
    {
        $importINI = eZINI::instance( 'sqliimport.ini' );
        $availableHandlers = $importINI->variable( 'ImportSettings', 'AvailableSourceHandlers' );
        $unsupportedHandlers = array();
        
        if( $aHandlers ) // If $aHandlers is empty, then all defined handlers will be processed
        {
            foreach ( $aHandlers as $handler )
            {
                if( !in_array( $handler, $availableHandlers ) )
                {
                    $unsupportedHandlers[] = $handler;
                }
            }
            
            if( $unsupportedHandlers )
                throw new RuntimeException( 'Error : Undefined handler(s) "'.implode(', ', $unsupportedHandlers).'"', SQLIImportBaseException::UNDEFINED_HANDLERS );
        }
        
        return true;
    }
    
    /**
     * Returns eZ Publish user used to do the import
     */
    public function getRobotUser()
    {
        return $this->robotUser;
    }
    
    /**
     * Handles performance settings
     */
    private function handlePerformanceSettings()
    {
        $this->handleViewCachePrevention();
        $this->handleSearchIndexPrevention();
    }
    
    /**
     * May deactivate view cache for current import script if so configured
     * Result is faster import but imported content objects will need to be cache cleared later via sqliimport_cleanup cronjob
     */
    private function handleViewCachePrevention()
    {
        $siteINI = eZINI::instance();
        $viewCacheEnabled = $this->importINI->variable( 'ImportSettings', 'ViewCaching' ) === 'enabled';
        if( !$viewCacheEnabled )
        {
            $this->viewCachingEnabledGlobal = $siteINI->variable( 'ContentSettings', 'ViewCaching' ) === 'enabled';
            $siteINI->setVariable( 'ContentSettings', 'ViewCaching', 'disabled' );
        }
    }
    
    /**
     * May activate SearchSettings.DelayedIndexing for current import script if so configured
     * Result is faster import but imported content objects will need to be indexed later
     * (by indexcontent cronjob or sqliimport_cleanup cronjob)
     */
    private function handleSearchIndexPrevention()
    {
        $siteINI = eZINI::instance();
        $objectIndexingEnabled = $this->importINI->variable( 'ImportSettings', 'ObjectIndexing' ) === 'enabled';
        if( !$objectIndexingEnabled )
        {
            $this->delayedIndexingEnabledGlobal = $siteINI->variable( 'SearchSettings', 'DelayedIndexing' ) === 'enabled';
            $siteINI->setVariable( 'SearchSettings', 'DelayedIndexing', 'enabled' );
        }
    }
    
    /**
     * Restores performance settings as they were set before import process
     */
    private function restorePerformanceSettings()
    {
        $siteINI = eZINI::instance();
        if( $this->viewCachingEnabledGlobal ) // Restore view cache (enable it if it was enabled before import)
            $siteINI->setVariable( 'ContentSettings', 'ViewCaching', 'enabled' );
            
        if( !$this->delayedIndexingEnabledGlobal ) // Restore DelayedIndexing (disable it if it was disabled before import)
            $siteINI->setVariable( 'SearchSettings', 'DelayedIndexing', 'disabled' );
    }
    
    /**
     * Returns current import item
     * @return SQLIImportItem
     */
    public function getCurrentImportItem()
    {
        return $this->currentImportItem;
    }
    
    /**
     * Sets scheduled imports
     * @param array $aScheduled
     */
    public function setScheduledImports( array $aScheduled )
    {
        $this->scheduledImports = $aScheduled;
    }
}