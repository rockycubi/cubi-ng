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
 * @version   $Id: InputDate.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

//include_once("InputElement.php");

/**
 * InputDate class is element for input date with date picker
 *
 * @package openbiz.bin.easy.element
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class InputDateRangePicker extends InputText {    


	public function getSearchRule(){
		$value = BizSystem::clientProxy()->getFormInputs($this->m_Name);
		$field = $this->m_FieldName;
		
		$dates = explode("-", $value);
		$date_start = str_replace("/","-",trim($dates[0]))." 00:00:00";
		
		if(count($dates)==2){
			$date_end = str_replace("/","-",trim($dates[1]))." 23:59:59";
		}else{						
			$date_end = str_replace("/","-",trim($dates[0]))." 23:59:59";
		}
		$searchRule = "([$field] >'$date_start' AND [$field]<'$date_end')";
		return $searchRule;
	}
    /**
     * Render / draw the element according to the mode
     *
     * @return string HTML text
     */
    public function render() {      
    	$this->m_cssClass=null;
    	$this->m_cssErrorClass = null;
    	$this->m_cssHoverClass = null;   
    	
   		if($this->m_Value!=null){
    		$value = $this->m_Value;
    	}else{
    		$value = $this->getText();
    	} 
    	
    	if($value==""){
    		$value = $this->getDefaultValue();
    	}
        $events = $this->getEvents();           
		    	
        $event_onchange = $events['onchange'];
        
        $sHTML = "<div class=\"input_daterangepicker\">
        <div class=\"ui-daterangepicker-arrows\">
        
        <a href=\"#\" class=\"ui-daterangepicker-prev\" title=\"Prev\"><span class=\"ui-daterangepicker-prev\"></span></a>
        <a href=\"#\" class=\"ui-daterangepicker-next\" title=\"Next\"><span class=\"ui-daterangepicker-next\"></span></a>
        
        <a class=\"ui-daterangepicker-inputbar\">
        <INPUT NAME=\"" . $this->m_Name . "\" ID=\"" . $this->m_Name ."\" VALUE=\"" . $value . "\" class=\"ui-rangepicker-input ui-widget-content\" style=\"border:none\"  />
        </a>
        </div>
        ";
        
        $sHTML .= "
        </div>
        <script>
        \$j(document).ready(function(){
	        \$j('#".$this->m_Name."').daterangepicker(
	        { arrows:true,
	          onChange:function(){
		    		$event_onchange
		    	}
		    });
	    });
        </script>
        
        ";
        return $sHTML;
    }

}

?>