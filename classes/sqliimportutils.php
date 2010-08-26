<?php
/**
 * SQLIImportUtils
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 */

class SQLIImportUtils
{
    /**
     * Abstract method to translate labels and eventually takes advantage of new 4.3 i18n API
     * @param $context
     * @param $message
     * @param $comment
     * @param $argument
     * @return string
     */
    public static function translate( $context, $message, $comment = null, $argument = null )
    {
        $translated = '';
        
        // eZ Publish < 4.3 => use old i18n system
        if( eZPublishSDK::majorVersion() >= 4 && eZPublishSDK::minorVersion() < 3 )
        {
            if( !function_exists( 'ezi18n' ) )
                include_once( 'kernel/common/i18n.php' );
            
            $translated = ezi18n( $context, $message, $comment, $argument );
        }
        else
        {
            $translated = ezpI18n::tr( $context, $message, $comment, $argument );
        }
        
        return $translated;
    }
    
    /**
     * Abstract method to initialize a template and eventually takes advantage of new 4.3 TPL API
     * @return eZTemplate
     */
    public static function templateInit()
    {
        $tpl = null;
        if(eZPublishSDK::majorVersion() >= 4 && eZPublishSDK::minorVersion() < 3)
        {
            include_once( 'kernel/common/template.php' );
            $tpl = templateInit();
        }
        else
        {
            $tpl = eZTemplate::factory();
        }
        
        return $tpl;
    }
    
    /**
     * Fetches handler limitation list for policies
     * @return array
     */
    public static function fetchHandlerLimitationList()
    {
        $importINI = eZINI::instance( 'sqliimport.ini' );
        $aHandlers = $importINI->variable( 'ImportSettings', 'AvailableSourceHandlers' );
        $aFinal = array();
        foreach( $aHandlers as $handler )
        {
            $aFinal[] = array(
                'id'    => $handler,
                'name'  => $handler
            );
        }
        return $aFinal;
    }
    
    /**
     * Shorthand method to check user access policy limitations for a given module/policy function.
     * Returns the same array as eZUser::hasAccessTo(), with "simplifiedLimitations".
     * 'simplifiedLimitations' array holds all the limitations names as defined in module.php.
     * If your limitation name is not defined as a key, then your user has full access to this limitation
     * @param string $module Name of the module
     * @param string $function Name of the policy function ($FunctionList element in module.php)
     * @return array
     */
    public static function getSimplifiedUserAccess( $module, $function )
    {
        $user = eZUser::currentUser();
        $userAccess = $user->hasAccessTo( $module, $function );

        $userAccess['simplifiedLimitations'] = array();
        if( $userAccess['accessWord'] == 'limited' )
        {
            foreach( $userAccess['policies'] as $policy )
            {
                foreach( $policy as $limitationName => $limitationList )
                {
                    foreach( $limitationList as $limitationValue )
                    {
                        $userAccess['simplifiedLimitations'][$limitationName][] = $limitationValue;
                    }

                    $userAccess['simplifiedLimitations'][$limitationName] = array_unique($userAccess['simplifiedLimitations'][$limitationName]);
                }
            }
        }
        return $userAccess;
    }
    
