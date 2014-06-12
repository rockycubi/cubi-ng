<?php
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.easy
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: SearchHelper.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

/**
 * Convert the user input on a given fieldcontrol in query mode to search rule
 *
 * @param string $fieldName - fieldcontrol name
 * @param string $inputVal - use input text
 * @param EasyForm $formObj
 * @return string - searchRule
 */
function inputValToRule($fieldName, $inputVal, $formObj)
{
    // todo: should check single quote for nonoperators clauses
    // find locations for all sql key words
    // search for starting ' and closing ' pair, check if sql key word in the pair

    $val = strtoupper(trim($inputVal));
    // check " AND ", " OR "
    if (($pos=strpos($val, " AND "))!==false)
    {
        $inputArr = explode(" AND ", $val);
        $retStr = null;
        foreach($inputArr as $v)
            $retStr .= ($retStr) ? " AND ".inputValToRule($fieldName, $v, $formObj) : inputValToRule($fieldName, $v, $formObj);
        return $retStr;
    }
    else if (($pos=strpos($val, " OR "))!==false)
    {
        $inputArr = explode(" OR ", $val);
        $retStr = null;
        foreach($inputArr as $v)
            $retStr .= ($retStr) ? " OR ".inputValToRule($fieldName, $v, $formObj) : inputValToRule($fieldName, $v, $formObj);
        return "(".$retStr.")";
    }

    // check >=, >, <=, <, =
    if (($pos=strpos($val, "<>"))!==false || ($pos=strpos($val, "!="))!==false)
    {
        $opr = "<>";
        $oprlen = 2;
    }
    else if (($pos=strpos($val, ">="))!==false)
    {
        $opr = ">=";
        $oprlen = 2;
    }
    else if (($pos=strpos($val, ">"))!==false)
    {
        $opr = ">";
        $oprlen = 1;
    }
    else if (($pos=strpos($val, "<="))!==false)
    {
        $opr = "<=";
        $oprlen = 2;
    }
    else if (($pos=strpos($val, "<"))!==false)
    {
        $opr = "<";
        $oprlen = 1;
    }
    else if (($pos=strpos($val, "="))!==false)
    {
        $opr = "=";
        $oprlen = 1;
    }
    if ($opr)
    {
        $val = trim(substr($val, $pos+$oprlen));
    }

    if (strpos($val, "*") !== false)
    {
        $opr = "LIKE";
        $val = str_replace("*", "%", $val);
    }
    //if (strpos($val, "'") !== false) {   // not needed since addslashes() is called before
    //   $val = str_replace("'", "\\'", $val);
    //}
    if (!$opr)
        $opr = "=";

    // unformat value to real value data
    if($formObj->getDataObj()){
    	$bizField = $formObj->getDataObj()->getField($fieldName);
    	$realValue = BizSystem::typeManager()->formattedStringToValue($bizField->m_Type, $bizField->m_Format, $val);
    }else{
    	$realValue = $val;
    }
    // set the query param
    $queryString = QueryStringParam::formatQueryString("[$fieldName]", $opr, $realValue);
    return $queryString;

    //return "[" . $field . "] " . $opr . " '" . $realVal . "'";
}

?>