<?php
require 'autoload.php';

$cli = eZCLI::instance();
$script = eZScript::instance( array( 'description' => ( "eZ Publish Script Executor\n\n" .
                                                        "Allows execution of simple PHP scripts which uses eZ Publish functionality,\n" .
                                                        "when the script is called all necessary initialization is done\n" .
                                                        "\n" .
                                                        "ezexec.php myscript.php" ),
                                     'use-session' => false,
                                     'use-modules' => true,
                                     'use-extensions' => true ) );

$script->startup();

$options = $script->getOptions();
$script->initialize();
$script->setUseDebugAccumulators( true );

$user = eZUser::fetchByName( 'admin' );
eZUser::setCurrentlyLoggedInUser( $user , $user->attribute( 'contentobject_id' ) );
