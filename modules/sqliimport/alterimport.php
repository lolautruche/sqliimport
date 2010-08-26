<?php
/**
 * SQLi Import import alteration view
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 */

$Module = $Params['Module'];
$Result = array();
$tpl = SQLIImportUtils::templateInit();

try
{
    $user = eZUser::currentUser();
    $userID = $user->attribute( 'contentobject_id' );
    $userLogin = $user->attribute( 'login' );
    $action = $Params['Action'];
    $importID = $Params['ImportID'];
    $import = SQLIImportItem::fetch( $importID );
    if ( !$import instanceof SQLIImportItem )
        throw new SQLIImportBaseException( SQLIImportUtils::translate( 'extension/sqliimport/error',
                                                                       "No import item found with ID #%importID",
                                                                        null, array( '%importID' => $importID) ) );

    // Check if user has access to handler alteration
    $aLimitation = array( 'SQLIImport_Type' => $import->attribute( 'handler' ) );
    $hasAccess = SQLIImportUtils::hasAccessToLimitation( $Module->currentModule(), 'manageimports', $aLimitation );
    if( !$hasAccess )
        return $Module->handleError( eZError::KERNEL_ACCESS_DENIED, 'kernel' );

    switch( $action )
    {
        case 'cancel':
            // Check if import is already running. Maybe user has not refreshed import list in the admin...
            $status = ( $import->attribute( 'status' ) == SQLIImportItem::STATUS_RUNNING ) ? SQLIImportItem::STATUS_INTERRUPTED : SQLIImportItem::STATUS_CANCELED;
            
            SQLIImportLogger::logNotice(
                'User "'.$userLogin.'" (#'.$userID.') requested cancelation of pending import #'.$importID.' on '.date( 'Y-m-d H:i' ),
                false
            );
            $import->setAttribute( 'status', $status );
            $import->store();
            break;
            
        case 'interrupt':
            SQLIImportLogger::logNotice(
                'User "'.$userLogin.'" (#'.$userID.') requested interruption of running import #'.$importID.' on '.date( 'Y-m-d H:i' ),
                false
            );
            $import->setAttribute( 'status', SQLIImportItem::STATUS_INTERRUPTED );
            $import->store();
            break;
            
        default:
            throw new SQLIImportBaseException( SQLIImportUtils::translate( 'extension/sqliimport/error',
                                                                           "Unknown alter import action '%action'",
                                                                            null, array( '%action' => $action) ) );
    }
    
    $Module->redirectToView( 'list' );
}
catch( Exception $e )
{
    $errMsg = $e->getMessage();
    SQLIImportLogger::writeError( $errMsg );
    $tpl->setVariable( 'error_message', $errMsg );
    
    $Result['path'] = array(
        array(
            'url'       => false,
            'text'      => SQLIImportUtils::translate( 'extension/sqliimport/error', 'Error' )
        )
    );
    $Result['left_menu'] = 'design:sqliimport/parts/leftmenu.tpl';
    $Result['content'] = $tpl->fetch( 'design:sqliimport/altererror.tpl' );
}

