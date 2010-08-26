<?php
/**
 * File containing SQLIContentObject
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage content
 */

/**
 * Proxy class for eZContentObject.
 * Useful for easy manipulation on content objects (insertion, modification...)
 * <code>
 * // Create a new content in default language
 * $contentOptions = new SQLIContentOptions( array( 'class_identifier' => 'article', 'remote_id' => 'my_remote_id' ) );
 * $article = SQLIContent::create( $contentOptions );
 * $article->fields->title = 'My article has a title';
 * $article->fields->body = '<tag>myxmlcontent</tag>';
 *
 * // Create a new translation
 * $article->addTranslation( 'fre-FR' );
 * $article->fields['fre-FR']->title = 'Mon article a un titre';
 * $article->fields['fre-FR']->body = '<tag>moncontenuxml</tag>';
 *
 * // Now create locations for content
 * $article->addLocation( SQLILocation::fromNodeID( 2 ) ); // Will be main location
 * $article->addLocation( SQLILocation::fromNodeID( 43 ) ); // Additional location
 *
 * // Now publish content
 * $publisher = SQLIContentPublisher::getInstance();
 * $publisher->publish( $article ); // Creator of this content will be currently logged in user
 * </code>
 *
 * To create XML for rich text attributes (XML Blocks), use {@link SQLIXMLInputParser} :
 * <code>
 * // Let's assume that we have HTML content holded in $htmlContent variable
 * $parser = new SQLIXMLInputParser();
 * $parser->setParseLineBreaks( true );
 * $document = $parser->process( $htmlContent ); // Result is a DOM Document
 * $xmlContent = eZXMLTextType::domString( $document );
 *
 * // Now use it in your content
 * $contentOptions = new SQLIContentOptions( array( 'class_identifier' => 'article', 'remote_id' => 'my_remote_id' ) );
 * $article = SQLIContent::create( $contentOptions );
 * $article->fields->title = 'My article has a title';
 * $article->fields->body = $xmlContent;
 * </code>
 *
 * @property-read SQLILocationFieldset $locations Available locations for this content
 * @property mixed All "attributes" available from eZContentObject (See {@link eZContentObject::definition()}).
 */
class SQLIContent
{
    const ACTION_CLEAR_CACHE = 'sqlicontent_clearcache';
    
    /**
     * @var eZContentObject
     */
    protected $contentObject;

    /**
     * Fieldset Holder
     * @var SQLIFieldsetHolder
     */
    public $fields;
    
    /**
     * Initial locations for content in content tree
     * These locations will be considered as "parent" locations
     * @var array( SQLILocation )
     */
    protected $initialLocations = array();
    
    /**
     * Locations for content.
     * @var SQLILocationSet
     */
    protected $currentLocations;

    /**
     * Currently active language, as a locale xxx-XX
     * @var string
     */
    protected $activeLanguage;
    
    /**
     * Content options.
     * See {@link SQLIContentOptions::__set()} to see available options
     * @var SQLIContentOptions
     */
    protected $options;

    /**
     * Private constructor
     * To create a content, use SQLIContent::create()
     * @see SQLIContent::create()
     */
    protected function __construct()
    {
        
    }
    
    /**
     * Destructor.
     */
    public function __destruct()
    {
        $this->contentObject->cleanupInternalDrafts();
        
        // Free some memory
        $this->flush();
    }

    /**
     * Returns raw content object
     * @return eZContentObject
     */
    public function getRawContentObject()
    {
        return $this->contentObject;
    }

