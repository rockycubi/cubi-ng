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
 * @version   $Id: Textarea.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */


/**
 * Textarea class is element for render html Textarea
 *
 * @package openbiz.bin.easy.element
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class Textarea extends InputElement
{
	
	public function readMetaData($xmlArr){
		parent::readMetaData($xmlArr);
		$this->m_cssClass = isset($xmlArr["ATTRIBUTES"]["CSSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSCLASS"] : "input_textarea";
		$this->m_cssErrorClass = isset($xmlArr["ATTRIBUTES"]["CSSERRORCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSERRORCLASS"] : "input_textarea_error";
		$this->m_cssFocusClass = isset($xmlArr["ATTRIBUTES"]["CSSFOCUSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSFOCUSCLASS"] : "input_textarea_focus";
	}
   /**
    * Render, draw the element according to the mode
    *
    * @return string HTML text
    */
    public function render()
    {
        if($this->m_Value!=null){
    		$value = $this->m_Value;
    	}else{
    		$value = $this->getText();
    	} 
    	
    	if($value==""){
    		$value = $this->getDefaultValue();
    	}
        $disabledStr = ($this->getEnabled() == "N") ? "READONLY=\"true\"" : "";
        $style = $this->getStyle();
        $func = $this->getFunction();
        
        $formobj = $this->GetFormObj();
		if (CLIENT_DEVICE != 'mobile') { 
			if($formobj->m_Errors[$this->m_Name]){
				$func .= "onchange=\"this.className='$this->m_cssClass'\"";
			}else{
				$func .= "onfocus=\"this.className='$this->m_cssFocusClass'\" onblur=\"this.className='$this->m_cssClass'\"";
			}        
        }

        $sHTML = "<TEXTAREA NAME=\"" . $this->m_Name . "\" ID=\"" . $this->m_Name ."\" $this->m_ModelText $this->m_HintText $disabledStr $this->m_HTMLAttr $style $func />".$value."</TEXTAREA>"; 
        return $sHTML;
    }

    
}

?>
