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
 * @version   $Id: InputText.php 3313 2011-02-24 04:32:17Z jixian2003 $
 */

//include_once("InputElement.php");

/**
 * InputText class is element for input text
 *
 * @package openbiz.bin.easy.element
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class IDCardReader extends InputElement
{
	protected function readMetaData(&$xmlArr){
		parent::readMetaData($xmlArr);
		$this->m_cssClass = isset($xmlArr["ATTRIBUTES"]["CSSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSCLASS"] : "input_cardreader";
		$this->m_cssErrorClass = isset($xmlArr["ATTRIBUTES"]["CSSERRORCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSERRORCLASS"] : $this->m_cssClass."_error";
		$this->m_cssFocusClass = isset($xmlArr["ATTRIBUTES"]["CSSFOCUSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSFOCUSCLASS"] : $this->m_cssClass."_focus";
	}
    /**
     * Render, draw the control according to the mode
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
    	if($formobj->m_Errors[$this->m_Name]){
			$func .= "onchange=\"this.className='$this->m_cssClass'\"";
		}else{
			$func .= "onfocus=\"this.className='$this->m_cssFocusClass'\" onblur=\"this.className='$this->m_cssClass'\"";
		}        
        
        //$sHTML = "<INPUT ReadOnly=\"Enabled\" NAME=\"" . $this->m_Name . "\" ID=\"" . $this->m_Name ."\" VALUE=\"" . $value . "\" $disabledStr $this->m_HTMLAttr $style $func />";
        //$sHTML .= "<br/><span ID=\"" . $this->m_Name ."_status\" >Standing By</span>";
        
        $sHTML = " <div id=\"" . $this->m_Name . "_reader\" $disabledStr $this->m_HTMLAttr $style $func >
        				<span class=\"cardcode\" ID=\"" . $this->m_Name ."_code\" >$value</span>
        				<div style=\"display:none;\" ><input ReadOnly=\"Enabled\" type=\"hidden\" NAME=\"" . $this->m_Name . "\" ID=\"" . $this->m_Name ."\" VALUE=\"\" /></div>
        			</div>"; 
        
		$elementName = $this->m_Name;
        $sHTML .= "<script>Openbiz.IDCardReader.init('$elementName');\n</script>";
        return $sHTML;
    }

}

?>
