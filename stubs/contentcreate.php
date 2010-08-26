<?php
include( 'extension/sqliimport/stubs/scriptinit.php' );
eZINI::instance()->setVariable( 'ContentSettings', 'ViewCaching', 'disabled' );

$cli->notice( 'Creation of a new "comment" object' );
$options = new SQLIContentOptions( array(
    'class_identifier'      => 'comment',
    'remote_id'             => 'my_ubber_cool_remote_id',
    'language'              => 'fre-FR'
) );
$comment = SQLIContent::create( $options );
$cli->notice( 'Current version : '.$comment->current_version );
$comment->fields->subject = 'Mon super sujet';
$comment->fields->author = 'Moi !';
$comment->fields->message = 'Le commentaire de la mort';

$comment->addTranslation( 'eng-US' );
$comment->fields['eng-US']->subject = 'My great subject';
$comment->fields['eng-US']->author = 'Batman';
$comment->fields['eng-US']->message = 'Death comment';

$comment->addLocation( SQLILocation::fromNodeID( 2 ) );
$comment->addLocation( SQLILocation::fromNodeID( 43 ) );

$publisher = SQLIContentPublisher::getInstance();
$publisher->publish( $comment );

$cli->notice( 'Current version : '.$comment->current_version );

// Loop against locations
foreach( $comment->locations as $nodeID => $location )
{
    $cli->notice( $nodeID.' => '.$location->path_string.' ('.$comment->locations[$nodeID]->path_identification_string.')' );
}

$script->shutdown();
