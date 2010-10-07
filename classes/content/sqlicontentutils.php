<?php
/**
 * File containing SQLIContentUtils class
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage content
 */

/**
 * Utility class aggregating several helper methods related to content
 */
class SQLIContentUtils
{
    /**
     * Downloads a remote file in the temp folder defined in site.ini.
     * Returns the local path of the downloaded file.
     * Will throw an exception if download fails.
     * Proxy settings defined in site.ini will be used if they are provided
     * @param string $url File URL
     * @param array $httpAuth Array (numerical indexed) containing HTTP authentication infos. Provide it only if needed (default is null)
     *                          - First element is username.
     *                          - Second element is password.
     * @param bool $debug Flag indicating whether to activate cURL debug or not
     * @param bool $allowProxyUse Flag indicating whether to allow proxy use or not, allowing to override proxy settings from site.ini
     * @return string
     * @throws SQLIContentException
     */
    public static function getRemoteFile( $url, array $httpAuth = null, $debug = false, $allowProxyUse = true )
    {
        $url = trim( $url );
        $ini = eZINI::instance();
        $importINI = eZINI::instance( 'sqliimport.ini' );
        
        $localPath = $ini->variable( 'FileSettings', 'TemporaryDir' ).'/'.basename( $url );
        $timeout = $importINI->variable( 'ImportSettings', 'StreamTimeout' );

        $ch = curl_init( $url );
        $fp = fopen( $localPath, 'w+' );
        curl_setopt( $ch, CURLOPT_HEADER, false );
        curl_setopt( $ch, CURLOPT_FILE, $fp );
        curl_setopt( $ch, CURLOPT_TIMEOUT, (int)$timeout );
        curl_setopt( $ch, CURLOPT_FAILONERROR, true );
        if ( $debug )
        {
            curl_setopt( $ch, CURLOPT_VERBOSE, true );
            curl_setopt( $ch, CURLOPT_NOPROGRESS, false );
        }

        // Should we use proxy ?
        $proxy = $ini->variable( 'ProxySettings', 'ProxyServer' );
        if ( $proxy && $allowProxyUse )
        {
            curl_setopt( $ch, CURLOPT_PROXY, $proxy );
            $userName = $ini->variable( 'ProxySettings', 'User' );
            $password = $ini->variable( 'ProxySettings', 'Password' );
            if ( $userName )
            {
                curl_setopt( $ch, CURLOPT_PROXYUSERPWD, "$username:$password" );
            }
        }
        
        // Should we use HTTP Authentication ?
        if( is_array( $httpAuth ) )
        {
            if( count( $httpAuth ) != 2 )
                throw new SQLIContentException( __METHOD__.' => HTTP Auth : Wrong parameter count in $httpAuth array' );
            
            list( $httpUser, $httpPassword ) = $httpAuth;
            curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY );
            curl_setopt( $ch, CURLOPT_USERPWD, $httpUser.':'.$httpPassword );
        }
        
        $result = curl_exec( $ch );
        if ( $result === false )
        {
            $error = curl_error( $ch );
            $errorNum = curl_errno( $ch );
            curl_close( $ch );
            throw new SQLIContentException( "Failed downloading remote file '$url'. $error", $errorNum);
        }
        
        curl_close( $ch );
        fclose( $fp );

            
        return trim($localPath);
    }
    
    /**
     * Returns eZXML content to insert into XML blocks (ezxmltext datatype)
     * eZXML is generated from HTML content provided as argument
     * @param string $htmlContent Input HTML string
     * @return string Generated eZXML string
     */
    public static function getRichContent( $htmlContent )
    {
        $htmlParser = new SQLIXMLInputParser();
        $htmlParser->setParseLineBreaks( true );
        $document = $htmlParser->process( $htmlContent );
        $richContent = eZXMLTextType::domString( $document );
        
        return $richContent;
    }
}
