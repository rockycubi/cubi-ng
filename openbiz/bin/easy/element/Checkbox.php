<?PHP
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.easy.element
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: Checkbox.php 3780 2011-04-18 18:26:11Z jixian2003 $
 */

//include_once("OptionElement.php");

/**
 * Checkbox class is element for Checkbox
 *
 * @package openbiz.bin.easy.element
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class Checkbox extends OptionElement
{
	protected $m_DefaultChecked ; 
	
	protected function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_DefaultChecked = isset($xmlArr["ATTRIBUTES"]["DEFAULTCHECKED"]) ? $xmlArr["ATTRIBUTES"]["DEFAULTCHECKED"] : "N";
    }
    public function getDefaultChecked()
    {
    	$formObj = $this->getFormObj();    	
    	return Expression::evaluateExpression($this->m_DefaultChecked, $formObj);
    }
    /**
     * Get value of element
     *
     * @return mixed
     */
    public function getValue()
    {
       
    	if(strtoupper($this->getDefaultChecked())=='Y' && !isset($_GET['F']) )
    	{
    		$this->m_Value = $this->getSelectFrom();
    		return $this->m_Value;
    	}
    	
        if($this->m_Value!=null)
        {
            return $this->m_Value;
        }
        else
        {
            return $this->m_DefaultValue;
        }
    }
    

    /**
     * Render element, according to the mode
     *
     * @return string HTML text
     */
    public function render()
    {
        $boolValue = $this->getSelectFrom();
        $disabledStr = ($this->getEnabled() == "N") ? "DISABLED=\"true\"" : "";
        
        if(strtoupper($this->getDefaultChecked())=='Y' && !isset($_GET['F']))
    	{		
    		$checkedStr = "CHECKED";
    	}else{    		
        	$checkedStr = ($boolValue == $this->getValue()) ? "CHECKED" : "";
    	}        
        $defaultValue = $this->m_DefaultValue;
        $style = $this->getStyle();
        $text = $this->getText();
        $func = $this->getFunction();
        $sHTML = '';
        $fromList = array();
        $this->getFromList($fromList);
        
        if (count($fromList) > 1)
        {
            $valueArr = explode(',', $this->getValue());

            foreach ($fromList as $opt)
            {
                $test = array_search($opt['val'], $valueArr);
                if ($test === false)
                {
                    $checkedStr = '';
                }
                else
                {
                    $checkedStr = "CHECKED";
                }
                $sHTML .= "<INPUT TYPE=CHECKBOX NAME='".$this->m_Name."[]' ID=\"" . $this->m_Name ."\" DefaultValue=\"$defaultValue\" VALUE=\"" . $opt['val'] . "\" $checkedStr $disabledStr $this->m_HTMLAttr $func /> " . $opt['txt'] . "";
            }
        }
        else
        {
            $sHTML = "<INPUT TYPE=\"CHECKBOX\" NAME=\"" . $this->m_Name . "\" ID=\"" . $this->m_Name ."\" DefaultValue=\"$DefaultValue\" VALUE='$boolValue' $checkedStr $disabledStr $this->m_HTMLAttr $style $func /> ".$text."";
        }

        return $sHTML;
    }
}

?>
