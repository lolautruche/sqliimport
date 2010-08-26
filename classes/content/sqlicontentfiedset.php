<?php
/**
 * SQLIContentFieldset
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage content
 */

class SQLIContentFieldset implements Iterator
{
    /**
     * Current language, as locale xxx-XX
     * @var string
     */
    protected $language;

    /**
     * Reference to the fields of this fieldset
     * Array indexed by attribute identifier
     * @var array( SQLIContentField )
     */
    protected $fields = array();
    
    /**
     * Internal iterator pointer for Iterator implementation
     * @internal
     * @var array
     */
    protected $iteratorPointer = array();

    /**
     * Getter. Returns given field
     * @param $name Attribute identifier
     * @return SQLIContentField
     */
    public function __get( $name )
    {
        if( !isset( $this->fields[$name] ) || !$this->fields[$name] instanceof SQLIContentField )
            throw new SQLIContentException( "Invalid '$name' field for current SQLIContent" );

        return $this->fields[$name];
    }

    /**
     * Setter
     * Assigns a value to given field.
     * $value must be a string
     * Best results when datatype implements fromString() method (@see eZDataType::fromString())
     * @param string $name Field name
     * @param string $value Field string value
     */
    public function __set( $name, $value )
    {
        if( !isset( $this->fields[$name] ) || !$this->fields[$name] instanceof SQLIContentField )
            throw new SQLIContentException( "Invalid '$name' field for current SQLIContent" );

        $this->fields[$name]->setData( $value );
    }

    /**
     * Checks if given field has content
     * @param $name
     * @return bool
     */
    public function __isset( $name )
    {
        return isset( $this->fields[$name] );
    }

    /**
     * Returns language for current fieldset as a locale xxx-XX
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }
    
    /**
     * Assigns language to fieldset
     * @param string $lang Language as a locale xxx-XX
     */
    public function setLanguage( $lang )
    {
        $this->language = $lang;
    }

    /**
     * Initializes a fieldset from a content object data map
     * @param array $dataMap
     * @return SQLIContentFieldset
     */
    public static function fromDataMap( array $dataMap )
    {
        $set = new SQLIContentFieldset();

        foreach( $dataMap as $key => $attribute )
        {
            $set->setContentObjectAttribute( $attribute );
        }
        
        $set->initIterator();
        
        return $set;
    }
    
    /**
     * Refreshes content object attributes for current fields.
     * Mandatory to update good attributes when publishing
     * @param eZContentObjectVersion $version Draft to be published
     * @see SQLIContentPublisher::publish()
     */
    public function refreshDataMap( eZContentObjectVersion $version )
    {
        $contentObject = $version->contentObject();
        // Copy missing translations from published version to current draft if needed
        eZContentOperationCollection::copyTranslations( $contentObject->attribute( 'id' ), $version->attribute( 'version' ) );
        $dataMap = $contentObject->fetchDataMap( $version->attribute( 'version' ), $this->language );
        
        foreach( $this->fields as $fieldName => $field )
        {
            if( isset( $dataMap[$fieldName] ) )
                $field->setRawAttribute( $dataMap[$fieldName] );
            else
                eZDebug::writeWarning( __METHOD__ . ' => Attribute "'.$fieldName.'" not set in data map. Cannot refresh field.', 'SQLIImport' );
        }
    }
    
    /**
     * Assigns a new field to the fieldset from an eZContentObjectAttribute object
     * @param eZContentObjectAttribute $attribute
     */
    protected function setContentObjectAttribute( eZContentObjectAttribute $attribute )
    {
        $identifier = $attribute->attribute( 'contentclass_attribute_identifier' );
        $this->fields[$identifier] = SQLIContentField::fromContentObjectAttribute( $attribute );
    }
    
    /**
     * Resets input data for each field.
     * Only data that has been assigned will be reset, not content object attribute.
     */
    public function resetInputData()
    {
        foreach( $this->fields as $field )
        {
            $field->resetData();
        }
    }
    
    /**
     * Initializes internal iterator pointer
     * @internal
     */
    protected function initIterator()
    {
        $this->iteratorPointer = array_keys( $this->fields );
    }
    
    /**
     * (non-PHPdoc)
     * @see Iterator::current()
     */
    public function current()
    {
        return $this->fields[current( $this->iteratorPointer )];
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
        return isset( $this->fields[current( $this->iteratorPointer )] );
    }
}