<?php
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.service
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: validateService.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

include_once (OPENBIZ_HOME."/messages/validateService.msg");

/**
 * validateService class is the plug-in service of handling system validation.
 * The service depends on the Zend Framework validator component.
 * REGIX service provided by preg_match ie. perl style
 *
 * @package openbiz.bin.service
 * @author jim jenkins jixian w.
 * @copyright Copyright (c) 2003 - 2009
 * @access public
 */
class validateService
{
    protected $m_ErrorMessage = null;
    protected $m_FieldNameMask = "%%FIELDNAME%%";

    /**
     * Initialize reportService with xml array metadata
     *
     * @param array $xmlArr
     * @return void
     */
    function __construct(&$xmlArr)
    {
        $this->readMetadata($xmlArr);
    }

    /**
     * Read array meta data, and store to meta object
     *
     * @param array $xmlArr
     * @return void
     */
    protected function readMetadata(&$xmlArr)
    {
    }

    /**
     * Function to check if a value is shorter than the $max length
     *
     * @param string $value
     * @param integer $max
     * @return boolean Valid?
     */
    public function shorterThan($value, $max)
    {
        $this->m_ErrorMessage = null;
        $result = false;
        if(function_exists("mb_strlen"))
        {
            if (mb_strlen($value,'UTF-8') < $max)
            {
                $result = true;
            }
        }else
        {
            if (strlen($value) < $max)
            {
                $result = true;
            }
        }
        if (!$result)
        {
            $this->m_ErrorMessage= BizSystem::getMessage("VALIDATESVC_SHORTER_THAN",array($this->m_FieldNameMask,$max));
        }
        return $result;
    }

    /**
     * Function to check if a value is between a $min and $max length
     *
     * @param string $value
     * @param integer $max
     * @param integer $min
     * @return boolean Valid?
     */
    public function betweenLength($value, $min, $max)
    {
        if((int)$min > (int)$max)
        {
            $tmp = $min;
            $min = $max;
            $max = $tmp;
        }
        $this->m_ErrorMessage = null;
        $result = false;
        if(function_exists("mb_strlen"))
        {
            if (mb_strlen($value,'UTF-8') <= $max && mb_strlen($value,'UTF-8') >= $min)
            {
                $result = true;
            }
        }
        else
        {
            if (strlen($value) <= $max && strlen($value) >= $min)
            {
                $result = true;
            }
        }
        if (!$result)
        {
            $this->m_ErrorMessage= BizSystem::getMessage("VALIDATESVC_BETWEEN_LENGTH",array($this->m_FieldNameMask,$min,$max));
        }
        return $result;
    }

    /**
     * Function to check if a value is longer than the $min length
     *
     * @param string $value
     * @param integer $min
     * @return boolean Valid?
     */
    public function longerThan($value, $min)
    {
        $this->m_ErrorMessage = null;
        $result = false;
        if(function_exists("mb_strlen"))
        {
            if (mb_strlen($value,'UTF-8') > $min)
            {
                $result = true;
            }
        }
        else
        {
            if (strlen($value) > $min)
            {
                $result = true;
            }
        }
        if (!$result)
        {
            $this->m_ErrorMessage= BizSystem::getMessage("VALIDATESVC_LONGER_THAN",array($this->m_FieldNameMask,$min));
        }
        return $result;
    }



    /**
     * Built-in Zend less than check.  Returns true if and only if $value is less than the minimum boundary.
     *
     * @param integer $value
     * @param integer $max
     * @return boolean Valid?
     */
    public function lessThan($value, $max)
    {
        $this->m_ErrorMessage = null;
        require_once 'Zend/Validate/LessThan.php';
        $validator = new Zend_Validate_LessThan($max);
        $result = $validator->isValid($value);
        if(!$result)
        {
            $this->m_ErrorMessage= BizSystem::getMessage("VALIDATESVC_LESS_THAN",array($this->m_FieldNameMask,$max));
        }
        return $result;
    }

