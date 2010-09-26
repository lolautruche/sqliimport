<?php
/**
 * Cronjob running scheduled and "one shot" imports as configured in the admin
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 */

include_once 'extension/sqliimport/modules/sqliimport/sigtermhandler.php';
include_once 'extension/sqliimport/modules/sqliimport/fatalerrorhandler.php';

try
{
    $importINI = eZINI::instance( 'sqliimport.ini' );
    $aAvailableSourceHandlers = $importINI->variable( 'ImportSettings', 'AvailableSourceHandlers' );
    
    // ##########
    // ##### Immediate imports
    // ##########
    
    $aImmediateImports = SQLIImportItem::fetchPendingList();
    if( count( $aImmediateImports ) > 0 )
    {
        $cli->warning( 'Now handling immediate imports' );
        $importFactory = SQLIImportFactory::instance();
        $importFactory->runImport( $aImmediateImports );
        $importFactory->cleanup();
    }
    unset( $aImmediateImports );
    
    // ##########
    // ##### End Immediate imports
    // ##########
    
    // ####################################################

    // ##########
    // ##### Scheduled imports
    // ##########
    
    // First fetch all scheduled imports to be processed
    $currentTimestamp = time();
    $conds = array(
        'is_active'     => 1,
        'next'          => array( '<=', $currentTimestamp )
    );
    $aScheduledImports = SQLIScheduledImport::fetchList( 0, null, $conds );
    
    // Then create a pending SQLIImportItem for each scheduled import
    if( count( $aScheduledImports ) > 0  )
    {
        $cli->warning( 'Now handling scheduled imports' );
        $aImportItems = array();
        foreach( $aScheduledImports as $scheduledImport )
        {
            // Create pending import
            $aImportItems[] = SQLIImportItem::fromScheduledImport( $scheduledImport );
        }
        
        $importFactory = SQLIImportFactory::instance();
        $importFactory->setScheduledImports( $aScheduledImports );
        $importFactory->runImport( $aImportItems );
        $importFactory->cleanup();
        unset( $aImportItems, $aScheduledImports );
    }
    
    // ##########
    // ##### End Scheduled imports
    // ##########
    
    $cli->notice( 'Import is over :)' );
    
    $memoryMax = memory_get_peak_usage(); // Result is in bytes
    $memoryMax = round( $memoryMax / 1024 / 1024, 2 ); // Convert in Megabytes
    $cli->notice( 'Peak memory usage : '.$memoryMax.'M' );
    
}
catch( Exception $e )
{
    $cli->error( 'An error has occurred : '.$e->getMessage() );
}

