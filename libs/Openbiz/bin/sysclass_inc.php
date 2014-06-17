<?php

/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: sysclass_inc.php 4179 2011-05-26 07:40:53Z rockys $
 */

/**
 * MetaObject is the base class of all derived metadata-driven classes
 *
 * @package   openbiz.bin
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
abstract class MetaObject
{

    /**
     * Name of meta-object
     *
     * @var string
     */
    public $m_Name;

    /**
     * Class name of meta-object
     *
     * @var string
     */
    public $m_Class;

    /**
     * Package of meta-object
     *
     * @var string
     */
    public $m_Package;

    /**
     * Description of meta-object
     *
     * @var string
     */
    public $m_Description;
    public $m_Access;

    function __construct(&$xmlArr)
    {
        
    }

    //function __destruct() {}

    /**
     * Read meta data
     *
     * @param array $xmlArr
     * @return void
     */
    protected function readMetadata(&$xmlArr)
    {
        $rootKeys = array_keys($xmlArr);
        $rootKey = $rootKeys[0];
        if ($rootKey != "ATTRIBUTES")
        {
            $this->m_Name = isset($xmlArr[$rootKey]["ATTRIBUTES"]["NAME"]) ? $xmlArr[$rootKey]["ATTRIBUTES"]["NAME"] : null;
            $this->m_Description = isset($xmlArr[$rootKey]["ATTRIBUTES"]["DESCRIPTION"]) ? $xmlArr[$rootKey]["ATTRIBUTES"]["DESCRIPTION"] : null;
            $this->m_Package = isset($xmlArr[$rootKey]["ATTRIBUTES"]["PACKAGE"]) ? $xmlArr[$rootKey]["ATTRIBUTES"]["PACKAGE"] : null;
            $this->m_Class = isset($xmlArr[$rootKey]["ATTRIBUTES"]["CLASS"]) ? $xmlArr[$rootKey]["ATTRIBUTES"]["CLASS"] : null;
            $this->m_Access = isset($xmlArr[$rootKey]["ATTRIBUTES"]["ACCESS"]) ? $xmlArr[$rootKey]["ATTRIBUTES"]["ACCESS"] : null;
        } else
        {
            $this->m_Name = isset($xmlArr["ATTRIBUTES"]["NAME"]) ? $xmlArr["ATTRIBUTES"]["NAME"] : null;
            $this->m_Description = isset($xmlArr["ATTRIBUTES"]["DESCRIPTION"]) ? $xmlArr["ATTRIBUTES"]["DESCRIPTION"] : null;
            $this->m_Package = isset($xmlArr["ATTRIBUTES"]["PACKAGE"]) ? $xmlArr["ATTRIBUTES"]["PACKAGE"] : null;
            $this->m_Class = isset($xmlArr["ATTRIBUTES"]["CLASS"]) ? $xmlArr["ATTRIBUTES"]["CLASS"] : null;
            $this->m_Access = isset($xmlArr["ATTRIBUTES"]["ACCESS"]) ? $xmlArr["ATTRIBUTES"]["ACCESS"] : null;
        }
    }

    public function getModuleName($name)
    {
        return substr($name, 0, intval(strpos($name, '.')));
    }

    /**
     * Read metadata collection
     * 
     * @param array $xmlArr
     * @param array $metaList
     * @return void
     */
    protected function readMetaCollection(&$xmlArr, &$metaList)
    {
        if (!$xmlArr)
        {
            $metaList = null;
            return;
        }
        if (isset($xmlArr["ATTRIBUTES"]))
            $metaList[] = $xmlArr;
        else
            $metaList = $xmlArr;
    }

    /**
     * Set Prefix Package
     *
     * @param string $name
     * @return string
     */
    protected function prefixPackage($name)
    {
        if ($name && !strpos($name, ".") && ($this->m_Package)) // no package prefix as package.object, add it
            $name = $this->m_Package . "." . $name;

        return $name;
    }

    /**
     * Get property
     * 
     * @param string $propertyName
     * @return <type>
     */
    public function getProperty($propertyName)
    {
        // TODO: really like this?
        if (isset($this->$propertyName))
            return $this->$propertyName;
        return null;
    }

    /**
     * Check is allow access?
     * 
     * @global BizSystem $g_BizSystem
     * @param <type> $access
     * @return <type>
     */
    public function allowAccess($access = null)
    {
        if (CLI)
        {
            return ALLOW;
        }
        if (!$access)
            $access = $this->m_Access;
        if ($access)
        {
            return BizSystem::allowUserAccess($access);
        }
        return ALLOW;
    }

    protected function getElementObject(&$xmlArr, $defaultClassName, $parentObj = null)
    {
        // find the class attribute
        $className = isset($xmlArr["ATTRIBUTES"]['CLASS']) ? $xmlArr["ATTRIBUTES"]['CLASS'] : $defaultClassName;

        if ((bool) strpos($className, "."))
        {
            $a_package_name = explode(".", $className);
            $className = array_pop($a_package_name);
            $clsLoaded = BizClassLoader::loadMetadataClass($className, implode(".", $a_package_name));
            if (!$clsLoaded)
                trigger_error("Cannot find the load class $className", E_USER_ERROR);
        }
        //echo "classname is $className\n";
        $obj = new $className($xmlArr, $parentObj);
        return $obj;
    }

}

