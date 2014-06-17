<?php
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.util
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: QueryStringParam.php 2630 2010-11-27 08:41:34Z jixian2003 $
 */

/**
 * QueryStringParam class 
 *
 * @package openbiz.bin.util
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class QueryStringParam
{
    //protected $m_QueryString = "";

    /**
     * Array of params
     *
     * @var array
     */
    protected static $m_Params = array();

    /**
     * Count of params
     *
     * @var int
     */
    private static $_counter = 1;

    /**
     * Format query string
     *
     * @param string $field
     * @param string $opr operator
     * @param mixed $value
     * @return string
     */
    public static function formatQueryString($field, $opr, $value)
    {
        $key = ":_v".QueryStringParam::$_counter;
        $queryString = "$field  $opr $key";
        QueryStringParam::$_counter++;
        QueryStringParam::$m_Params[$key] = $value;

        return $queryString;
    }

    /**
     * Format query value
     *
     * @param mixed $value
     * @return string the query string
     */
    public static function formatQueryValue($value)
    {
        $key = ":_v".QueryStringParam::$_counter;
        $queryString = "$key";
        QueryStringParam::$_counter++;
        QueryStringParam::$m_Params[$key] = $value;

        return $queryString;
    }

    /**
     * Set bind value
     *
     * @param array $params
     * @return void
     */
    public static function setBindValues($params)
    {
        if (!$params)
            return;
        QueryStringParam::$m_Params = $params;
        QueryStringParam::$_counter = count($params)+1;
    }

    /**
     * Get bnd values
     * 
     * @return array
     */
    public static function getBindValues()
    {
        return QueryStringParam::$m_Params;
    }

    /**
     * Get bind value string
     *
     * @return string
     */
    public static function getBindValueString()
    {
        return implode(',', QueryStringParam::$m_Params);
    }

    /**
     * Reset query string param
     *
     * @return void
     */
    public static function reset()
    {
        QueryStringParam::$_counter = 1;
        QueryStringParam::$m_Params = array();
    }
}

?>