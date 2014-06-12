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
 * @version   $Id: InputPicker.php 3984 2011-04-28 02:54:31Z jixian2003 $
 */

//include_once("InputElement.php");

/**
 * InputPicker class is element for input picker
 *
 * @package openbiz.bin.easy.element
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class InputPicker extends InputText
{
    public $m_ValuePicker;
    public $m_PickerMap;

    /**
     * Read array meta data, and store to meta object
     *
     * @param array $xmlArr
     * @return void
     */
    public function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_ValuePicker  = isset($xmlArr["ATTRIBUTES"]["VALUEPICKER"]) ? $xmlArr["ATTRIBUTES"]["VALUEPICKER"] : null;
        $this->m_PickerMap  = isset($xmlArr["ATTRIBUTES"]["PICKERMAP"]) ? $xmlArr["ATTRIBUTES"]["PICKERMAP"] : null;
        // if no class name, add default class name. i.e. NewRecord => ObjName.NewRecord
        $this->m_ValuePicker = $this->prefixPackage($this->m_ValuePicker);
        $this->m_UpdateForm = isset($xmlArr["ATTRIBUTES"]["UPDATEFORM"]) ? $xmlArr["ATTRIBUTES"]["UPDATEFORM"] : "N";
    }

    /**
     * Render, draw the control according to the mode
     *
     * @return string HTML text
     */
    public function render()
    {
    	$this->m_DefaultValueRename="N";
    	$this->m_Enabled='N';
        $sHTML = parent::render();

        // sample picker call CallFunction('easy.f_AttendeeListChild.LoadPicker(view,form,elem)','Prop_Window');
        if ($this->m_ValuePicker != null)
        {
            $function = $this->m_FormName . ".LoadPicker($this->m_ValuePicker,$this->m_Name)";
            $sHTML .= " <input type=button onClick=\"Openbiz.CallFunction('$function');\" value=\"...\" style='width:20px;' />";
        }

        return $sHTML;
    }
	
    public function getEvents(){
    	$events = parent::getEvents();
    	$events['onclick'] .= "Openbiz.CallFunction('".$this->m_FormName . ".LoadPicker($this->m_ValuePicker,$this->m_Name)')";
        return $events;
    }
    
    public function matchRemoteMethod($method)
    {
        return ($method == "loadpicker");
    }
}

?>