/**
 * MetaIterator class
 * MetaIterator is the base class of all derived metadata-driven classes who support iteration
 *
 * @package   openbiz.bin
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class MetaIterator implements Iterator
{

    /**
     * Parent object
     * @var object
     */
    protected $m_prtObj = null;

    /**
     * Store value
     * @var array
     */
    protected $m_var = array();

    /**
     * Contructor of class
     *
     * @param array $xmlArr
     * @param string $childClassName
     * @param object $parentObj
     * @return void
     */
    public function __construct(&$xmlArr, $childClassName, $parentObj = null)
    {
        //if (is_array($array)) $this->var = $array;
        $this->m_prtObj = $parentObj;
        if (!$xmlArr)
            return;

        if (isset($xmlArr["ATTRIBUTES"]))
        {
            $className = isset($xmlArr["ATTRIBUTES"]['CLASS']) ? $xmlArr["ATTRIBUTES"]['CLASS'] : $childClassName;
            if ((bool) strpos($className, "."))
            {
                $a_package_name = explode(".", $className);
                $className = array_pop($a_package_name);
                //require_once(BizSystem::getLibFileWithPath($className, implode(".", $a_package_name)));
                $clsLoaded = BizClassLoader::loadMetadataClass($className, implode(".", $a_package_name));
            }
            //if (!$clsLoaded) trigger_error("Cannot find the load class $className", E_USER_ERROR);
            $obj = new $className($xmlArr, $parentObj);
            $this->m_var[$obj->m_Name] = $obj;
        } else
        {
            foreach ($xmlArr as $child)
            {
                $className = isset($child["ATTRIBUTES"]['CLASS']) ? $child["ATTRIBUTES"]['CLASS'] : $childClassName;

                /**
                 * If a '.' is found within className we need to require such class
                 * and then get the className after the last dot
                 * ex. shared.dataobjs.FieldName, in this case FieldName is the class, shared/dataobjs the path
                 *
                 * The best solution to this is enable object factory to specify its resulting object constructor parameters
                 */
                if ($className)
                { //bug fixed by jixian for resolve load an empty classname
                    if ((bool) strpos($className, "."))
                    {
                        $a_package_name = explode(".", $className);
                        $className = array_pop($a_package_name);
                        //require_once(BizSystem::getLibFileWithPath($className, implode(".", $a_package_name)));
                        $clsLoaded = BizClassLoader::loadMetadataClass($className, implode(".", $a_package_name));
                    } elseif ($parentObj->m_Package)
                    {
                        /* if(is_file(BizSystem::getLibFileWithPath($className, $parentObj->m_Package))){
                          require_once(BizSystem::getLibFileWithPath($className, $parentObj->m_Package));
                          }; */
                        $clsLoaded = BizClassLoader::loadMetadataClass($className, $parentObj->m_Package);
                    }
                    //if (!$clsLoaded) trigger_error("Cannot find the load class $className", E_USER_ERROR);
                    $obj = new $className($child, $parentObj);
                    $this->m_var[$obj->m_Name] = $obj;
                }
            }
        }
    }

    /**
     * Merge to another MetaIterator object
     *
     * @param MetaIterator $anotherMIObj another MetaIterator object
     * @return void
     */
    public function merge(&$anotherMIObj)
    {
        $old_m_var = $this->m_var;
        $this->m_var = array();
        foreach ($anotherMIObj as $key => $value)
        {
            if (!$old_m_var[$key])
            {
                $this->m_var[$key] = $value;
            } else
            {
                $this->m_var[$key] = $old_m_var[$key];
            }
        }
        foreach ($old_m_var as $key => $value)
        {
            if (!key_exists($key, $this->m_var))
            {
                $this->m_var[$key] = $value;
            }
        }
    }

    /**
     * Get value
     *
     * @param mixed $key
     * @return mixed
     */
    public function get($key)
    {
        return isset($this->m_var[$key]) ? $this->m_var[$key] : null;
    }

    /**
     * Set value
     *
     * @param mixed $key
     * @param mixed $val
     */
    public function set($key, $val)
    {
        $this->m_var[$key] = $val;
    }

    /**
     * Clear value
     *
     * @param mixed $key
     * @param mixed $val
     */
    public function clear($key)
    {
        unset($this->m_var[$key]);
    }

    public function count()
    {
        return count($this->m_var);
    }

    /**
     * Rewind
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->m_var);
    }

    /**
     * Current item
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->m_var);
    }

    /**
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->m_var);
    }

    /**
     * 
     * @return mixed
     */
    public function next()
    {
        return next($this->m_var);
    }

    /**
     *
     * @return boolean
     */
    public function valid()
    {
        return $this->current() !== false;
    }

}

