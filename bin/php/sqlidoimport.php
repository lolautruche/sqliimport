<?php
/**
 * Main import script
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 */

require 'autoload.php';

$cli = eZCLI::instance();
$cli->setUseStyles(true);
$script = eZScript::instance( array( 'description' => ( "SQLIImport import script\n"),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

// Options processing
$options = $script->getOptions(
    '[source-handlers:][list-source-handlers][options:]',
    '',
    array(
        'source-handlers'           => 'Comma separated source handlers identifiers. If not provided, all source handlers will be processed.',
        'list-source-handlers'      => 'Lists all available handlers',
        'options'                   => 'Options for import handlers. Should be something like --options="handler1::foo=bar,foo2=baz|handler2::someoption=biz"'
    )
);
$script->initialize();
$script->setUseDebugAccumulators( true );

include_once 'extension/sqliimport/modules/sqliimport/fatalerrorhandler.php';

try
{
    $importINI = eZINI::instance( 'sqliimport.ini' );
    $aAvailableSourceHandlers = $importINI->variable( 'ImportSettings', 'AvailableSourceHandlers' );
    
    /*
     * List all available source handlers if requested, then exit
     */
    if( isset( $options['list-source-handlers'] ) )
    {
        if( $aAvailableSourceHandlers )
        {
            $cli->notice( 'Available source handlers :' );
            $cli->warning( implode( ', ', $aAvailableSourceHandlers ) );
        }
        else
        {
            $cli->error( 'No source handler defined !' );
        }
        $script->shutdown();
    }
    else
    {
        /*
         * Process requested import handlers
         * An SQLIImportItem object will be created and stored in DB for each handler
         */
        $requestedHandlers = $options['source-handlers'] ? $options['source-handlers'] : '';
        $aRequestedHandlers = $requestedHandlers ? explode( ',', $requestedHandlers ) : $importINI->variable( 'ImportSettings', 'AvailableSourceHandlers');
        $areValidHandlers = SQLIImportFactory::checkExistingHandlers( $aRequestedHandlers ); // An exception may be thrown if a handler is not defined in sqliimport.ini
        if( $aRequestedHandlers )
        {
            $aHandlersOptions = SQLIImportHandlerOptions::decodeHandlerOptionLine( $options['options'] );
            $importUser = eZUser::fetchByName( 'admin' ); // As this is a manual script, "Admin" user will be used to import
            $aImportItems = array();
            
            // First stores an SQLIImportItem for each handler to process
            foreach( $aRequestedHandlers as $handler )
            {
                $handlerOptions = isset( $aHandlersOptions[$handler] ) ? $aHandlersOptions[$handler] : null;
                $pendingImport = new SQLIImportItem( array(
                    'handler'               => $handler,
                    'user_id'               => $importUser->attribute( 'contentobject_id' ),
                ) );
                if ( $handlerOptions instanceof SQLIImportHandlerOptions )
                    $pendingImport->setAttribute( 'options', $handlerOptions );
                $pendingImport->store();
                $aImportItems[] = $pendingImport;
            }
            
            $importFactory = SQLIImportFactory::instance();
            $importFactory->runImport( $aImportItems );
            $importFactory->cleanup();
            $cli->notice( 'Import is over :)' );
        }
        else
        {
            $cli->warning( 'No import handler to process ! Check sqliimport.ini to define handlers' );
        }
    
        $memoryMax = memory_get_peak_usage();
        $memoryMax = round( $memoryMax / 1024 / 1024, 2 ); // Convert in Megabytes
        $cli->notice( 'Peak memory usage : '.$memoryMax.'M' );
    }
    
    $script->shutdown();
}
catch( Exception $e )
{
    $errCode = $e->getCode();
    $errCode = $errCode != 0 ? $errCode : 1; // If an error has occured, script must terminate with a status other than 0
    $script->shutdown( $errCode, $e->getMessage() );
}
