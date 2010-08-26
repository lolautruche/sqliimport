<?php
/**
 * SQLIFieldsetHolder
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage content
 */

class SQLIContentFieldsetHolder implements ArrayAccess, Iterator
{
    /**
     * Reference to the known children field sets
     * Indexed by locale: xxx-XX => SQLIContentFieldset
     * @var array( SQLIContentFieldset )
     */
    protected $fieldsets = array();
    
    /**
     * @var eZContentObject
     */
    private $contentObject;

    /**
     * Currently active language, as a locale xxx-XX
     * @var string
     */
    protected $activeLanguage;
    
    /**
     * Internal iterator pointer
     * @internal
     * @var array
     */
    protected $iteratorPointer;

    /**
     * Checks if locale is registered for current SQLIContent
     * offsetExists from ArrayAccess interface
     * @param $offset
     */
    public function offsetExists( $offset )
    {
        return isset( $this->fieldsets[$offset] );
    }

    /**
     * Returns SQLIContentFieldset for requested language
     * offsetGet from ArrayAccess interface
     * @param $offset
     */
    public function offsetGet( $offset )
    {
        if( !isset( $this->fieldsets[$offset] ) )
        {
            // Inexistent language, try to create it
            $this->addTranslation( $offset );
        }

        return $this->fieldsets[$offset];
    }

    /**
     * offsetSet from ArrayAccess interface
     * Fieldsets are readonly. Will throw an exception when trying to directly assign a value
     * @param $offset
     * @param $value
     */
    public function offsetSet( $offset, $value )
    {
        throw new SQLIContentException( __CLASS__ . ' => Fieldset direct value assignment not allowed' );
    }

    /**
     * offsetUnset from ArrayAccess interface
     * Systematically throws an exception. Fieldset unset is not allowed
     * @param $offset
     * @throws SQLIContentException
     */
    public function offsetUnset( $offset )
    {
        throw new SQLIContentException( __CLASS__ . ' => Fieldset unset not allowed' );
    }

    /**
     * Getter. Used to get attributes value for current active language
     * @param string $name Attribute Identifier
     * @return SQLIContentField
     */
    public function __get( $name )
    {
        $this->checkActiveLanguage();
        return $this->fieldsets[$this->activeLanguage]->$name;
    }

    /**
     * Setter. Used to set attributes value for current active language.
     * @param string $name Attribute identifier
     * @param string $value String representation for attribute
     */
    public function __set( $name, $value )
    {
        $this->checkActiveLanguage();
        $this->fieldsets[$this->activeLanguage]->$name = (string)$value;
    }

    /**
     * Checks if an attribute has content
     * @see ezContentObjectAttribute->hasContent()
     * @param string $name Attribute identifier
     * @return bool
     */
    public function __isset( $name )
    {
        $this->checkActiveLanguage();
        return isset( $this->fieldsets[$this->activeLanguage]->$name );
    }

    /**
     * Checks if active language is defined and valid.
     * Throws an exception if not
     * @throws SQLIContentException
     */
    protected function checkActiveLanguage()
    {
        if( !isset( $this->activeLanguage ) )
            throw new SQLIContentException( 'No active language defined for SQLIContent' );

        if( !isset( $this->fieldsets[$this->activeLanguage] ) || !$this->fieldsets[$this->activeLanguage] instanceof SQLIContentFieldset )
            throw new SQLIContentException( 'Invalid active language defined for SQLIContent' );
    }
    
    /**
     * Returns currently active language
     * @return string Language as locale xxx-XX
     */
    public function getActiveLanguage()
    {
        return $this->activeLanguage;
    }

    /**
     * Sets the currently active language when reading attribute(/object/node) properties
     * @param string $language Language locale (xxx-XX)
     * @return void
     */
    public function setActiveLanguage( $language )
    {
        $this->activeLanguage = $language;
    }
    
    /**
     * Initializes a content fieldset holder from eZContentObject
     * @param eZContentObject $object
     * @return SQLIContentFieldsetHolder
     */
    public static function fromContentObject( eZContentObject $object )
    {
        $setHolder = new self;
        $languages = $object->availableLanguages();
        $setHolder->contentObject = $object;
        
        foreach ( $languages as $lang )
        {
            $set = SQLIContentFieldset::fromDataMap( $object->fetchDataMap( false, $lang ) );
            $set->setLanguage( $lang );
            $setHolder->fieldsets[$lang] = $set;
        }
        
        // Set default language
        $setHolder->setActiveLanguage( eZContentObject::defaultLanguage() );
        
        // Init internal iterator
        $setHolder->initIterator();
        
        return $setHolder;
    }
    
