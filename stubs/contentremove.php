<?php
include( 'extension/sqliimport/stubs/scriptinit.php' );

$cli->notice( 'Creation of a new "comment" object' );

$options = new SQLIContentOptions( array(
    'class_identifier'      => 'comment',
    'language'              => 'fre-FR'
) );
$comment = SQLIContent::create( $options );
$comment->fields->subject = 'Test subject';
$comment->fields->author = 'Test Author';
$comment->fields->message = 'Test comment';

$publishOptions = new SQLIContentPublishOptions( array( 'parent_node_id' => 2 ) );
$publisher = SQLIContentPublisher::getInstance();
$publisher->publish( $comment );

$comment->remove( false );

$script->shutdown();