    /**
     * Content creation
     * <code>
     * $options = new SQLIContentOptions( array( 'class_identifier'     => 'article',
     *                                           'remote_id'            => 'myremoteid',
     *                                           'language'             => 'fre-FR' ) );
     * $article = SQLIContent::create( $options );
     * $article->fields->title = 'My Title';
     * $article->fields->body = '<tag>myxmlcontent</tag>';
     * </code>
     * @param SQLIContentOptions $options See {@link SQLIContentOptions::__set()} to see available options.
     *                                    Mandatory parameters are :
     *                                      - class_identifier
     */
    public static function create( SQLIContentOptions $options )
    {
        if ( !isset( $options['class_identifier'] ) )
            throw new SQLIContentException( 'Cannot create content without "class_identifier" option' );
            
        $creatorID = $options['creator_id'];
        $sectionID = $options['section_id'];
        $lang = $options['language'];
        $contentObject = null;
        
        // If remote ID is set, first try to fetch content object from it
        if( isset( $options['remote_id'] ) )
        {
            $contentObject = eZContentObject::fetchByRemoteID( $options['remote_id'] );
        }
        
        if( !$contentObject instanceof eZContentObject )
        {
            $db = eZDB::instance();
            $db->begin();
                $contentClass = eZContentClass::fetchByIdentifier( $options['class_identifier'] );
                $contentObject = $contentClass->instantiate( $creatorID, $sectionID, false, $lang );
                if( isset( $options['remote_id'] ) )
                {
                    $contentObject->setAttribute( 'remote_id', $options['remote_id'] );
                    $contentObject->store();
                }
            $db->commit();
        }
        
        $content = self::fromContentObject( $contentObject );
        if( $lang )
            $content->setActiveLanguage( $lang );
        
        return $content;
    }

    /**
     * Sets the currently active language when reading attribute(/object/node) properties
     * @param string $language Language locale (xxx-XX)
     * @return void
     * @todo Check if $language is a valid locale
     * @todo Also change language for content object name
     */
    public function setActiveLanguage( $language )
    {
        $this->activeLanguage = $language;
        $this->fields->setActiveLanguage( $language );
    }
    
    /**
     * Initializes an object from eZContentObject
     * @param eZContentObject $object
     * @return SQLIContent
     */
    public static function fromContentObject( eZContentObject $object )
    {
        $content = new self;
        $content->fields = SQLIContentFieldsetHolder::fromContentObject( $object );
        $content->contentObject = $object;
        $content->setActiveLanguage( $content->fields->getActiveLanguage() );
        
        return $content;
    }
    
    /**
     * Initializes an object from ContentObjectID
     * @param int $objectID
     * @return SQLIContent
     */
    public static function fromContentObjectID( $objectID )
    {
        $contentObject = eZContentObject::fetch( $objectID );
        if ( !$contentObject instanceof eZContentObject )
            throw new SQLIContentException( "Unable to find an eZContentObject with ID $objectID" );
        
        $content = self::fromContentObject( $contentObject );
        
        return $content;
    }
    
    /**
     * Initializes an object from a node
     * @param eZContentObjectTreeNode $node
     *
     * @return SQLIContent
     */
    public static function fromNode( eZContentObjectTreeNode $node )
    {
        $object = $node->object();
        $content = self::fromContentObject( $object );
        $content->locations->addLocation( SQLILocation::fromNode( $node ) );
        
        return $content;
    }
    
    /**
     * Initializes an object from a nodeID
     * @param int $nodeID
     * @throws SQLIContentException
     */
    public static function fromNodeID( $nodeID )
    {
        $node = eZContentObjectTreeNode::fetch( $nodeID );
        if ( !$node instanceof eZContentObjectTreeNode )
            throw new SQLIContentException("Unable to find node with ID $nodeID");
        
        return self::fromNode( $node );
    }
    
    /**
     * Initializes an object from RemoteID.
     * If no content object can be found, returns null
     * @param int $remoteID
     * @return SQLIContent|null
     */
    public static function fromRemoteID( $remoteID )
    {
        $contentObject = eZContentObject::fetchByRemoteID( $remoteID );
        $content = null;
        if ( $contentObject instanceof eZContentObject )
            $content = self::fromContentObject( $contentObject );
        else
            SQLIImportLogger::logWarning( "Unable to find an eZContentObject with RemoteID $remoteID", false );
        
        return $content;
    }
    
    /**
     * Generic string version of object
     * Returns bulk "name" of content object
     */
    public function __toString()
    {
        return $this->contentObject->attribute( 'name' );
    }
    
    /**
     * Allows to set options to content
     * Enter description here ...
     * @param $options
     */
    public function setOptions( SQLIContentOptions $options )
    {
        $this->options = $options;
        
        foreach( $options as $optionName => $option )
        {
            switch( $optionName )
            {
                case 'language':
                    $this->setActiveLanguage( $option );
                    break;
                default:
                    $this->contentObject->setAttribute( $optionName, $option );
            }
        }
        
        $this->contentObject->store();
    }
    
