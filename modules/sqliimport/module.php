<?php
/**
 * SQLi Import module descriptor
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 */

$Module = array( 'name' => 'sqliimport' );

$ViewList = array();

$ViewList['list'] = array(
	'script'					=>	'list.php',
	'params'					=> 	array(),
	'unordered_params'			=> 	array(),
	'single_post_actions'		=> 	array(),
	'post_action_parameters'	=> 	array(),
	'default_navigation_part'	=> 'sqliimportnavigationpart',
	'functions'					=> array( 'listimports' )
);

$ViewList['scheduledlist'] = array(
    'script'                    =>  'scheduledlist.php',
    'params'                    =>  array(),
    'unordered_params'          =>  array(),
    'single_post_actions'       =>  array(),
    'post_action_parameters'    =>  array(),
    'default_navigation_part'   => 'sqliimportnavigationpart',
    'functions'                 => array( 'listimports' )
);

$ViewList['addimport'] = array(
    'script'                    =>  'addimport.php',
    'params'                    =>  array(),
    'unordered_params'          =>  array(),
    'single_post_actions'       =>  array( 'RequestImportButton' => 'RequestImport' ),
    'post_action_parameters'    =>  array( 'RequestImport' => array(
                                                'ImportHandler'     => 'ImportHandler',
                                                'ImportOptions'     => 'ImportOptions'
                                            ) ),
    'default_navigation_part'   => 'sqliimportnavigationpart',
    'functions'                 => array( 'manageimports' )
);

$ViewList['addscheduled'] = array(
    'script'                    =>  'addscheduled.php',
    'params'                    =>  array( 'ScheduledImportID' ),
    'unordered_params'          =>  array(),
    'single_post_actions'       =>  array( 'RequestScheduledImportButton' => 'RequestScheduledImport' ),
    'post_action_parameters'    =>  array( 'RequestScheduledImport' => array(
                                                'ImportHandler'         => 'ImportHandler',
                                                'ImportOptions'         => 'ImportOptions',
                                                'ScheduledDate'         => 'ScheduledDate',
                                                'ScheduledHour'         => 'ScheduledHour',
                                                'ScheduledMinute'       => 'ScheduledMinute',
                                                'ScheduledFrequency'    => 'ScheduledFrequency',
                                                'ScheduledLabel'        => 'ScheduledLabel',
                                                'ScheduledActive'       => 'ScheduledActive'
                                            ) ),
    'default_navigation_part'   => 'sqliimportnavigationpart',
    'functions'                 => array( 'manageimports' )
);

$ViewList['removescheduled'] = array(
    'script'                    =>  'removescheduled.php',
    'params'                    =>  array( 'ImportID' ),
    'unordered_params'          =>  array(),
    'single_post_actions'       =>  array(),
    'post_action_parameters'    =>  array(),
    'default_navigation_part'   => 'sqliimportnavigationpart',
    'functions'                 => array( 'manageimports' )
);

$ViewList['alterimport'] = array(
    'script'                    =>  'alterimport.php',
    'params'                    =>  array( 'Action', 'ImportID' ),
    'unordered_params'          =>  array(),
    'single_post_actions'       =>  array(),
    'post_action_parameters'    =>  array(),
    'default_navigation_part'   => 'sqliimportnavigationpart',
    'functions'                 => array( 'manageimports' )
);

$ViewList['purgelist'] = array(
    'script'                    =>  'purgelist.php',
    'params'                    =>  array(),
    'unordered_params'          =>  array(),
    'single_post_actions'       =>  array(),
    'post_action_parameters'    =>  array(),
    'default_navigation_part'   => 'sqliimportnavigationpart',
    'functions'                 => array( 'manageimports' )
);

// Import handler limitation for policy
$Type = array(
    'name'			=> 'SQLIImport_Type',
    'values'		=> array(),
	'extension'		=> 'sqliimport',
    'path'			=> 'classes/',
    'file'			=> 'sqliimportutils.php',
    'class'			=> 'SQLIImportUtils',
    'function'		=> 'fetchHandlerLimitationList',
    'parameter'		=> array()
);

$FunctionList['listimports'] = array();
$FunctionList['manageimports'] = array( 'SQLIImport_Type' => $Type );
