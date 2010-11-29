<?php
/**
 * SQLIContentField
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage content
 */

/**
 * SQLIContentField is a proxy and simplified object for eZContentObjectAttribute
 */
class SQLIContentField
{
    /**
     * Raw attribute
     * @var eZContentObjectAttribute
     */
    protected $attribute;

    /**
     * Attribute identifier
     * @var string
     */
    protected $identifier;
    
    /**
     * String representation of data to be stored in content object attribute
     * @var string
     */
    protected $data;
    
    /**
     * Array of eZ datatypes implementing fromString() method
     * Only data_type_string is stored
     * @var array
     */
    protected static $datatypesFromStringImpl = array();
    
    /**
     * Array of eZ datatypes NOT implementing fromString() method
     * Only data_type_string is stored
     * @var array
     */
    protected static $datatypesFromStringNotImpl = array();
    
    /**
     * Array of eZ datatypes implementing toString() method
     * Only data_type_string is stored
     * @var array
     */
    protected static $datatypesToStringImpl = array();
    
    /**
     * Array of eZ datatypes NOT implementing toString() method
     * Only data_type_string is stored
     * @var array
     */
    protected static $datatypesToStringNotImpl = array();

    /**
     * Initializes an SQLIContentField from an eZContentObjectAttribute
     * @param eZContentObjectAttribute $attribute
     * @return SQLIContentField
     */
    public static function fromContentObjectAttribute( eZContentObjectAttribute $attribute )
    {
        $field = new self;
        $field->attribute = $attribute;
        $field->identifier = $attribute->attribute( 'contentclass_attribute_identifier' );
        return $field;
    }

    /**
     * Check if given attribute exists.
     * All "classic" attributes can be used (See {@link eZContentObjectAttribute::definition()}).
     * @param $name
     * @return bool
     */
    public function __isset( $name )
    {
        if( !$this->attribute instanceof eZContentObjectAttribute )
        {
            throw new SQLIContentException('"'.$this->name.'" SQLIContentField doesn\'t have a valid related content object attribute');
        }

        return $this->attribute->hasAttribute( $name );
    }

    /**
     * Getter
     * Returns given attribute if it exists. Will throw an exception otherwise.
     * All "classic" attributes can be used (See {@link eZContentObjectAttribute::definition()}).
     * You can also use "serializedXML" to get the serialized version of the attribute through the eZPackage mechanism
     * @param $name
     * @throws ezcBasePropertyNotFoundException
     * @return mixed
     */
    public function __get( $name )
    {
        $ret = null;
        
        switch( $name )
        {
            // returns the serialized version of the attribute through the eZPackage mechanism
            case 'serializedXML':
                $ret = $this->attribute->serialize( new eZPackage );
                break;
            case 'identifier':
                $ret = $this->identifier;
            default:
                if ( $this->attribute->hasAttribute( $name ) )
                    $ret = $this->attribute->attribute( $name );
                else
                    throw new ezcBasePropertyNotFoundException( $name );
        }
        
        return $ret;
    }

    /**
     * Setter
     * Sets value to an attribute for the content object attribute.
     * All "classic" attributes can be used (See {@link eZContentObjectAttribute::definition()}).
     * If attribute doesn't exist, will throw an exception
     * @param $name Attribute name
     * @param $value Attribute value
     * @throws ezcBasePropertyNotFoundException
     * @return void
     */
    public function __set( $name, $value )
    {
        if( !$this->attribute->hasAttribute( $name ) )
        {
            throw new ezcBasePropertyNotFoundException( $name );
        }

        $this->attribute->setAttribute( $name, $value );
    }

    /**
     * Returns string representation of content.
     * To work properly, datatype needs to implement toString() method.
     * If this method is not implemented, will return data_text
     * @see eZContentObjectAttribute::toString()
     * @return string
     */
    public function __toString()
    {
        $ret = null;
        
        if( method_exists( $this->attribute->dataType(), 'toString' ) )
        {
            $ret = $this->attribute->toString();
        }
        else
        {
            $ret = $this->attribute->attribute( 'data_text' );
        }

        return $ret;
    }

    /**
     * Generic method for calling current attribute methods
     * If method isn't implemented, will throw an exception
     * @param $method Method name
     * @param $arguments
     * @throws ezcBasePropertyNotFoundException
     * @return mixed
     */
    public function __call( $method, $arguments )
    {
        if ( method_exists( $this->attribute, $method ) )
            return call_user_func_array( array( $this->attribute, $method ), $arguments );
        else
            throw new ezcBasePropertyNotFoundException( $method );
    }

    /**
     * Returns reference eZContentObjectAttribute
     * @return eZContentObjectAttribute
     */
    public function getRawAttribute()
    {
        return $this->attribute;
    }
    
    /**
     * Sets reference eZContentObjectAttribute
     * @param eZContentObjectAttribute $attribute
     */
    public function setRawAttribute( eZContentObjectAttribute $attribute )
    {
        $this->attribute = $attribute;
    }
    
    /**
     * Stores string representation of data for current field.<br />
     * If available for datatype, {@link eZDataType::fromString()} will be used
     * to store data in content object attribute.
     * If not available, data will stored in <i>data_text</i> attribute field as is.<br />
     * Note : Data will be stored at publish time
     * @param string $data
     */
    public function setData( $data )
    {
        $this->data = (string) $data;
    }
    
