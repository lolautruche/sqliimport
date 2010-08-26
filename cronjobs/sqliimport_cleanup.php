<?php
/**
 * SQLi Import cleanup cronjob
 * Will clear imported content objects caches if needed
 * Will trigger imported content objects indexing if needed
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 */

// Clear view cache
SQLIImportUtils::viewCacheClear();

// Indexing will be trigerred by indexcontent cronjob

$cli->notice( 'Cleanup is over :)' );
    
$memoryMax = memory_get_peak_usage(); // Result is in bytes
$memoryMax = round( $memoryMax / 1024 / 1024, 2 ); // Convert in Megabytes
$cli->notice( 'Peak memory usage : '.$memoryMax.'M' );