/**
 * Parameter class
 *
 * @package   openbiz.bin
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class Parameter
{
    public $m_Name, $m_Value, $m_Required, $m_InOut;

    public function __construct(&$xmlArr)
    {
        $this->m_Name = isset($xmlArr["ATTRIBUTES"]["NAME"]) ? $xmlArr["ATTRIBUTES"]["NAME"] : null;
        $this->m_Value = isset($xmlArr["ATTRIBUTES"]["VALUE"]) ? $xmlArr["ATTRIBUTES"]["VALUE"] : null;
        $this->m_Required = isset($xmlArr["ATTRIBUTES"]["REQUIRED"]) ? $xmlArr["ATTRIBUTES"]["REQUIRED"] : null;
        $this->m_InOut = isset($xmlArr["ATTRIBUTES"]["INOUT"]) ? $xmlArr["ATTRIBUTES"]["INOUT"] : null;
    }

    /**
     * Get property
     *
     * @param string $propertyName property name
     * @return mixed
     */
    public function getProperty($propertyName)
    {
        if ($propertyName == "Value")
            return $this->m_Value;
        return null;
    }

}

/**
 * iSessionObject interface - stateful metadata-driven classed need to implement SetSessionVars and GetSessionVars
 *
 * @package   openbiz.bin
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
interface iSessionObject
{

    public function setSessionVars($sessCtxt);

    public function getSessionVars($sessCtxt);
}

/**
 * iUIControl interface, all UI classes need to implement Render method
 *
 * @package   openbiz.bin
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
interface iUIControl
{

    public function render();
}

/**
 * BDOException
 *
 * @package   openbiz.bin
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class BDOException extends Exception
{
    
}

/**
 * BFMException
 *
 * @package   openbiz.bin
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class BFMException extends Exception
{
    
}

/**
 * BSVCException
 *
 * @package   openbiz.bin
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class BSVCException extends Exception
{
    
}

/**
 * ValidationException
 *
 * @package   openbiz.bin
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class ValidationException extends Exception
{
    public $m_Errors;   // key, errormessage pairs

    public function __construct($errors)
    {
        $this->m_Errors = $errors;
        $message = "";
        foreach ($errors as $key => $err)
        {
            $message .= "$key = $err, ";
        }
        $this->message = $message;
    }
	public function getErrors() { 
		return $this->m_Errors;
	}
}

include_once "EventManager.php";
?>