    /**
     * Adds a new translation and creates a new dedicated fieldset.
     * If $lang is an invalid locale (ie. malformed or not declared in site.ini/RegionalSettings.Locale), will throw a SQLIContentException
     * @param string $lang Translation code to add, as a locale (xxx-XX)
     * @throws SQLIContentException
     */
    public function addTranslation( $lang )
    {
        $language = eZContentLanguage::fetchByLocale( $lang, true );
        if ( !$language instanceof eZContentLanguage )
            throw new SQLIContentException( "Invalid language '$lang'. Must be a valid locale declared in site.ini, RegionalSettings.Locale !");
        
        $db = eZDB::instance();
        $db->begin();
        
        $version = $this->getCurrentDraft( $lang );
        $versionNumber = $version->attribute( 'version' );
        $objectID = $this->contentObject->attribute( 'id' );
        $translatedDataMap = $this->contentObject->fetchDataMap( $versionNumber, $lang );
        
        // Check if data map exists for this language in the current draft
        // Indeed, several translations can be created for only one publication of an object
        if( !$translatedDataMap )
        {
            $classAttributes = $this->contentObject->fetchClassAttributes();
            foreach( $classAttributes as $classAttribute )
            {
                // TODO : Check if attribute is translatable
                $classAttribute->instantiate( $objectID, $lang, $versionNumber );
            }
            
            // Now clears in-memory cache for this datamap (it was fetched once above)
            // Then re-fetch the newly created translated data map
            global $eZContentObjectDataMapCache;
            unset( $eZContentObjectDataMapCache[$objectID][$versionNumber][$lang] );
            unset( $this->contentObject->ContentObjectAttributes[$versionNumber][$lang] );
            unset( $this->contentObject->DataMap[$versionNumber][$lang] );
            $translatedDataMap = $this->contentObject->fetchDataMap( $versionNumber, $lang );
        }
        
        $version->setAttribute( 'initial_language_id', $language->attribute( 'id' ) );
        $version->updateLanguageMask();
        $version->store();
        
        $db->commit();
        
        $set = SQLIContentFieldset::fromDataMap( $translatedDataMap );
        $set->setLanguage( $lang );
        $this->fieldsets[$lang] = $set;
        $this->initIterator();
    }
    
    /**
     * Returns current draft for current content object.
     * If there is no current draft, a new one will be created in provided language.
     * @param string $lang Valid locale xxx-XX. If not provided, default edit language will be used
     * @see eZContentObject::createNewVersionIn()
     * @return eZContentObjectVersion
     */
    public function getCurrentDraft( $lang = false )
    {
        $currentDraft = null;
        $db = eZDB::instance();
        
        // First check if we already have a draft
        $aFilter = array(
            'contentobject_id'      => $this->contentObject->attribute( 'id' ),
            'status'                => array( array( eZContentObjectVersion::STATUS_DRAFT, eZContentObjectVersion::STATUS_INTERNAL_DRAFT ) )
        );
        $res = eZContentObjectVersion::fetchFiltered( $aFilter , null, null );
        
        if( count( $res ) > 0 && $res[0] instanceof eZContentObjectVersion )
        {
            $currentDraft = $res[0]; // FIXME : Fetch may result several drafts. We should take the last one (highest version)
            $currentDraft->setAttribute( 'modified', eZDateTime::currentTimeStamp() );
            $currentDraft->setAttribute( 'status', eZContentObjectVersion::STATUS_DRAFT );
            $currentDraft->store();
        }
        else // No current draft found, create a new one
        {
            $db->begin();
                $currentDraft = $this->contentObject->createNewVersionIn( $lang, false, $this->contentObject->attribute( 'current_version' ) );
                $currentDraft->store();
            $db->commit();
        }
        
        return $currentDraft;
    }
    
    /**
     * Initializes internal iterator pointer
     * @internal
     */
    protected function initIterator()
    {
        $this->iteratorPointer = array_keys( $this->fieldsets );
    }
    
    /**
     * (non-PHPdoc)
     * @see Iterator::current()
     */
    public function current()
    {
        return $this->fieldsets[current( $this->iteratorPointer )];
    }
    
    /**
     * (non-PHPdoc)
     * @see Iterator::key()
     */
    public function key()
    {
        return current( $this->iteratorPointer );
    }
    
    /**
     * (non-PHPdoc)
     * @see Iterator::next()
     */
    public function next()
    {
        next( $this->iteratorPointer );
    }
    
    /**
     * (non-PHPdoc)
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        reset( $this->iteratorPointer );
    }
    
    /**
     * (non-PHPdoc)
     * @see Iterator::valid()
     */
    public function valid()
    {
        return isset( $this->fieldsets[current( $this->iteratorPointer )] );
    }
}