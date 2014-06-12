<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.websvc.lib
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: Array2Xml.php 5433 2014-05-04 05:54:04Z rockyswen@gmail.com $
 */

/**
 *
 * Array 2 XML class
 * Convert an array or multi-dimentional array to XML
 *
 * @author Kevin Waterson
 * @copyright 2009 PHPRO.ORG
 *
 */
class array2xml extends DomDocument
{

    public $nodeName;

    private $xpath;

    private $root;

    private $node_name;


    /**
    * Constructor, duh
    *
    * Set up the DOM environment
    *
    * @param    string    $root        The name of the root node
    * @param    string    $nod_name    The name numeric keys are called
    *
    */
    public function __construct($root='root', $node_name='node')
    {
        parent::__construct();

        /*** set the encoding ***/
        $this->encoding = "UTF-8"; //"ISO-8859-1";

        /*** format the output ***/
        $this->formatOutput = true;

        /*** set the node names ***/
        $this->node_name = $node_name;

        /*** create the root element ***/
        $this->root = $this->appendChild($this->createElement( $root ));

        $this->xpath = new DomXPath($this);
    }
	
	public function setDataAttribute($attribute, $value)
	{
		$this->documentElement->setAttribute($attribute, $value);
	}

    /*
    * creates the XML representation of the array
    *
    * @access    public
    * @param    array    $arr    The array to convert
    * @aparam    string    $node    The name given to child nodes when recursing
    *
    */
    public function createNode( $arr, $node = null)
    {
        if (is_null($node))
        {
            $node = $this->root;
        }
        foreach($arr as $element => $value) 
        {
            $element = is_numeric( $element ) ? $this->node_name : $element;

            $child = $this->createElement($element, (is_array($value)||is_object($value) ? null : $value));
            $node->appendChild($child);

            if (is_array($value)||is_object($value))
            {
                self::createNode($value, $child);
            }
        }
    }
    /*
    * Return the generated XML as a string
    *
    * @access    public
    * @return    string
    *
    */
    public function __toString()
    {
        return $this->saveXML();
    }

    /*
    * array2xml::query() - perform an XPath query on the XML representation of the array
    * @param str $query - query to perform
    * @return mixed
    */
    public function query($query)
    {
        return $this->xpath->evaluate($query);
    }

} // end of class
?>