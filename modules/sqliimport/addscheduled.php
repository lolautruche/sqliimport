<?php
/**
 * SQLi Import add scheduled import view
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 */

$Module = $Params['Module'];
$Result = array();
$tpl = SQLIImportUtils::templateInit();
$importINI = eZINI::instance( 'sqliimport.ini' );
$http = eZHTTPTool::instance();

try
{
    $userLimitations = SQLIImportUtils::getSimplifiedUserAccess( 'sqliimport', 'manageimports' );
    $simplifiedLimitations = $userLimitations['simplifiedLimitations'];
    $scheduledImport = null;
    $importID = null;
    $currentImportHandler = null;
    $importOptions = null;
    $importDate = date( 'Y-m-d' );
    $importHour = date( 'H' );
    $importMinute = 0;
    $importFrequency = 'none';
    $importLabel = null;
    $importIsActive = true;
    $manualFrequency = 0;

    if( $Module->isCurrentAction( 'RequestScheduledImport' ) )
    {
        // Check if user has access to handler alteration
        $currentImportHandler = $Module->actionParameter( 'ImportHandler') ;
        $aLimitation = array( 'SQLIImport_Type' => $currentImportHandler );
        $hasAccess = SQLIImportUtils::hasAccessToLimitation( $Module->currentModule(), 'manageimports', $aLimitation );
        if( !$hasAccess )
            return $Module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' );

        $importID = (int)$Params['ScheduledImportID'];
        $importOptions = $Module->actionParameter( 'ImportOptions' );
        $importFrequency = $Module->actionParameter( 'ScheduledFrequency' );
        $importLabel = $Module->actionParameter( 'ScheduledLabel' );
        $importIsActive = $Module->actionParameter( 'ScheduledActive' ) ? 1 : 0;

        $aImportDate = explode( '-', $Module->actionParameter( 'ScheduledDate' ) );
        $importHour = (int)$Module->actionParameter( 'ScheduledHour' );
        $importMinute = (int)$Module->actionParameter( 'ScheduledMinute' );
        $nextDate = mktime( $importHour, $importMinute, 0, $aImportDate[1], $aImportDate[2], $aImportDate[0] );
        if( $nextDate == -1 ) // Bad date entered
            throw new SQLIImportBaseException( SQLIImportUtils::translate( 'extension/sqliimport/error', 'Please choose a correct date' ) );

        $row = array(
            'handler'               => $currentImportHandler,
            'user_id'               => eZUser::currentUserID(),
            'label'                 => $importLabel,
            'frequency'             => $importFrequency,
            'next'                  => $nextDate,
            'is_active'             => $importIsActive
        );

        // Handle frequency
        if( $importFrequency == SQLIScheduledImport::FREQUENCY_MANUAL )
        {
            $manualFrequency = (int)$Module->actionParameter( 'ManualScheduledFrequency' );
            if( $manualFrequency < 5 )
                throw new SQLIImportBaseException( SQLIImportUtils::translate( 'extension/sqliimport/error', 'Please choose a frequency greater than 5min' ) );

            $row['manual_frequency'] = $manualFrequency;
        }

        $scheduledImport = SQLIScheduledImport::fetch( $importID );
        if ( !$scheduledImport instanceof SQLIScheduledImport )
            $scheduledImport = new SQLIScheduledImport( $row );
        else
            $scheduledImport->fromArray( $row );

        if( $importOptions )
        {
            if( is_array( $importOptions ) )
            {
                $scheduledImport->setAttribute( 'options', SQLIImportHandlerOptions::fromHTTPInput( $importOptions ) );
            }
            else
            {
                //backwards compatibility mode : options are set in a textarea
                $scheduledImport->setAttribute( 'options', SQLIImportHandlerOptions::fromText( $importOptions ) );
            }
        }
        $scheduledImport->store();
        $Module->redirectToView( 'scheduledlist' );
    }
    else if( $Params['ScheduledImportID'] )
    {
        $scheduledImport = SQLIScheduledImport::fetch( $Params['ScheduledImportID'] );
        $importID = $Params['ScheduledImportID'];
        $currentImportHandler = $scheduledImport->attribute( 'handler' );

        // Check if user has access to handler alteration
        $aLimitation = array( 'SQLIImport_Type' => $currentImportHandler );
        $hasAccess = SQLIImportUtils::hasAccessToLimitation( $Module->currentModule(), 'manageimports', $aLimitation );
        if( !$hasAccess )
            return $Module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' );

        $importOptions = $scheduledImport->attribute( 'options' )->toText();
        $nextTime = $scheduledImport->attribute( 'next' );
        if( !$nextTime )
            $nextTime = $scheduledImport->attribute( 'requested_time' );
        $importDate = date( 'Y-m-d', $nextTime );
        $importHour = date( 'H', $nextTime );
        $importMinute = date( 'i', $nextTime );
        $importFrequency = $scheduledImport->attribute( 'frequency' );
        $importLabel = $scheduledImport->attribute( 'label' );
        $importIsActive = $scheduledImport->attribute( 'is_active' );
        $manualFrequency = $scheduledImport->attribute( 'manual_frequency' );
    }

}
catch( Exception $e )
{
    $errMsg = $e->getMessage();
    eZDebug::writeError( $errMsg );
    $tpl->setVariable( 'error_message', $errMsg );
}

$tpl->setVariable( 'import_id', $importID );
$tpl->setVariable( 'current_import_handler', $currentImportHandler );
$tpl->setVariable( 'import_options', $importOptions );
$tpl->setVariable( 'import_date', $importDate );
$tpl->setVariable( 'import_hour', $importHour );
$tpl->setVariable( 'import_minute', $importMinute );
$tpl->setVariable( 'import_frequency', $importFrequency );
$tpl->setVariable( 'import_label', $importLabel );
$tpl->setVariable( 'import_is_active', $importIsActive );
$tpl->setVariable( 'manual_frequency', $manualFrequency );

$importHandlers = $importINI->variable( 'ImportSettings', 'AvailableSourceHandlers' );
$aValidHandlers = array();
// Check if import handlers are enabled
foreach( $importHandlers as $handler )
{
    $handlerSection = $handler.'-HandlerSettings';
    if( $importINI->variable( $handlerSection, 'Enabled' ) === 'true' )
    {
        $handlerName = $importINI->hasVariable( $handlerSection, 'Name' ) ? $importINI->variable( $handlerSection, 'Name' ) : $handler;
        /*
         * Policy limitations check.
         * User has access to handler if it appears in $simplifiedLimitations['SQLIImport_Type']
         * or if $simplifiedLimitations['SQLIImport_Type'] is not set (no limitations)
         */
        if( ( isset( $simplifiedLimitations['SQLIImport_Type'] ) && in_array ($handler, $simplifiedLimitations['SQLIImport_Type'] ) )
            || !isset( $simplifiedLimitations['SQLIImport_Type'] ) )
            $aValidHandlers[$handlerName] = $handler;
    }
}

$tpl->setVariable( 'importHandlers', $aValidHandlers );

//session vars used by file uploader
$tpl->setVariable( 'session_id', session_id() );
$tpl->setVariable( 'session_name', session_name() );
$tpl->setVariable( 'user_session_hash', '' );

$Result['path'] = array(
    array(
        'url'       => false,
        'text'      => SQLIImportUtils::translate( 'extension/sqliimport', 'Edit a scheduled import' )
    )
);
$Result['left_menu'] = 'design:sqliimport/parts/leftmenu.tpl';
$Result['content'] = $tpl->fetch( 'design:sqliimport/addscheduled.tpl' );
