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
 * @version   $Id: InputElement.php 3536 2011-03-28 19:13:05Z jixian2003 $
 */

//include_once("Element.php");

/**
 * InputElement class is based element for all input element
 *
 * @package openbiz.bin.easy.element
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class InputElement extends Element
{
    public $m_FieldName;
    public $m_Label;
    public $m_Description;
    public $m_DefaultValue = "";
    public $m_DefaultValueRename = "Y";
    public $m_Required = "N";
    public $m_Enabled = "Y";      // support expression
    public $m_Text;
    public $m_Hint;
	
	protected $m_ModelText;
	protected $m_HintText;

    /**
     * Read array meta data, and store to meta object
     *
     * @param array $xmlArr
     * @return void
     */
    protected function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_FieldName = isset($xmlArr["ATTRIBUTES"]["FIELDNAME"]) ? $xmlArr["ATTRIBUTES"]["FIELDNAME"] : null;
        $this->m_Label = isset($xmlArr["ATTRIBUTES"]["LABEL"]) ? $xmlArr["ATTRIBUTES"]["LABEL"] : null;
        $this->m_Description = isset($xmlArr["ATTRIBUTES"]["DESCRIPTION"]) ? $xmlArr["ATTRIBUTES"]["DESCRIPTION"] : null;
        $this->m_DefaultValue = isset($xmlArr["ATTRIBUTES"]["DEFAULTVALUE"]) ? $xmlArr["ATTRIBUTES"]["DEFAULTVALUE"] : null;
        $this->m_DefaultValueRename = isset($xmlArr["ATTRIBUTES"]["DEFAULTVALUERENAME"]) ? $xmlArr["ATTRIBUTES"]["DEFAULTVALUERENAME"] : "Y";
        $this->m_Required = isset($xmlArr["ATTRIBUTES"]["REQUIRED"]) ? $xmlArr["ATTRIBUTES"]["REQUIRED"] : null;
        $this->m_Enabled = isset($xmlArr["ATTRIBUTES"]["ENABLED"]) ? $xmlArr["ATTRIBUTES"]["ENABLED"] : null;
        $this->m_Text = isset($xmlArr["ATTRIBUTES"]["TEXT"]) ? $xmlArr["ATTRIBUTES"]["TEXT"] : null;

        $this->m_Hint = isset($xmlArr["ATTRIBUTES"]["HINT"]) ? $xmlArr["ATTRIBUTES"]["HINT"] : null;
        
        // if no class name, add default class name. i.e. NewRecord => ObjName.NewRecord
        $this->m_ValuePicker = $this->prefixPackage($this->m_ValuePicker);
		
		$this->m_HintText = $this->m_Hint ? "placeholder='".$this->m_Hint."'" : null;
		if ($this->m_FieldName) {
			$this->m_ModelText = "ng-model='dataobj.".$this->m_FieldName."'";
		}
    }

	public function setPanelName($panelName)
	{
		$this->m_PanelName = $panelName;
		if ($this->m_FieldName) {
			$prefix = $this->m_PanelName ? $this->m_PanelName : 'dataobj';
			$this->m_ModelText = "ng-model='$prefix.".$this->m_FieldName."'";
		}
	}

    /**
     * Get enable status
     *
     * @return string
     */
    protected function getEnabled()
    {
        $formObj = $this->getFormObj();
        return Expression::evaluateExpression($this->m_Enabled, $formObj);
    }
    
    protected function getRequired()
    {
        $formObj = $this->getFormObj();
        return Expression::evaluateExpression($this->m_Required, $formObj);
    }    
	
    public function getValue()
    {
    	$value=parent::getValue();
    	if($value==$this->m_Hint)
    	{
    		$this->m_Value = null;
    		return null;
    	}
    	return $value;
    }
    /**
     * Render label, just return label value
     *
     * @return string
     */
    public function renderLabel()
    {
        $sHTML = $this->translateString($this->m_Label);       
        return $sHTML;
    }

    /**
     * Render, draw the element according to the mode
     * just return element value
     *
     * @return string HTML text
     */
    public function render()
    {
        return $this->m_Value;
    }

    /**
     * Add sort-cut key scripts
     *
     * @return string
     */
    protected function addSCKeyScript()
    {
	return;
        $keyMap = $this->getSCKeyFuncMap();
        if (count($keyMap) == 0)
            return "";
        BizSystem::clientProxy()->appendScripts("shortcut", "shortcut.js");
        $str = "<script>\n";
        $formObj = $this->getFormObj();
        if (!$formObj->removeall_sck) {
            $str .= " shortcut.removeall(); \n";
            $formObj->removeall_sck = true;
        }
        foreach ($keyMap as $key => $func)
            $str .= " shortcut.remove(\"$key\"); \n";
        $str .= " shortcut.add(\"$key\",function() { $func }); \n";
        $str .= "</script>\n";
        return $str;
    }
}

?>
