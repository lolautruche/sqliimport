<?php

include( 'extension/sqliimport/stubs/scriptinit.php' );
eZINI::instance()->setVariable( 'ContentSettings', 'ViewCaching', 'disabled' );
eZINI::instance()->setVariable( 'SearchSettings', 'DelayedIndexing', 'enabled' );

// =================================================
$objectID = 27395; // Enter an ObjectID here
$nodeID = 53115; // Enter a NodeID here
//$contentObject = eZContentObject::fetch( $objectID );
//$content = SQLIContent::fromContentObject( $contentObject );
$content = SQLIContent::fromNodeID( $nodeID );
$content->setActiveLanguage( 'fre-FR' );
$cli->notice( "Object name : $content" );

$cli->notice();
$cli->notice( 'Available locales for this object : ' );
foreach( $content->fields as $locale => $fieldset )
{
    $cli->notice( $locale.' - '.$fieldset->getLanguage() );
}

$cli->notice();

// =================================================
// ===== CONTENT EDITING
// =================================================

$cli->notice( 'Now publishing new version of object #'.$objectID.' (NodeID #'.$nodeID.')' );
$content->fields['eng-US']->author = 'Oscar Wilde';
$content->fields['fre-FR']->author = 'Victor Hugo';
$content->fields['fre-FR']->name = 'Test FR5';
$content->fields['eng-US']->name = 'Test US5';
$content->fields['eng-GB']->name = 'Test GB5';
$content->fields['ita-IT']->name = 'Test IT5';
$content->fields['esl-ES']->name = 'Test ES5';
$content->fields['ger-DE']->name = 'Test DE5';

$options = new SQLIContentOptions( array( 'remote_id' => 'myremoteid' ) );
$content->setOptions( $options );

$publisher = SQLIContentPublisher::getInstance();
$publisher->publish( $content );
$script->shutdown();