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
 * @version   $Id: AutoSuggest.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */
//include_once ("OptionElement.php");

/**
 * AutoSuggest class  is element for AutoSuggest
 *
 * @package openbiz.bin.easy.element
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class AutoSuggest extends OptionElement
{

    public function readMetaData (&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_cssClass = isset($xmlArr["ATTRIBUTES"]["CSSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSCLASS"] : "input_text";
        $this->m_cssErrorClass = isset($xmlArr["ATTRIBUTES"]["CSSERRORCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSERRORCLASS"] : $this->m_cssClass . "_error";
        $this->m_cssFocusClass = isset($xmlArr["ATTRIBUTES"]["CSSFOCUSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSFOCUSCLASS"] : $this->m_cssClass . "_focus";
    }

    /**
     * Render element, according to the mode
     *
     * @return string HTML text
     */
    public function render ()
    {
        if (defined('JSLIB_BASE') && JSLIB_BASE == 'JQUERY') {
			$inputName = $this->m_Name;
			$style = $this->getStyle();
			$sHTML = "<input type=\"text\" id=\"$inputName\" name=\"$inputName\" value=\"$this->m_Value\"/ $style>\n";
			$sHTML .= "<script>Openbiz.AutoSuggest.init('$this->m_FormName','AutoSuggest','$inputName');</script>";
			return $sHTML;
		}
		
		BizSystem::clientProxy()->appendScripts("scriptaculous", "scriptaculous.js");
        $selFrom = $this->m_SelectFrom;
        $pos0 = strpos($selFrom, "[");
        $pos1 = strpos($selFrom, "]");
        $first_half = substr($selFrom, 0, $pos1);
        $inputName = $this->m_Name;
        $inputChoice = $this->m_Name . '_choices';
        $style = $this->getStyle();
        if ($formobj->m_Errors[$this->m_Name]) {
            $func .= "onchange=\"this.className='$this->m_cssClass'\"";
        } else {
            $func .= "onfocus=\"this.className='$this->m_cssFocusClass'\" onblur=\"this.className='$this->m_cssClass'\"";
        }
        if (strpbrk($first_half, ':')) {
            $hInputName = $this->m_Name . '_hidden';
            $inputChoice = $this->m_Name . '_hidden_choices';
            $sHTML = "<input type=\"text\" id=\"$hInputName\" name=\"$hInputName\" value=\"$this->m_Value\" $style $func/>\n";
            $sHTML .= "<div id=\"$inputChoice\" class=\"autocomplete\" style=\"display:none\"></div>\n";
            $sHTML .= "<script>Openbiz.AutoSuggest.init('$this->m_FormName','AutoSuggest','$hInputName','$inputChoice');</script>";
            $sHTML .= "<INPUT NAME=\"" . $inputName . "\" ID=\"" . $inputName . "\" VALUE=\"" . $this->m_Value . "\" type=\"hidden\" >";
        } else {
            $sHTML = "<input type=\"text\" id=\"$inputName\" name=\"$inputName\" value=\"$this->m_Value\" $style $func/>\n";
            $sHTML .= "<div id=\"$inputChoice\" class=\"autocomplete\" style=\"display:none\"></div>\n";
            $sHTML .= "<script>Openbiz.AutoSuggest.init('$this->m_FormName','AutoSuggest','$inputName','$inputChoice');</script>";
        }
        return $sHTML;
    }

    public function matchRemoteMethod ($method)
    {
        return ($method == "autosuggest");
    }
}
?>