    /**
     * Strong Password checks if the string is "strong", i.e. the password must be at least 8 characters
     * and must contain at least one lower case letter, one upper case letter and one digit
     *
     * @param mixed $value
     * @return boolean Valid?
     */
    public function strongPassword($value)
    {
        $this->m_ErrorMessage = null;
        require_once 'Zend/Validate/Regex.php';
        $validator = new Zend_Validate_Regex("/^.*(?=.{8,})(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).*$/");
        $result = $validator->isValid($value);
        if(!$result)
        {
            $this->m_ErrorMessage= BizSystem::getMessage("VALIDATESVC_PASSWORD_NOT_STRONG",array($this->m_FieldNameMask));
        }
        return $result;
    }



    /**
     * Built-in Zend greater than check.  Returns true if and only if $value is greater than the minimum boundary.
     *
     * @param integer $value
     * @param integer $min
     * @return boolean Valid?
     */
    public function greaterThan($value, $min)
    {
        $this->m_ErrorMessage = null;
        require_once 'Zend/Validate/GreaterThan.php';
        $validator = new Zend_Validate_GreaterThan($min);
        $result = $validator->isValid($value);
        if(!$result)
        {
            $this->m_ErrorMessage= BizSystem::getMessage("VALIDATESVC_GREATER_THAN",array($this->m_FieldNameMask, $min));
        }
        return $result;
    }

    /**
     * Built-in Zend between check.  Returns true if and only if $value is between the minimum and maximum boundary values.
     *
     * @param integer $value
     * @param integer $min
     * @param mixed $max
     * @param boolean $inclusive
     *
     * @return boolean Valid?
     */
    public function between($value, $min, $max, $inclusive=true)
    {
        $this->m_ErrorMessage = null;
        require_once 'Zend/Validate/Between.php';
        $validator = new Zend_Validate_Between($min, $max, $inclusive);
        $result = $validator->isValid($value);
        if(!$result)
        {
            $this->m_ErrorMessage= BizSystem::getMessage("VALIDATESVC_BETWEEN",array($this->m_FieldNameMask ,$min, $max));
        }
        return $result;
    }


    /**
     * Built-in Zend email check
     *
     * @param string $email
     * @return boolean Valid?
     */
    public function email($email)
    {
        $this->m_ErrorMessage = null;
        require_once 'Zend/Validate/EmailAddress.php';

        /*
         * it's a newbelogic, too complicated
        $validator = new Zend_Validate_EmailAddress();
        $result = $validator->isValid($email);
        */ 
		if(preg_match("/^[A-Z0-9_-][A-Z0-9._-]*@([A-Z0-9][A-Z0-9-]*\.)+[A-Z\.]{2,6}$/i",$email)){
			$result = true;
		}else{
			$result = false;
		};
        if(!$result)
        {
            $this->m_ErrorMessage= BizSystem::getMessage("VALIDATESVC_EMAIL_INVALID",array($this->m_FieldNameMask));
        }
        return $result;
    }

    /**
     * Built-in Zend date check using YYYY-MM-DD
     *
     * @param string in date format $date
     * @return boolean Valid?
     */
    public function date($date)
    {
        $this->m_ErrorMessage=null;
        require_once 'Zend/Validate/Date.php';
        $validator = new Zend_Validate_Date();
        $result = $validator->isValid($date);
        if(!$result)
        {
            $this->m_ErrorMessage = BizSystem::getMessage("VALIDATESVC_DATE_INVALID",array($this->m_FieldNameMask));
        }
        return $result;
    }
    
    /**
     * Phone check for US format ###-###-#### or (###)###-####
     *
     * @param string $phone
     * @return boolean Valid?
     */
    public function phone($phone)
    {
        $this->m_ErrorMessage = null;
        require_once 'Zend/Validate/Regex.php';
        $validator = new Zend_Validate_Regex("/^[0-9]{3}-[0-9]{3}-[0-9]{4}/");
        $result = $validator->isValid($phone);
        if(!$result)
        {
            $this->m_ErrorMessage= BizSystem::getMessage("VALIDATESVC_PHONE_INVALID",array($this->m_FieldNameMask));
        }
        return $result;
    }