    /**
     * Check access to a specific module/function with limitation values.
     * See eZ Publish documentation on more info on module, function and
     * limitation values. Example: a user can have content/read permissions
     * but it can be limited to a specific limitation like a section, a node
     * or node tree. 1.0 limitation: returns false if one of provided values
     * don't match but ignores limitations not specified in $limitations.
     *
     * Taken from eZJSCore extension.
     * @see eZJSCore
     * @param string $module
     * @param string $function
     * @param array|null $limitations A hash of limitation keys and values
     * @return bool
     */
    public static function hasAccessToLimitation( $module, $function, $limitations = null, $debug = false )
    {
        // Like fetch(user,has_access_to), but with support for limitations
        $user = eZUser::currentUser();

        if ( $user instanceof eZUser )
        {
            $result = $user->hasAccessTo( $module, $function );
            
            if ( $result['accessWord'] !== 'limited')
            {
                return $result['accessWord'] === 'yes';
            }
            else
            {
                // Merge limitations before we check access
                $mergedLimitations = array();
                $missingLimitations = array();
                foreach ( $result['policies'] as $userLimitationArray  )
                {
                    foreach ( $userLimitationArray as $userLimitationKey => $userLimitationValues  )
                    {
                        if ( isset( $limitations[$userLimitationKey] ) )
                        {
                            if ( isset( $mergedLimitations[$userLimitationKey] ) )
                                $mergedLimitations[$userLimitationKey] = array_merge( $mergedLimitations[$userLimitationKey], $userLimitationValues );
                            else
                                $mergedLimitations[$userLimitationKey] = $userLimitationValues;
                        }
                        else
                        {
                            $missingLimitations[] = $userLimitationKey;
                        }
                    }
                }

                // User has access unless provided limitations don't match
                foreach ( $mergedLimitations as $userLimitationKey => $userLimitationValues  )
                {
                    // Handle subtree matching specifically as we need to match path string
                    if ( $userLimitationKey === 'User_Subtree' || $userLimitationKey === 'Subtree' )
                    {
                        $pathMatch = false;
                        foreach ( $userLimitationValues as $subtreeString )
                        {
                            if ( strstr( $limitations[$userLimitationKey], $subtreeString ) )
                            {
                                $pathMatch = true;
                                break;
                            }
                        }
                        if ( !$pathMatch )
                        {
                            if ( $debug ) eZDebug::writeDebug( "Unmatched[$module/$function]: " . $userLimitationKey . ' '. $limitations[$userLimitationKey] . ' != ' . $subtreeString, __METHOD__ );
                            return false;
                        }
                    }
                    else
                    {
                        if ( is_array( $limitations[$userLimitationKey] ) )
                        {
                            // All provided limitations must exist in $userLimitationValues
                            foreach( $limitations[$userLimitationKey] as $limitationValue )
                            {
                                if ( !in_array( $limitationValue, $userLimitationValues ) )
                                {
                                    if ( $debug ) eZDebug::writeDebug( "Unmatched[$module/$function]: " . $userLimitationKey . ' ' . $limitationValue . ' != [' . implode( ', ', $userLimitationValues ) . ']', __METHOD__ );
                                    return false;
                                }
                            }
                        }
                        else if ( !in_array( $limitations[$userLimitationKey], $userLimitationValues ) )
                        {
                            if ( $debug ) eZDebug::writeDebug( "Unmatched[$module/$function]: " . $userLimitationKey . ' ' . $limitations[$userLimitationKey] . ' != [' . implode( ', ', $userLimitationValues ) . ']', __METHOD__ );
                            return false;
                        }
                    }
                }
                if ( isset( $missingLimitations[0] ) && $debug )
                {
                    eZDebug::writeNotice( "Matched, but missing limitations[$module/$function]: " . implode( ', ', $missingLimitations ), __METHOD__ );
                }
                return true;
            }
        }
        eZDebug::writeDebug( 'No user instance', __METHOD__ );
        return false;
    }
    
    /**
     * Clears view cache for imported content objects.
     * ObjectIDs are stored in 'ezpending_actions' table, with {@link SQLIContent::ACTION_CLEAR_CACHE} action
     */
    public static function viewCacheClear()
    {
        $db = eZDB::instance();
        $isCli = isset( $_SERVER['argv'] );
        $output = null;
        $progressBar = null;
        $i = 0;
        
        $conds = array( 'action' => SQLIContent::ACTION_CLEAR_CACHE );
        $limit = array(
            'offset'    => 0,
            'length'    => 50
        );
        $count = (int)eZPersistentObject::count( eZPendingActions::definition(), $conds );
        
        if( $isCli && $count > 0 )
        {
            // Progress bar implementation
            $output = new ezcConsoleOutput();
            $output->outputLine( 'Starting to clear view cache for imported objects...' );
            $progressBarOptions = array(
                'emptyChar'         => ' ',
                'barChar'           => '='
            );
            $progressBar = new ezcConsoleProgressbar( $output, $count, $progressBarOptions );
            $progressBar->start();
        }
        
        /*
         * To avoid fatal errors due to memory exhaustion, pending actions are fetched by packets
         */
        do
        {
            $aObjectsToClear = eZPendingActions::fetchObjectList( eZPendingActions::definition(), null, $conds, null, $limit );
            $jMax = count( $aObjectsToClear );
            if( $jMax > 0 )
            {
                for( $j=0; $j<$jMax; ++$j )
                {
                    if( $isCli )
                        $progressBar->advance();
                    
                    $db->begin();
                    eZContentCacheManager::clearContentCacheIfNeeded( (int)$aObjectsToClear[$j]->attribute( 'param' ) );
                    $aObjectsToClear[$j]->remove();
                    $db->commit();
                    $i++;
                }
            }
            unset( $aObjectsToClear );
            eZContentObject::clearCache();
        }
        while( $i < $count );
        
        if( $isCli && $count > 0 )
        {
            $progressBar->finish();
            $output->outputLine();
        }
    }
}