    /**
     * Adds a new translation to content if not already set
     * @param string $lang Translation code to add, as a locale (xxx-XX)
     */
    public function addTranslation( $lang )
    {
        if( !isset( $this->fields[$lang] ) )
        {
            $this->fields->addTranslation( $lang );
        }
        else
        {
            eZDebug::writeWarning(__CLASS__ . ' : Adding already existent language '.$lang);
        }
    }
    
    /**
     * Checks if current content is a newly created one
     * @return bool
     */
    public function isNew()
    {
        $isNew = false;
        
        // We can reasonably consider that first version is "1"
        // However, kernel lets instantiate an object with a version number other than "1" :-|
        // @see eZContentClass::instantiate()
        if( $this->contentObject->attribute( 'current_version' ) == 1 )
        {
            // Not very clean since we check content object status, not version status
            // But more efficient as we avoid some DB calls (this method can be called several times in one script)
            $aStatusNew = array( eZContentObject::STATUS_DRAFT );
            if( in_array( $this->contentObject->attribute( 'status' ), $aStatusNew ) )
                $isNew = true;
        }
        
        return $isNew;
    }
    
    /**
     * Will clear current content object cache and reset dataMap.
     * Avoids useless memory consumption and allows to "refresh" content object.
     * Warning ! Further call to object attributes will do new DB queries.
     * @see eZContentObject::clearCache()
     * @see eZContentObject::resetDataMap()
     */
    public function flush()
    {
        if ( $this->contentObject instanceof eZContentObject )
        {
            $objectID = $this->contentObject->attribute( 'id' );
            $this->contentObject->resetDataMap();
            eZContentObject::clearCache( array( $objectID ) );
        }
    }
    
    /**
     * Refreshes internal {@link eZContentObject content object}
     * @see eZContentObject
     */
    public function refresh()
    {
        $contentObjectID = $this->contentObject->attribute( 'id' );
        
        global $eZContentObjectContentObjectCache;
        unset( $eZContentObjectContentObjectCache[$contentObjectID] );
        
        $this->contentObject = eZContentObject::fetch( $contentObjectID );
        
        // Reset input data
        foreach( $this->fields as $fieldset )
        {
            $fieldset->resetInputData();
        }
    }
    
    /**
     * Returns current draft for content.
     * If no draft has been found, a new one will be created
     * @see SQLIContentFieldsetHolder::getCurrentDraft()
     * @return eZContentObjectVersion
     */
    public function getDraft()
    {
        return $this->fields->getCurrentDraft( $this->activeLanguage );
    }
    
    /**
     * Generic method for calling current content object methods.
     * If method isn't implemented, will throw an exception
     * @param $method Method name
     * @param $arguments
     * @throws ezcBasePropertyNotFoundException
     */
    public function __call( $method, $arguments )
    {
        if ( method_exists( $this->contentObject, $method ) )
            return call_user_func_array( array( $this->contentObject, $method ), $arguments );
        else
            throw new ezcBasePropertyNotFoundException( $method );
    }
    
    /**
     * Getter
     * Returns given attribute for current content object if it exists (ie. main_node_id).
     * Will throw an exception otherwise.
     * All "classic" attributes can be used (See {@link eZContentObject::definition()}).
     * @param $name
     * @throws ezcBasePropertyNotFoundException
     */
    public function __get( $name )
    {
        $ret = null;
        
        switch( $name )
        {
            case 'locations':
                if ( !$this->currentLocations instanceof SQLILocationSet )
                {
                    $this->refreshLocations();
                }
                
                $ret = $this->currentLocations;
                break;
            default:
                if ( $this->contentObject->hasAttribute( $name ) )
                    $ret = $this->contentObject->attribute( $name );
                else
                    throw new ezcBasePropertyNotFoundException( $name );
        }
        
        return $ret;
    }

    /**
     * Setter
     * Sets value to an attribute for the content object.
     * All "classic" attributes can be used (See {@link eZContentObject::definition()}).
     * If attribute doesn't exist, will throw an exception
     * @param $name Attribute name
     * @param $value Attribute value
     * @throws ezcBasePropertyNotFoundException
     * @return void
     */
    public function __set( $name, $value )
    {
        if( !$this->contentObject->hasAttribute( $name ) )
        {
            throw new ezcBasePropertyNotFoundException( $name );
        }

        $this->contentObject->setAttribute( $name, $value );
    }
    
