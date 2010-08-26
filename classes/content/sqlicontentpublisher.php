<?php
/**
 * SQLIContentPublisher
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage content
 */

/**
 * SQLIContentPublisher is a class allowing to publish a content
 * <code>
 * $options = new SQLIContentOptions( array( 'class_identifier'     => 'article',
 *                                           'remote_id'            => 'myremoteid',
 *                                           'language'             => 'fre-FR' ) );
 * $article = SQLIContent::create( $options );
 * $article->fields->title = 'My Title';
 * $article->fields->body = '<tag>myxmlcontent</tag>';
 *
 * $publisher = new SQLIContentPublisher();
 * $publisher->publish( $article );
 * </code>
 */
class SQLIContentPublisher
{
    /**
     * Publish options
     * @var SQLIContentPublishOptions
     */
    protected $options;
    
    /**
     * Publisher instance. Singleton pattern implementation
     * @var SQLIContentPublisher
     */
    protected static $instance;
    
    /**
     * Private constructor. Use singleton method
     * <code>
     * $publisher = SQLIContentPublisher::getInstance();
     * </code>
     */
    protected function __construct()
    {
        // Set default options
        $this->options = new SQLIContentPublishOptions();
    }
    
    /**
     * Singleton
     * @return SQLIContentPublisher
     */
    public function getInstance()
    {
        if( !self::$instance instanceof SQLIContentPublisher )
            self::$instance = new SQLIContentPublisher();
        
        return self::$instance;
    }
    
    /**
     * Enter description here ...
     * @param SQLIContentPublishOptions $options
     */
    public function setOptions( SQLIContentPublishOptions $options )
    {
        $this->options = $options;
    }
    
    /**
     * Publishes provided content
     * @param SQLIContent $content
     */
    public function publish( SQLIContent $content )
    {
        $initialLocations = array();
        
        if( $content->isNew() && !$this->options['parent_node_id'] ) // parent_node_id is mandatory for new content
        {
            // Check for initial locations that may have been registered in content
            if( $content->hasInitialLocations() )
            {
                $initialLocations = $content->getInitialLocations();
                $initialMainLocation = array_shift( $initialLocations );
                $this->options['parent_node_id'] = $initialMainLocation->getNodeID();
            }
            else
            {
                throw new SQLIContentException( __METHOD__.' : Initial location or "parent_node_id" option not defined for new content !' );
            }
        }
        
        $contentObject = $content->getRawContentObject();
        $db = eZDB::instance();
        $db->begin();
        
        $canPublish = false;
        $version = $this->createNewVersion( $content );
        
        // Loop against all fieldsets (by language) and edit attributes
        foreach( $content->fields as $lang => $fieldset )
        {
            if( !$this->publicationAllowed( $content, $lang ) ) // Publish only if necessary and/or allowed (will let $canPublish to false)
            {
                $msg = 'Content object #'.$contentObject->attribute( 'id' ).' in language '.$lang.' has no need to be modified';
                eZDebug::writeNotice( $msg, __METHOD__ );
                continue;
            }
            
            eZDebug::accumulatorStart( 'sqlicontentpublisher_'.$lang.'_attributes', 'sqlicontentpublisher', 'Attributes handling for '.$lang );
            $canPublish = true;
            $fieldset->refreshDataMap( $version );
            
            // Now store attributes one by one
            foreach( $fieldset as $fieldName => $field )
            {
                $data = $field->getData();
                
                // Don't update field if its data is null and "update_null_field" option is false
                if( $this->options['update_null_field'] === false && is_null( $data ) )
                    continue;
                
                // Use fromString() method if available from datatype, setContent() otherwise
                if( method_exists( $field->datatype(), 'fromString' ) )
                {
                    $field->fromString( $data );
                }
                else
                {
                    $field->setAttribute( 'data_text', $data );
                }
                
                $field->store();
            }
            eZDebug::accumulatorStop( 'sqlicontentpublisher_'.$lang.'_attributes' );
        }
        
        $db->commit();
        
        if( $canPublish ) // Publication is allowed (content modified)
        {
            // Now do publish for current language
            eZDebug::accumulatorStart( 'sqlicontentpublisher_publish', 'sqlicontentpublisher', 'Publishing object #'.$contentObject->attribute( 'id' ) );
            $operationResult = eZOperationHandler::execute( 'content', 'publish', array( 'object_id'    => $contentObject->attribute( 'id' ),
                                                                                         'version'      => $version->attribute( 'version' ) ) );
            eZDebug::accumulatorStop( 'sqlicontentpublisher_publish' );
            
            // Now cleaning
            $contentObject->cleanupInternalDrafts();
            $content->refresh();
            $content->addPendingClearCacheIfNeeded();
            
            // Handling additional "initial" locations. Only for new content
            if( count( $initialLocations ) > 0 )
            {
                foreach( $initialLocations as $initialAdditionalLocation )
                {
                    $this->addLocationToContent( $initialAdditionalLocation , $content);
                }
            }
        }
        else // Publication not allowed (content not modified for ex.). Remove draft
        {
            $version->removeThis();
        }
    }
    
