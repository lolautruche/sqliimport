<?php
/**
 * SQLi Import add immediate import view
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

    if( $Module->isCurrentAction( 'RequestImport' ) )
    {
        // Check if user has access to handler alteration
        $aLimitation = array( 'SQLIImport_Type' => $Module->actionParameter( 'ImportHandler' ) );
        $hasAccess = SQLIImportUtils::hasAccessToLimitation( $Module->currentModule(), 'manageimports', $aLimitation );
        if( !$hasAccess )
            return $Module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' );

        $importOptions = $Module->actionParameter( 'ImportOptions' );
        $pendingImport = new SQLIImportItem( array(
            'handler'               => $Module->actionParameter( 'ImportHandler' ),
            'user_id'               => eZUser::currentUserID()
        ) );

        if( $importOptions )
        {
            if( is_array( $importOptions ) )
            {
                $pendingImport->setAttribute( 'options', SQLIImportHandlerOptions::fromHTTPInput( $importOptions ) );
            }
            else
            {
                //backwards compatibility mode : options are set in a textarea
                $pendingImport->setAttribute( 'options', SQLIImportHandlerOptions::fromText( $importOptions ) );
            }
        }
        $pendingImport->store();
        $Module->redirectToView( 'list' );
    }

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
}
catch( Exception $e )
{
    $errMsg = $e->getMessage();
    SQLIImportLogger::writeError( $errMsg );
    $tpl->setVariable( 'error_message', $errMsg );
}

$Result['path'] = array(
    array(
        'url'       => false,
        'text'      => SQLIImportUtils::translate( 'extension/sqliimport', 'Request a new immediate import' )
    )
);
$Result['left_menu'] = 'design:sqliimport/parts/leftmenu.tpl';
$Result['content'] = $tpl->fetch( 'design:sqliimport/addimport.tpl' );
