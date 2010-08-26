<?php
include( 'extension/sqliimport/stubs/scriptinit.php' );

$xmlFile = 'http://www.lolart.net/rss/feed/blog'; // RSS feed

$cli->notice( '#########################' );
$cli->notice( "### Processing XML test file $xmlFile with DOM" );
$cli->notice( '#########################' );
$cli->notice();

try
{
    // Default XML parser is DOM (SQLIXMLOptions::XML_PARSER_DOM)
    $options = new SQLIXMLOptions( array(
        'xml_path'      => $xmlFile
    ) );
    $parser = new SQLIXMLParser( $options );
    $xmlDoc = $parser->parse(); // $xmlDoc is DomDocument
    
    $items = $xmlDoc->getElementsByTagName( 'item' );
    foreach( $items as $item )
    {
        $title = $item->getElementsByTagName( 'title' )->item( 0 )->nodeValue;
        $cli->notice( "RSS Item title : '$title'" );
    }
}
catch( SQLIXMLException $e ) // XML parser may throw a SQLIXMLException on error
{
    $cli->error( $e->getMessage() );
}

$cli->notice();
$cli->notice( '#########################' );
$cli->notice( "### Processing XML test file $xmlFile with SimpleXML" );
$cli->notice( '#########################' );
$cli->notice();

try {
    $options2 = new SQLIXMLOptions( array(
        'xml_path'      => $xmlFile,
        'xml_parser'    => SQLIXMLOptions::XML_PARSER_SIMPLEXML
    ) );
    $parser2 = new SQLIXMLParser( $options2 );
    $simpleXMLDoc = $parser2->parse(); // $simpleXMLDoc is SimpleXMLElement
    
    foreach( $simpleXMLDoc->channel->item as $item )
    {
        $cli->notice( "RSS Item title : '$item->title'" );
    }
}
catch( SQLIXMLException $e )
{
    $cli->error( $e->getMessage() );
}

$script->shutdown();