    /**
     * Checks if content has been modified for given language.
     * Useful in order to avoid useless content publication, and so to get better performance.
     * If language is not provided, the function will take {@link SQLIContent::getActiveLanguage() content active language}
     * @param SQLIContent $content
     * @param string $language Language to check modification for. Must be a valid locale (xxx-XX)
     * @return bool
     */
    public function contentIsModified( SQLIContent $content, $language = null )
    {
        if( !$language )
            $language = $content->getActiveLanguage();
        
        eZDebug::accumulatorStart( 'sqlicontentpublisher_checkmodification', 'sqlicontentpublisher', 'Checking content real modification' );
        $contentIsModified = false;
        $updateNullField = $this->options['update_null_field'];
        
        foreach( $content->fields[$language] as $fieldName => $field )
        {
            $data = $field->getData();
            
            // Don't update field if its data is null and "update_null_field" option is false
            if( $updateNullField === false && is_null( $data ) )
                continue;
            
            $contentIsModified = $field->isModified();
            if( $contentIsModified === true )
                break;
        }
        
        eZDebug::accumulatorStop( 'sqlicontentpublisher_checkmodification' );
        return $contentIsModified;
    }
    
    /**
     * Checks if publication is "allowed" for provided content in provided language.
     * Publication will be allowed if content has been modified (a diff is operated) or if modification check has
     * been explicitly disabled with option "modification_check" to false.
     * If language is not provided, the function will take {@link SQLIContent::getActiveLanguage() content active language}
     * @param SQLIContent $content
     * @param string $language Language to check modification for. Must be a valid locale (xxx-XX)
     */
    protected function publicationAllowed( SQLIContent $content, $language = null )
    {
        $publicationAllowed = false;
        
        if( $this->options['modification_check'] === false ) // Modification check is explicitly asked to be avoided, then allow publication
        {
            $publicationAllowed = true;
        }
        else if( $this->contentIsModified( $content, $language ) ) // Content has been modified in provided language, allow publication
        {
            $publicationAllowed = true;
        }
        
        return $publicationAllowed;
    }
    
    /**
     * Creates initial node assignment for new content
     * @param SQLIContent $content
     * @param int $parentNodeID
     * @param bool $isMain
     * @return eZNodeAssignment
     * @internal
     */
    protected function createNodeAssignmentForContent( SQLIContent $content, $parentNodeID, $isMain = false )
    {
        $contentObject = $content->getRawContentObject();
        $contentClass = $contentObject->contentClass();
        
        $nodeAssignment = eZNodeAssignment::create( array( 'contentobject_id' => $contentObject->attribute( 'id' ),
                                                           'contentobject_version' => $contentObject->attribute( 'current_version' ),
                                                           'parent_node' => $parentNodeID,
                                                           'is_main' => $isMain ? 1 : 0,
                                                           'sort_field' => $contentClass->attribute( 'sort_field' ),
                                                           'sort_order' => $contentClass->attribute( 'sort_order' ) ) );
        $nodeAssignment->store();
        
        return $nodeAssignment;
    }
    
    /**
     * Creates a new version for content.
     * If content is a new one, a new node assignment will be created with 'parent_node_id' option ({@link self::setOptions()})
     * @param SQLIContent $content
     * @internal
     * @return eZContentObjectVersion
     */
    private function createNewVersion( SQLIContent $content )
    {
        eZDebug::accumulatorStart( 'sqlicontentpublisher_version', 'sqlicontentpublisher', 'Version creation' );
        $contentObject = $content->getRawContentObject();
        if( $content->isNew() )
        {
            $nodeAssignment = $this->createNodeAssignmentForContent( $content, $this->options['parent_node_id'], true ); // Main node assignment for new object
            $version = $contentObject->currentVersion();
            $version->setAttribute( 'modified', eZDateTime::currentTimeStamp() );
            $version->setAttribute( 'status', eZContentObjectVersion::STATUS_DRAFT );
            $version->store();
        }
        else
        {
            $version = $content->getDraft();
        }
        eZDebug::accumulatorStop( 'sqlicontentpublisher_version' );
        
        return $version;
    }
    
    /**
     * Adds a location to provided content.
     * Prefer using SQLIContent::addLocation() instead of calling this method directly
     * @param SQLILocation $location
     * @param SQLIContent $content
     * @internal
     */
    public function addLocationToContent( SQLILocation $location, SQLIContent $content )
    {
        $nodeID = $content->attribute( 'main_node_id' );
        if( !$nodeID ) // No main node ID, object has not been published at least once
            throw new SQLIContentException( __METHOD__ . ' => Cannot directly add a location to a not-yet-published content. Try to use SQLIContent::addLocation()' );
            
        $objectID = $content->attribute( 'id' );
        $locationNodeID = $location->getNodeID();
        
        // Check first if content has already an assigned node in provided location
        $assignedNodes = $content->assignedNodes( false );
        for( $i=0, $iMax=count( $assignedNodes ); $i<$iMax; ++$i )
        {
            if( $locationNodeID == $assignedNodes[$i]['parent_node_id'] )
            {
                eZDebug::writeWarning( __METHOD__ . ' => Content with ObjectID #'.$objectID.' already has a location as a child of node #'.$locationNodeID );
                return;
            }
        }
            
        
        eZDebug::accumulatorStart( 'sqlicontentpublisher_add_location', 'sqlicontentpublisher', 'Adding a location for object #'.$objectID );
        
        $selectedNodeIDArray = array( $locationNodeID );
        if( eZOperationHandler::operationIsAvailable( 'content_addlocation' ) )
        {
            $operationResult = eZOperationHandler::execute( 'content',
                                                            'addlocation', array( 'node_id'              => $nodeID,
                                                                                  'object_id'            => $objectID,
                                                                                  'select_node_id_array' => $selectedNodeIDArray ),
                                                            null,
                                                            true );
        }
        else
        {
            eZContentOperationCollection::addAssignment( $nodeID, $objectID, $selectedNodeIDArray );
        }

        $content->refreshLocations();
        
        eZDebug::accumulatorStop( 'sqlicontentpublisher_add_location' );
    }
}
