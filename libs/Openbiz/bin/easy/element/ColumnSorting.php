<?php 
//include_once("ColumnText.php");

class ColumnSorting extends ColumnText
{
	public function render(){
		$func_up = $this->getBtnFunction('fld_sortorder_up');
		$func_down = $this->getBtnFunction('fld_sortorder_down');
		$formobj = $this->getFormObj();		
        
        
		//$this->m_EventHandlers = null;
		$value = $this->m_Text ? $this->getText() : $this->m_Value;
        
        if ($value === null || $value ==="")
            return "";

        $style = $this->getStyle();
        $id = $this->m_Name;

        if ($this->m_Translatable == 'Y')
            $value = $this->translateString($value);
        
        if((int)$this->m_MaxLength>0){
	        if(function_exists('mb_strlen') && function_exists('mb_substr')){
	        	if(mb_strlen($value,'UTF8') > (int)$this->m_MaxLength){
	        		$value = mb_substr($value,0,(int)$this->m_MaxLength,'UTF8').'...';
	        	}        	
	        }else{
	        	if(strlen($value) > (int)$this->m_MaxLength){
	        		$value = substr($value,0,(int)$this->m_MaxLength).'...';
	        	}         	
	        }
        }
        
        if ($value!==null)
        {
        	if($this->m_DisplayFormat)
        	{
        		$value = sprintf($this->m_DisplayFormat,$value);
        	}
        	if($this->m_Percent=='Y')
        	{
        		$value = sprintf("%.2f",$value*100).'%';
        	}
        	
            if ($this->m_Link)
            {
                $link = $this->getLink();
                $target = $this->getTarget();
                //$sHTML = "<a href=\"$link\" onclick=\"SetOnLoadNewView();\" $style>" . $val . "</a>";
                $sHTML = "<a id=\"$id\" href=\"$link\" $target $func $style>" . $value . "</a>";
            }
            else
            {
                $sHTML = "<span style=\"width:auto;height:auto;line-height:16px;\" $func>" . $value . "</span>";
            }
        }
        
		$sHTML = "<a $func_up  class=\"arrow_up\" href=\"javascript:;\"><img src=\"".Resource::getImageUrl()."/spacer.gif"."\" style=\"width:12px;height:12px;\" /></a> ".
				$sHTML.
				" <a $func_down  class=\"arrow_down\" href=\"javascript:;\"><img src=\"".Resource::getImageUrl()."/spacer.gif"."\" style=\"width:12px;height:12px;\" /></a>";
		
		return $sHTML;
	}
	
	public function getBtnFunction($event_name){
        $name = $this->m_Name;
        // loop through the event handlers
        $func = "";

        if ($this->m_EventHandlers == null)
            return null;
        $formobj = $this->getFormObj();
        
        $eventHandler = $this->m_EventHandlers->get($event_name);
                
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
				$func .= " $event=\"$function_str\"";
			}else{
				$func .= " $event=\"$function\"";
			}
		}
        return $func;		
	}
}
?>