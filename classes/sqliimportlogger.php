<?php
/**
 * SQLIImportLogger
 * Class for logging and/or display messages during import process
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 */

class SQLIImportLogger
{
    const NOTICELOG = 'notice',
          ERRORLOG = 'error',
          WARNINGLOG = 'warning';
          
    const ERRORLOG_FILE = 'sqliimport-error.log',
          WARNINGLOG_FILE = 'sqliimport-warning.log',
          NOTICELOG_FILE = 'sqliimport-notice.log';
          
    /**
     * Instance of eZCLI
     *
     * @var eZCLI
     */
    protected static $cli;
    
    /**
     * Generic method for logging a message
     *
     * @param string $msg
     * @param bool $bPrintMsg
     * @param string $logType
     */
    public static function logMessage( $msg, $bPrintMsg = true, $logType = self::NOTICELOG )
    {
        switch( $logType )
        {
            case self::ERRORLOG: 
                $logFile = self::ERRORLOG_FILE;
                if( $bPrintMsg )
                    self::writeError( $msg );
            break;

            case self::WARNINGLOG:
                $logFile = self::WARNINGLOG_FILE;
                if( $bPrintMsg )
                    self::writeWarning( $msg );
            break;
            
            case self::NOTICELOG:
            default:
                $logFile = self::NOTICELOG_FILE;
                if( $bPrintMsg )
                    self::writeNotice( $msg );
            break;
        }
        
        eZLog::write( $msg, $logFile );
    }
    
    /**
     * Logs a notice message
     *
     * @param string $msg
     * @param bool $bPrintMsg Display the message on the current ouput (cli or web) ?
     */
    public static function logNotice( $msg, $bPrintMsg = true )
    {
        self::logMessage( $msg, $bPrintMsg, self::NOTICELOG );
    }
    
    /**
     * Logs a warning message
     *
     * @param string $msg
     * @param bool $bPrintMsg Display the message on the current ouput (cli or web) ?
     */
    public static function logWarning( $msg, $bPrintMsg = true )
    {
        self::logMessage ($msg, $bPrintMsg, self::WARNINGLOG );
    }
    
    /**
     * Logs an error message
     *
     * @param string $msg
     * @param bool $bPrintMsg Display the message on the current ouput (cli or web) ?
     */
    public static function logError( $msg, $bPrintMsg = true )
    {
        self::logMessage( $msg, $bPrintMsg, self::ERRORLOG );
    }
    
    /**
     * Displays a message on the appropriate output (cli or eZDebug)
     *
     * @param string $msg
     * @param string $logType
     */
    public static function writeMessage( $msg, $logType = self::NOTICELOG )
    {
        self::$cli = eZCLI::instance();
        $isWebOutput = self::$cli->isWebOutput(); 
        switch( $logType )
        {
            case self::ERRORLOG:
                if( !$isWebOutput )
                    self::$cli->error( $msg );
                else
                    eZDebug::writeError( $msg, 'SQLIImport' );
            break;
            
            case self::WARNINGLOG:
                if( !$isWebOutput )
                    self::$cli->warning( $msg );
                else
                    eZDebug::writeWarning( $msg, 'SQLIImport' );
            break;
            
            case self::NOTICELOG:
            default:
                if( !$isWebOutput )
                    self::$cli->notice( $msg );
                else
                    eZDebug::writeNotice( $msg, 'SQLIImport' );
            break;
        }
    }
    
    /**
     * Displays an error message on the appropriate output (cli or eZDebug)
     * @param string $msg
     */
    public static function writeError( $msg )
    {
        self::writeMessage( $msg, self::ERRORLOG );
    }
    
    /**
     * Displays a warning message on the appropriate output (cli or eZDebug)
     * @param string $msg
     */
    public static function writeWarning( $msg )
    {
        self::writeMessage( $msg, self::WARNINGLOG );
    }
    
    /**
     * Displays a notice message on the appropriate output (cli or eZDebug)
     * @param string $msg
     */
    public static function writeNotice( $msg )
    {
        self::writeMessage( $msg, self::NOTICELOG );
    }
}