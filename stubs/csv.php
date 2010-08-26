<?php
include( 'extension/sqliimport/stubs/scriptinit.php' );

$csvFile = 'extension/sqliimport/stubs/example.csv';
$cli->notice( 'Processing CSV test file '.$csvFile );
 
$options = new SQLICSVOptions( array(
    'csv_path'    => $csvFile,
    'enclosure'   => '~' // Default is ". Mandatory to change if CSV file contains unescaped " chars
) );
$csvDoc = new SQLICSVDoc( $options );
$csvDoc->parse();
 
$rowCount = count( $csvDoc->rows );
$cli->notice( "There are $rowCount rows in CSV file" );
 
// Cleant headers, camel case
$headers = $csvDoc->rows->getHeaders();
$cli->warning( 'Cleant headers : ', false );
$cli->notice( implode( ', ', $headers ) );
 
// Raw headers, as provided in CSV file
$rawHeaders = $csvDoc->rows->getRawHeaders();
$cli->warning( 'Raw headers : ', false );
$cli->notice( implode( ', ', $rawHeaders ) );
 
$cli->notice( $csvDoc->rows[0]->myFirstField ); // array access
//var_dump($csvDoc->rows);
 
foreach( $csvDoc->rows as $row ) // Iteration
{
    $cli->notice( $row->myFirstField );
    $cli->notice( $row->otherField );
    $cli->notice( $row->andAnotherOne );
    $cli->notice();
}

$peakMemory = number_format( memory_get_peak_usage(), 0, '.', ' ');
$cli->notice( 'Peak memory usage : '.$peakMemory.' octets' );

$script->shutdown();
