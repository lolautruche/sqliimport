<?php
/**
 * SQLi Import main list view
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
    $offset = isset( $Params['UserParameters']['offset'] ) ? (int)$Params['UserParameters']['offset'] : 0; // Offset for pagination
    $limit = eZPreferences::value( 'sqliimport_import_limit' );
    $limit = $limit ? $limit : 10; // Default limit is 10
    $imports = SQLIImportItem::fetchList( $offset, $limit );
    $importCount = SQLIImportItem::count( SQLIImportItem::definition() );
    $currentURI = '/'.$Module->currentModule().'/'.$Module->currentView();
    
    $tpl->setVariable( 'imports', $imports );
    $tpl->setVariable( 'offset', $offset );
    $tpl->setVariable( 'limit', $limit );
    $tpl->setVariable( 'uri', $currentURI );
    $tpl->setVariable( 'import_count', $importCount );
    $tpl->setVariable( 'view_parameters', $Params['UserParameters'] );
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
        'text'      => SQLIImportUtils::translate( 'extension/sqliimport', 'Import management list' )
    )
);
$Result['left_menu'] = 'design:sqliimport/parts/leftmenu.tpl';
$Result['content'] = $tpl->fetch( 'design:sqliimport/list.tpl' );