    /**
     * US Zip check in ##### or #####-####
     *
     * @param string $zip
     * @return boolean Valid?
     */
    public function zip($zip)
    {
        $this->m_ErrorMessage = null;
        require_once 'Zend/Validate/Regex.php';
        $validator = new Zend_Validate_Regex("/^[0-9]{5,5}([- ]?[0-9]{4,4})?$/");
        $result = $validator->isValid($zip);
        if(!$result)
        {
            $this->m_ErrorMessage= BizSystem::getMessage("VALIDATESVC_ZIP_INVALID",array($this->m_FieldNameMask));
        }
        return $result;
    }

    /**
     * Social Security check for US format ###-###-#### or (###)###-####
     *
     * @param string $social
     * @return boolean Valid?
     */
    public function social($social)
    {
        $this->m_ErrorMessage = null;
        require_once 'Zend/Validate/Regex.php';
        $validator = new Zend_Validate_Regex("\b[0-9]{3}-[0-9]{2}-[0-9]{4}\b");
        $result = $validator->isValid($social);
        if(!$result)
        {
            $this->m_ErrorMessage= BizSystem::getMessage("VALIDATESVC_SOCIAL_INVALID",array($this->m_FieldNameMask));
        }
        return $result;
    }

    /**
     * Credit Card check for US format VISA/AMEX/DISC/MC
     *
     * @param string $credit
     * @return boolean Valid?
     */
    public function credit($credit)
    {
        $this->m_ErrorMessage = null;
        require_once 'Zend/Validate/Regex.php';
        $validator = new Zend_Validate_Regex("^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|");
        $result = $validator->isValid($credit);
        if(!$result)
        {
            $this->m_ErrorMessage= BizSystem::getMessage("VALIDATESVC_CREDIT_INVALID",array($this->m_FieldNameMask));
        }
        return $result;
    }

    /**
     * Street Address check for US format #### Memory Lane
     *
     * @param string $street
     * @return boolean Valid?
     */
    public function street($street)
    {
        $this->m_ErrorMessage = null;
        require_once 'Zend/Validate/Regex.php';
        $validator = new Zend_Validate_Regex("");
        $result = $validator->isValid($street);
        if(!$result)
        {
            $this->m_ErrorMessage= BizSystem::getMessage("VALIDATESVC_STREET_INVALID",array($this->m_FieldNameMask));
        }
        return $result;
    }

    /**
     * Get error message
     *
     * @param string $validator
     * @param string $fieldName
     * @return string
     */
    public function getErrorMessage($validator=null, $fieldName=null)
    {
        if($this->m_ErrorMessage != "")
        {
            if($fieldName != "")
            {
                $this->m_ErrorMessage = str_replace($this->m_FieldNameMask,
                        $fieldName,
                        $this->m_ErrorMessage);
            }
            return $this->m_ErrorMessage;
        }
        else
        {
            $validator = str_replace('{@validate:', '', $validator);
            $pos1 = strpos($validator, '(');
            $type = substr($validator, 0, $pos1);

            switch ($type)
            {
                case "date":
                    return BizSystem::getMessage("VALIDATESVC_DATE_INVALID",array($fieldName));
                    break;
                case "email":
                    return BizSystem::getMessage("VALIDATESVC_EMAIL_INVALID",array($fieldName));
                    break;
                case "phone":
                    return BizSystem::getMessage("VALIDATESVC_PHONE_INVALID",array($fieldName));
                    break;
                case "zip":
                    return BizSystem::getMessage("VALIDATESVC_ZIP_INVALID",array($fieldName));
                    break;
                case "social":
                    return BizSystem::getMessage("VALIDATESVC_SOCIAL_INVALID",array($fieldName));
                    break;
                case "credit":
                    return BizSystem::getMessage("VALIDATESVC_CREDIT_INVALID",array($fieldName));
                    break;
                case "street":
                    return BizSystem::getMessage("VALIDATESVC_STREET_INVALID",array($fieldName));
                    break;
                case "strongPassword":
                    return BizSystem::getMessage("VALIDATESVC_PASSWORD_NOT_STRONG",array($fieldName));
                    break;
            }
            return BizSystem::getMessage("VALIDATESVC_INVALID",array($fieldName));
        }
    }
}

?>