<?php
/**
 * File containing SQLILocation class
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 * @subpackage location
 */

class SQLILocation
{
    /**
     * Node
     * @var eZContentObjectTreeNode
     */
    protected $node;
    
    /**
     * NodeID for this location
     * @var int
     */
    protected $nodeID;
    
    /**
     * Protected constructor
     */
    protected function __construct()
    {
        
    }
    
    /**
     * Initializes a location from eZContentObjectTreeNode
     * @param eZContentObjectTreeNode $node
     * @return SQLILocation
     */
    public static function fromNode( eZContentObjectTreeNode $node )
    {
        $location = new self();
        $location->node = $node;
        $location->nodeID = $node->attribute( 'node_id' );
        return $location;
    }
    
    /**
     * Initializes a location from NodeID
     * @param int $nodeID
     * @return SQLILocation
     */
    public static function fromNodeID( $nodeID )
    {
        $node = eZContentObjectTreeNode::fetch( $nodeID );
        if ( !$node instanceof eZContentObjectTreeNode )
            throw new SQLILocationException( "Unable to find eZContentObjectTreeNode with NodeID #$nodeID" );
        
        $location = self::fromNode( $node );
        return $location;
    }
    
    /**
     * Returns location NodeID
     * @return int
     */
    public function getNodeID()
    {
        return $this->nodeID;
    }
    
    /**
     * Getter
     * Returns given attribute for current content node if it exists (ie. path_string).
     * Will throw an exception otherwise.
     * All "classic" attributes can be used (See {@link eZContentObjectTreeNode::definition()}).
     * @param $name
     * @throws ezcBasePropertyNotFoundException
     * @return mixed
     */
    public function __get( $name )
    {
        $this->getNode();
        $ret = null;
        
        switch( $name )
        {
            default:
                if ( $this->node->hasAttribute( $name ) )
                    $ret = $this->node->attribute( $name );
                else
                    throw new ezcBasePropertyNotFoundException( $name );
        }
        
        return $ret;
    }
    
    /**
     * Setter
     * Sets value to an attribute for the content node.
     * All "classic" attributes can be used (See {@link eZContentObjectTreeNode::definition()}).
     * If attribute doesn't exist, will throw an exception
     * @param $name Attribute name
     * @param $value Attribute value
     * @throws ezcBasePropertyNotFoundException
     * @return void
     */
    public function __set( $name, $value )
    {
        $this->getNode();
        
        if( !$this->node->hasAttribute( $name ) )
        {
            throw new ezcBasePropertyNotFoundException( $name );
        }

        $this->node->setAttribute( $name, $value );
    }
    
    /**
     * Check if given attribute exists.
     * All "classic" attributes can be used (See {@link eZContentObjectTreeNode::definition()}).
     * @param $name
     */
    public function __isset( $name )
    {
        $this->getNode();
        
        return $this->node->hasAttribute( $name );
    }
    
    /**
     * Generic method for calling current content node methods.
     * If method isn't implemented, will throw an exception
     * @param $method Method name
     * @param $arguments
     * @throws ezcBasePropertyNotFoundException
     * @return mixed
     */
    public function __call( $method, $arguments )
    {
        $this->getNode();
        
        if ( method_exists( $this->node, $method ) )
            return call_user_func_array( array( $this->node, $method ), $arguments );
        else
            throw new ezcBasePropertyNotFoundException( $method );
    }
    
    /**
     * Fetches internal node if not already available
     * @throws SQLILocationException
     * @return void
     */
    protected function getNode()
    {
        if ( !$this->node instanceof eZContentObjectTreeNode )
        {
            $this->node = eZContentObjectTreeNode::fetch( $this->nodeID );
            if ( !$this->node instanceof eZContentObjectTreeNode )
                throw new SQLILocationException( "Unable to find eZContentObjectTreeNode with NodeID #$this->nodeID" );
        }
    }
}
