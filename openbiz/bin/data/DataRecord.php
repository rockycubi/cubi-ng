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
 * @version   $Id: DataRecord.php 4086 2011-05-03 06:00:35Z rockys $
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
class DataRecord implements Iterator, ArrayAccess
{
    /**
     * Record in array format
     *
     * @var array
     */
    protected $m_var = array();

    /**
     * Old record in array format
     *
     * @var array
     */
    protected $m_var_old = array();

    /**
     * Reference of {@link BizDataObj}
     *
     * @var BizDataObj
     */
    protected $m_BizObj = null;

    /**
     * Initialize DataRecord with record array.
     * Creat a new record - new {@link DataRecord(null, $bizObj)}
     * Get a current record - new {@link DataRecord($recArr, $bizObj)}
     *
     * @param array $recArray record array.
     * @param BizDataObj $bizObj BizDataObj instance
     * @return void
     */
    public function __construct($recArray, $bizObj)
    {
        if ($recArray != null)
        {
            if (is_array($recArray)) {
                $this->m_var = $recArray;
                $this->m_var_old = $recArray;
            }
            else if (is_a($recArray,"DataRecord")) {
                $this->m_var = $recArray->toArray();
                $this->m_var_old = $this->m_var;
            }
        }
        else
            $this->m_var = $bizObj->newRecord();

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
    
    public function getOldValue($key)
    {
    	return isset($this->m_var_old[$key]) ? $this->m_var_old[$key] : null;
    }

	public function getDataObj()
    {
    	return $this->m_BizObj;
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

    /**
     * Get field value with property format
     * <pre>
     *   $value = $obj->get($fieldName); => $value = $obj->fieldName;
     * </pre>
     *
     * @param string $fieldName name of a field
     * @return mixed value of the field
     */
    public function __get($fieldName)
    {
        return $this->get($fieldName);
    }

    /**
     * Set field value with property format
     * <pre>
     *   $obj->set($fieldName, $value); => $obj->fieldName = $value;
     * </pre>
     *
     * @param string $fieldName name of a field
     * @param mixed value of the field
     * @return avoid
     */
    public function __set($fieldName, $value)
    {
        $this->set($fieldName, $value);
    }

    /**
     * Save record. This function calls {@link BizDataObj::updateRecord} method internally
     *
     * @return boolean true for success
     */
    public function save()
    {
        if (count($this->m_var_old) > 0)
            $ok = $this->m_BizObj->updateRecord($this->m_var, $this->m_var_old);
        else
            $ok = $this->m_BizObj->insertRecord($this->m_var);

        // repopulate current record with bizdataobj activerecord
        if ($ok)
        {
            $this->m_var = $this->m_BizObj->getActiveRecord();
            $this->m_var_old = $this->m_var;
        }

        return $ok;
    }

    /**
     * Delete record. This function calls {@link BizDataObj::deleteRecord} method internally
     *
     * @return boolean true for success
     */
    public function delete()
    {
        return $this->m_BizObj->deleteRecord($this->m_var);
    }

    /**
     * Get error message
     *
     * @return string error message
     */
    public function getError()
    {
        return $this->m_BizObj->getErrorMessage();
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
        $this->m_BizObj->setActiveRecord($this->m_var);
		return $this->m_BizObj->getRefObject($objName);
    }

}
?>