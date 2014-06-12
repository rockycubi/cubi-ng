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
 * @version   $Id: EditCombobox.php 2871 2010-12-16 08:21:22Z rockys $
 */

//include_once("OptionElement.php");

/**
 * EditCombobox class is element for EditCombobox
 *
 * @package openbiz.bin.easy.element
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class EditCombobox extends OptionElement
{
    public $m_BlankOption;
    protected $m_WidthInput = "128px";
    protected $m_Onchange = "";

    /**
     * Read array meta data, and store to meta object
     *
     * @param array $xmlArr
     * @return void
     */
    protected function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_BlankOption=isset($xmlArr["ATTRIBUTES"]["BLANKOPTION"]) ? $xmlArr["ATTRIBUTES"]["BLANKOPTION"] : null;
    }

    /**
     * Get style of element
     *
     * @return string style of Element
     */
    protected function getStyle()
    {
        $htmlClass = $this->m_cssClass ? "class='".$this->m_cssClass."' " : "class='editcombobox'";
        /* 
        $width = $this->m_Width ? $this->m_Width : 146;
        $this->m_WidthInput = ($width-18).'px';
        $this->m_Width = $width.'px';
        $style = "position: absolute; width: $this->m_Width; z-index: 1; clip: rect(auto, auto, auto, $this->m_WidthInput);";
		*/
        if ($this->m_Style)
            $style .= $this->m_Style;
        if (!isset($style) && !$htmlClass)
            return null;
        if (isset($style))
        {
            $formObj = $this->getFormObj();
            $style = Expression::evaluateExpression($style, $formObj);
            $style = "style='$style'";
        }
        if ($htmlClass)
            $style = $htmlClass." ".$style;
        return $style;
    }

    /**
     * Render element, according to the mode
     *
     * @return string HTML text
     */
    public function render()
    {
        $fromList = array();
        $this->getFromList($fromList);
        $valueArr = explode(',', $this->m_Value);
        $disabledStr = ($this->getEnabled() == "N") ? "disabled=\"true\"" : "";
        $style = $this->getStyle();
        $func = $this->getFunction();
        $selName = $this->m_Name . "_sel";
        //$onchange = "onchange=\"$('$inputName').value=this.value;".$this->m_Onchange."\"";
        $onChange = "onchange=\"$('$this->m_Name').value=this.value; $('$this->m_Name').triggerEvent('change');\" $func";

        $sHTML = "<div $style>\n";
        $sHTML .= "<select name=\"" . $selName . "\" id=\"" . $selName ."\" $disabledStr $this->m_HTMLAttr $onChange>\n";

        if ($this->m_BlankOption) // ADD a blank option

        {
            $entry = explode(",",$this->m_BlankOption);
            $text = $entry[0];
            $value = ($entry[1]!="") ? $entry[1] : null;
            $entryList = array(array("val" => $value, "txt" => $text ));
            $fromList = array_merge($entryList, $fromList);
        }

        foreach ($fromList as $opt)
        {
            $test = array_search($opt['val'], $valueArr);
            if ($test === false)
            {
                $selectedStr = '';
            }
            else
            {
                $selectedStr = "selected";
                $selVal = $opt['val'];
            }
            $sHTML .= "<option value=\"" . $opt['val'] . "\" $selectedStr>" . $opt['txt'] . "</option>\n";
        }

        if (!$selVal)
            $selVal = $this->m_Value?$this->m_Value:$this->getDefaultValue();

        $sHTML .= "</select>\n";
        $sHTML .= "<div><input id=\"$this->m_Name\" name=\"$this->m_Name\" type=\"text\" value=\"$selVal\" $func/></div>\n";
        $sHTML .= "</div>\n";

        return $sHTML;
    }
}

?>