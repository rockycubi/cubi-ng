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
 * @version   $Id: ColorPicker.php 2912 2010-12-17 18:30:31Z jixian2003 $
 */

//include_once("InputElement.php");
class ColorPicker extends InputText {
	public $m_Config;
	public $m_Mode;
    /**
     * Read array meta data, and store to meta object
     *
     * @param array $xmlArr
     * @return void
     */
    protected function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_cssClass = isset($xmlArr["ATTRIBUTES"]["CSSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSCLASS"] : "input_text_s";
		$this->m_cssErrorClass = isset($xmlArr["ATTRIBUTES"]["CSSERRORCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSERRORCLASS"] : $this->m_cssClass."_error";
		$this->m_cssFocusClass = isset($xmlArr["ATTRIBUTES"]["CSSFOCUSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSFOCUSCLASS"] : $this->m_cssClass."_focus";
        $this->m_Mode = isset($xmlArr["ATTRIBUTES"]["MODE"]) ? $xmlArr["ATTRIBUTES"]["MODE"] : null;
        $this->m_Config = isset($xmlArr["ATTRIBUTES"]["CONFIG"]) ? $xmlArr["ATTRIBUTES"]["CONFIG"] : null;
    }
    
	public function render(){
		BizSystem::clientProxy()->includeColorPickerScripts();
		if($this->m_Value!=null){
    		$value = $this->m_Value;
    	}else{
    		$value = $this->getText();
    	} 
    	
        $disabledStr = ($this->getEnabled() == "N") ? "READONLY=\"true\"" : "";
        $style = $this->getStyle();
        $func = $this->getFunction();
        $func_org = $func;
        $formobj = $this->GetFormObj();
    	if($formobj->m_Errors[$this->m_Name]){
			$func .= "onchange=\"this.className='$this->m_cssClass'\"";
		}else{
			$func .= "onfocus=\"this.className='$this->m_cssFocusClass'\" onblur=\"this.className='$this->m_cssClass'\"";
		}        
        $elementName = $this->m_Name;   
        $elementTrigger=array();
        
		if($value){
			$default_color = "color: '#$value',";
		}else{
			$default_color = "";
			$value=$this->getDefaultValue() ? $this->getDefaultValue() : "";
		}        
		switch(strtolower($this->m_Mode)){
			case "viewonly":				
				$sHTML .= "<span id=\"colorpreview_$elementName\" $func_org class=\"colorpicker_preview\" style=\"background-color:#$value;width:98px;\" ></span>";
				$elementTrigger = array();
				break;			
			case "widget":
				$config = " 
							onShow: function (colpkr) {
								if(\$j(colpkr).css('display')=='none'){
									\$j(colpkr).fadeIn(300);
								}
								return false;
							},
							onHide: function (colpkr) {
								\$j(colpkr).fadeOut(300);
								return false;
							},													
							onSubmit: function(hsb, hex, rgb, el) {
								$('$this->m_Name').value=hex;
								\$j('#colorpreview_$this->m_Name').css('backgroundColor', '#' + hex);
							},
							onChange: function (hsb, hex, rgb) {
								$('$this->m_Name').value=hex;
								\$j('#colorpreview_$this->m_Name').css('backgroundColor', '#' + hex);
							}
							";
				$sHTML .= "<span id=\"colorpreview_$elementName\" class=\"colorpicker_preview\" style=\"background-color:#$value;\" $func ></span>";
				$sHTML .= "<INPUT NAME=\"" . $this->m_Name . "\" ID=\"" . $this->m_Name ."\" VALUE=\"" . $value . "\" type=\"hidden\" />";
				$elementTrigger = array("colorpreview_$elementName");
				break;
				
			case "flat":
				$config = "flat: true,
							onSubmit: function(hsb, hex, rgb, el) {
								$('$this->m_Name').value=hex;
								\$j('#colorpreview_$this->m_Name').css('backgroundColor', '#' + hex);								
							},
							onChange: function (hsb, hex, rgb) {
								$('$this->m_Name').value=hex;
								\$j('#colorpreview_$this->m_Name').css('backgroundColor', '#' + hex);
								
							}
				";
				$sHTML .= "<span id=\"colorpreview_$elementName\" class=\"colorpicker_preview\" style=\"background-color:#$value;\" ></span>";						
				$sHTML .= "<INPUT NAME=\"" . $this->m_Name . "\" ID=\"" . $this->m_Name ."\" VALUE=\"" . $value . "\" $disabledStr $this->m_HTMLAttr $style $func />";
				$sHTML .= "<div id=\"colorpicker_$elementName\" style=\"float:left\"></div>";
				$elementTrigger = array("colorpicker_".$elementName);
				break;
				
			default:
				$config = " 
							onShow: function (colpkr) {
								if(\$j(colpkr).css('display')=='none'){
									\$j(colpkr).fadeIn(300);
								}
								return false;
							},
							onHide: function (colpkr) {
								\$j(colpkr).fadeOut(300);
								return false;
							},													
							onSubmit: function(hsb, hex, rgb, el) {
								$('$this->m_Name').value=hex;
								\$j('#colorpreview_$this->m_Name').css('backgroundColor', '#' + hex);
							},
							onChange: function (hsb, hex, rgb) {
								$('$this->m_Name').value=hex;
								\$j('#colorpreview_$this->m_Name').css('backgroundColor', '#' + hex);
							}
							";
				$sHTML .= "<span id=\"colorpreview_$elementName\" class=\"colorpicker_preview\" style=\"background-color:#$value;\" ></span>";
				$sHTML .= "<INPUT NAME=\"" . $this->m_Name . "\" ID=\"" . $this->m_Name ."\" VALUE=\"" . $value . "\" $disabledStr $this->m_HTMLAttr $style $func />";								
				$elementTrigger = array($elementName,"colorpreview_$elementName");
				break;
		}
		
		if($this->m_Config){
			$config .= ",".$this->m_Config;	
		}
		$config = "{".$default_color.$config."}";
		foreach($elementTrigger as $trigger){
			$sHTML .= "<script>\$j('#$trigger').ColorPicker($config);</script>\n";
		}
        
        return $sHTML;
	}
	
	public function getFunctionByEvent($event_name){
        $name = $this->m_Name;
        // loop through the event handlers
        $func = "";

        if ($this->m_EventHandlers == null)
            return null;
        $formobj = $this->getFormObj();
        
        foreach($this->m_EventHandlers as $eventHandler){
        	if($eventHandler->m_Event==$event_name){
        		break;
        	}
        }
                
        $ehName = $eventHandler->m_Name;
        $event = $eventHandler->m_Event;
        $type = $eventHandler->m_FunctionType;
        if (!$event) return;
        if($events[$event]!=""){
           $events[$event]=array_merge(array($events[$event]),array($eventHandler->getFormedFunction()));
        }else{
           $events[$event]=$eventHandler->getFormedFunction();
        }

		foreach ($events as $event=>$function){
			if(is_array($function)){
				foreach($function as $f){
					$function_str.=$f.";";
				}
				$func .= $function_str;
			}else{
				$func .= $function;
			}
		}
        return $func;		
	}    
}
?>