    /**
     * Returns data for current field
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * Resets data for current field.
     * @internal
     */
    public function resetData()
    {
        $this->data = null;
    }
    
    /**
     * Checks if content has been modified for this field.
     * Compares $this->data vs already published data
     * @return bool
     */
    public function isModified()
    {
        $isModified = false;
        $datatype = $this->attribute->attribute( 'data_type_string' );
        $importINI = eZINI::instance( 'sqliimport.ini' );
        $diffHandlerMap = $importINI->variable( 'ContentSettings', 'DiffHandlerMap' );
        $defaultDiffHandler = $importINI->variable( 'ContentSettings', 'DefaultDiffHandler' );
        $aSkipDatatypes = $importINI->variable( 'ContentSettings', 'ContentModificationSkipDatatypes' ); // Skip modification check for these datatypes
        
        if( !in_array( $datatype, $aSkipDatatypes ) ) // Process diff operation for non-skipped datatypes
        {
            $aDiffHandlerParams = array( $this->data, $this->attribute );
            
            if( isset( $diffHandlerMap[$datatype] ) ) // A diff handler is defined, use it
            {
                $isModified = call_user_func_array( array( $diffHandlerMap[$datatype], 'contentIsModified' ), $aDiffHandlerParams );
            }
            else // No diff handler for this datatype, use default diff handler
            {
                $isModified = call_user_func_array( array( $defaultDiffHandler, 'contentIsModified' ) , $aDiffHandlerParams );
            }
        }
        
        return $isModified;
    }
    
    /**
     * Generic method wrapping datatype's fromString() method if implemented
     * Checks if fromString() is implemented in the field's datatype
     * If not, data_text will be used by default
     * @see http://projects.ez.no/sqliimport/forum/issues/two_little_thing
     * @param string $data String representation of data to inject
     * @return void
     */
    public function fromString( $data )
    {
        $datatype = $this->attribute->attribute( 'data_type_string' );
        $fromStringImplemented = null;
        
        // First check if datatype is already known as implementing fromString() or not
        // (Better performance as the check is made through Reflection API)
        if( in_array( $datatype, self::$datatypesFromStringImpl ) ) // Already known as implementing it
        {
            $fromStringImplemented = true;
        }
        else if( in_array( $datatype, self::$datatypesFromStringNotImpl ) ) // Already known as NOT implementing it
        {
            $fromStringImplemented = false;
        }
        else
        {
            $reflector = new ReflectionObject( $this->attribute->dataType() );
            $callerClass = $reflector->getMethod( 'fromString' )->class;
            if( $callerClass == 'eZDataType' ) // Caller class is the original eZDataType class => fromString() is not implemented
            {
                self::$datatypesFromStringNotImpl[] = $datatype;
                $fromStringImplemented = false;
            }
            else // Caller class is the datatype itself, so we consider that fromString() is implemented
            {
                self::$datatypesFromStringImpl[] = $datatype;
                $fromStringImplemented = true;
            }
        }
        
        // Now insert data through the appropriate way
        if( $fromStringImplemented )
        {
            $this->attribute->fromString( $data );
        }
        else
        {
            $this->attribute->setAttribute( 'data_text', $data );
        }
    }
    
    /**
     * Generic method emulating datatype's toString() method
     * @see SQLIContentField::convertAttributeToString()
     * @return string
     */
    public function toString()
    {
        return self::convertAttributeToString( $this->attribute );
    }
    
    /**
     * Generic method wrapping datatype's toString() method if implemented
     * Checks if toString() is implemented in the field's datatype
     * If not, data_text will be used by default
     * @return string
     */
    public static function convertAttributeToString( eZContentObjectAttribute $attribute )
    {
        $datatype = $attribute->attribute( 'data_type_string' );
        $toStringImplemented = null;
        $ret = null;
        
        // First check if datatype is already known as implementing fromString() or not
        // (Better performance as the check is made through Reflection API)
        if( in_array( $datatype, self::$datatypesToStringImpl ) ) // Already known as implementing it
        {
            $toStringImplemented = true;
        }
        else if( in_array( $datatype, self::$datatypesToStringNotImpl ) ) // Already known as NOT implementing it
        {
            $toStringImplemented = false;
        }
        else
        {
            $reflector = new ReflectionObject( $attribute->dataType() );
            $callerClass = $reflector->getMethod( 'toString' )->class;
            if( $callerClass == 'eZDataType' ) // Caller class is the original eZDataType class => fromString() is not implemented
            {
                self::$datatypesToStringNotImpl[] = $datatype;
                $toStringImplemented = false;
            }
            else // Caller class is the datatype itself, so we consider that fromString() is implemented
            {
                self::$datatypesToStringImpl[] = $datatype;
                $toStringImplemented = true;
            }
        }
        
        // Now insert data through the appropriate way
        if( $toStringImplemented )
        {
            $ret = $attribute->toString();
        }
        else
        {
            $ret = $attribute->attribute( 'data_text' );
        }
        
        return $ret;
    }
}