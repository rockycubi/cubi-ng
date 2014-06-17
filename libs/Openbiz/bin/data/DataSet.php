<?php
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.data
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: DataSet.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

/**
 * DataRecord class  is the wrapper class of record array.
 * It is recommmended to be used in data update and deletion.
 *
 * @package openbiz.bin.data
 * @author Rocky Swen
 * @copyright Copyright (c) 2007-2009
 * @access public
 **/
class DataSet implements Iterator, ArrayAccess, Countable 
{
    /**
     * Record in array format
     *
     * @var array
     */
    protected $m_var = array();

    /**
     * Reference of {@link BizDataObj}
     *
     * @var BizDataObj
     */
    protected $m_BizObj = null;

    /**
     * Initialize DataSet
     *
     * @param array $recArray record array.
     * @param BizDataObj $bizObj BizDataObj instance
     * @return void
     */
    public function __construct($bizObj)
    {
        $this->m_BizObj = $bizObj;
    }

    // Iterator methods BEGIN---------

    /**
     * Get item value of array
     *
     * @param mixed $key
     * @return mixed
     */
    public function get($key)
    {
        return isset($this->m_var[$key]) ? $this->m_var[$key] : null;
    }

    /**
     * Set item value of array
     *
     * @param mixed $key
     * @param mixed $val
     */
    public function set($key, $val)
    {
        $this->m_var[$key] = $val;

    }

    /**
     * Rewind, Send pointer to start of list
     *
     * @return void
     */
    public function rewind()
    { 
        reset($this->m_var);
    }

    /**
     * Return element at current pointer position
     *
     * @return mixed
     */
    public function current()
    { 
        return current($this->m_var);
    }


    /**
     * Return current key (i.e., pointer value)
     *
     * @return mixed
     */
    public function key()
    { 
        return key($this->m_var);
    }

    /**
     * Return element at current pointer and advance pointer
     *
     * @return mixed
     */
    public function next()
    { 
        return next($this->m_var);
    }

    /**
     * Confirm that there is an element at the current pointer position
     *
     * @return boolean
     */
    public function valid()
    { 
        return $this->current() !== false;
    }
    
    // ArrayAccess methods
    
    /**
     * Check is offset value (by key) exist?
     *
     * @param mixed $key
     * @return mixed
     */
    public function offsetExists($key)
    { 
        return isset($this->m_var[$key]);
    }

    /**
     * Get value of offset (by key)
     *
     * @param mixed $key
     * @return mixed
     */
    public function offsetGet($key)
    { 
        return $this->get($key);
    }

    /**
     * Set value of offset by key
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function offsetSet($key, $value)
    { 
        $this->set($key, $value);
    }

    /**
     * Unset element by key
     *
     * @param mixed $key key of element
     */
    public function offsetUnset($key)
    { 
        unset($this->m_var[$key]);
    }
    
    public function count() 
    {
        return count($this->m_var);
    }

    /**
     * Return record in array
     *
     * @return array record array
     */
    public function toArray()
    {
        return $this->m_var;
    }

    /**
     * Get reference object with given object name
     *
     * @param string $objName name of the object reference
     * @return obejct the instance of reference object
     */
    public function getRefObject($objName)
    {
        return $this->m_BizObj->getRefObject($objName);
    }

}
?>