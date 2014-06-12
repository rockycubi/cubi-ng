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

//include_once("OptionElement.php");

/**
 * Textarea class is element for render html Textarea
 *
 * @package openbiz.bin.easy.element
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class Textarea extends OptionElement
{
	public $m_BlankOption;
	
	public function readMetaData(&$xmlArr){
		parent::readMetaData($xmlArr);
		$this->m_cssClass = isset($xmlArr["ATTRIBUTES"]["CSSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSCLASS"] : "input_textarea";
		$this->m_cssErrorClass = isset($xmlArr["ATTRIBUTES"]["CSSERRORCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSERRORCLASS"] : "input_textarea_error";
		$this->m_cssFocusClass = isset($xmlArr["ATTRIBUTES"]["CSSFOCUSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSFOCUSCLASS"] : "input_textarea_focus";
		$this->m_BlankOption = isset($xmlArr["ATTRIBUTES"]["BLANKOPTION"]) ? $xmlArr["ATTRIBUTES"]["BLANKOPTION"] : null;
	}
   /**
    * Render, draw the element according to the mode
    *
    * @return string HTML text
    */
    public function render()
    {

        
        $disabledStr = ($this->getEnabled() == "N") ? "DISABLED=\"true\"" : "";
        $style = $this->getStyle();
        $func = $this->getFunction(); 
    	if($formobj->m_Errors[$this->m_Name]){
			$func .= "onchange=\"this.className='$this->m_cssClass'\"";
		}else{
			$func .= "onfocus=\"this.className='$this->m_cssFocusClass'\" onblur=\"this.className='$this->m_cssClass'\"";
		}        
        $sHTML .= "<TEXTAREA NAME=\"" . $this->m_Name . "\" ID=\"" . $this->m_Name ."\" $disabledStr $this->m_HTMLAttr $style $func>".$this->m_Value."</TEXTAREA>";        
    	
        if($this->m_SelectFrom){
        	$fromList = array();
	        $this->getFromList($fromList);
	        $valueArray = explode(',', $this->m_Value);
	        $sHTML .= "<UL ID=\"" . $this->m_Name ."_suggestion\" class=\"input_textarea_suggestion\" >";
	        if ($this->m_BlankOption) // ADD a blank option
	        {
	            $entry = explode(",",$this->m_BlankOption);
	            $text = $entry[0];
	            $value = ($entry[1]!= "") ? $entry[1] : null;
	            $entryList = array("val" => $value, "txt" => $text );
	            $sHTML .= "<LI><H3>".$entryList['txt']."</H3></LI>";
	        }
	        
	        foreach ($fromList as $option)
	        {            
	            $sHTML .= "<LI><A href=\"javascript:;\" onclick=\"$('".$this->m_Name."').value+='".$option['val']."'\" >".$option['txt']."</A></LI>";        	
	        }
	        $sHTML .= "</UL>";
        }
        return $sHTML;
    }

    
}

?>
