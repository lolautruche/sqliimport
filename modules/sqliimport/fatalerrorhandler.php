<?php
/**
 * Fatal error handler
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 */

/**
 * Fatal Error Handler
 * @return void
 */
function SQLIImportErrorCleanup()
{
    global $script; // eZScript
    if ( !$script instanceof eZScript ) // Just make sure $script is an instance of eZScript
    {
        return;
    }
    
    SQLIImportToken::cleanAll();
    $factory = SQLIImportFactory::instance();
    $currentItem = $factory->getCurrentImportItem();
    $currentItem->setAttribute( 'status', SQLIImportItem::STATUS_FAILED );
    $currentItem->store();
    
    $aLastError = error_get_last();
    if( $aLastError )
        SQLIImportLogger::logError( $aLastError['message'].' at '.$aLastError['file'].' (line '.$aLastError['line'].')' );
        
    SQLIImportLogger::logError( 'An error has occurred during import process. The import status has been updated.' );
    $script->shutdown( 1 );
}
eZExecution::addFatalErrorHandler( 'SQLIImportErrorCleanup' ); // Registering Fatal Error handler

/**
 * Cleanup handler, in case of a non caught DB transaction error
 * @return void
 */
function SQLIImportCleanupHandler()
{
    // Check if an error has occurred and report it if necessary
    $db = eZDB::instance();
    if( $db->errorNumber() > 0 )
    {
        SQLIImportToken::cleanAll();
        $factory = SQLIImportFactory::instance();
        $currentItem = $factory->getCurrentImportItem();
        $currentItem->setAttribute( 'status', SQLIImportItem::STATUS_FAILED );
        $currentItem->store();
        
        SQLIImportLogger::logError( 'A DB transaction error occurred : #'.$db->errorNumber().' - "'.$db->errorMessage().'"' );
        SQLIImportLogger::logError( 'An error has occurred during import process. The import status has been updated.' );
    }
}
eZExecution::addCleanupHandler( 'SQLIImportCleanupHandler' );