    /**
     * Check if given attribute exists.
     * All "classic" attributes can be used (See {@link eZContentObject::definition()}).
     * @param $name
     * @return bool
     */
    public function __isset( $name )
    {
        return $this->contentObject->hasAttribute( $name );
    }
    
    /**
     * Will remove content and all its locations/translations...
     * See {@link self::removeLocation()} for removing only one location.
     * Will move to trash by default
     * @param bool $moveToTrash Indicates if we move content to trash or not. True by default
     */
    public function remove( $moveToTrash = true )
    {
        $aAssignedNodes = $this->contentObject->assignedNodes( false );
        $aNodesID = array();
        foreach( $aAssignedNodes as $node )
        {
            $aNodesID[] = $node['node_id'];
        }
        
        $this->doRemove( $aNodesID, $moveToTrash );
        $this->flush();
    }
    
    /**
     * Removes only one location for content object.
     * Location is identified by $nodeID
     * @param int $nodeID A valid node ID
     * @param bool $moveToTrash Indicates if we move content to trash or not. True by default
     */
    public function removeLocation( $nodeID, $moveToTrash = true )
    {
        $node = eZContentObjectTreeNode::fetch( $nodeID );
        if ( !$node instanceof eZContentObjectTreeNode )
            throw new SQLIContentException( __METHOD__." : Invalid node with nodeID #$nodeID. Cannot remove it from tree" );
        
        $this->doRemove( array( $nodeID ), $moveToTrash );
    }
    
    /**
     * Does the remove job.
     * Will use content_delete operation if available (workflow support)
     * @param array $aNodeID
     * @param bool $moveToTrash Indicates if we move content to trash or not. True by default
     * @internal
     */
    private function doRemove( array $aNodeID, $moveToTrash = true )
    {
        if ( eZOperationHandler::operationIsAvailable( 'content_delete' ) )
        {
            $operationResult = eZOperationHandler::execute( 'content',
                                                            'delete',
                                                             array( 'node_id_list' => $aNodeID,
                                                                    'move_to_trash' => $moveToTrash ),
                                                              null, true );
        }
        else
        {
            eZContentOperationCollection::deleteObject( $aNodeID, $moveToTrash );
        }
    }
    
    /**
     * Adds a location for content in content tree.
     * Content must has been published at least once
     * Enter description here ...
     * @param $parentLocation
     */
    public function addLocation( SQLILocation $parentLocation )
    {
        // First check if content is new.
        // If so, location will be registered as "initial" and added at publish time
        if( $this->isNew() )
        {
            $this->initialLocations[] = $parentLocation;
        }
        else
        {
            $publisher = SQLIContentPublisher::getInstance();
            $publisher->addLocationToContent( $parentLocation, $this );
        }
    }
    
    /**
     * Returns "initial" locations.
     * It only has sense if content is new
     * @see self::initialLocations
     * @return array
     */
    public function getInitialLocations()
    {
        return $this->initialLocations;
    }
    
    /**
     * Checks if content has initial location(s)
     * @return bool
     */
    public function hasInitialLocations()
    {
        if( $this->isNew() ) // New content, check if initial locations has been registered
            return count( $this->initialLocations ) > 0;
        else // Existing content, so necessarily has an initial location
            return true;
    }
    
    /**
     * Refreshes locations for content
     * @internal
     */
    public function refreshLocations()
    {
        $this->currentLocations = null;
        $this->currentLocations = new SQLILocationSet();
        $assignedNodes = $this->contentObject->assignedNodes();
        foreach( $assignedNodes as $node )
        {
            $this->currentLocations->addLocation( SQLILocation::fromNode( $node ) );
        }
    }
    
    /**
     * Adds a "pending clear cache" action if ViewCaching is disabled.
     * This method should be called at publish time.
     * Cache should then be cleared by a cronjob
     * @return void
     */
    public function addPendingClearCacheIfNeeded()
    {
        if( eZINI::instance()->variable( 'ContentSettings', 'ViewCaching' ) === 'disabled' )
        {
            $rowPending = array(
                'action'        => self::ACTION_CLEAR_CACHE,
                'created'       => time(),
                'param'         => $this->contentObject->attribute( 'id' )
            );
            
            $pendingItem = new eZPendingActions( $rowPending );
            $pendingItem->store();
        }
    }
}
