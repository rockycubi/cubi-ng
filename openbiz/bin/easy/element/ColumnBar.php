<?php 
//include_once("ColumnText.php");
class ColumnBar extends ColumnText
{
    public $m_Percent;
    public $m_MaxValue;
    public $m_DisplayUnit;
    public $m_Color;

    protected function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_Percent = isset($xmlArr["ATTRIBUTES"]["PERCENT"]) ? $xmlArr["ATTRIBUTES"]["PERCENT"] : "N";
        $this->m_MaxValue = isset($xmlArr["ATTRIBUTES"]["MAXVALUE"]) ? $xmlArr["ATTRIBUTES"]["MAXVALUE"] : "1";
        $this->m_DisplayUnit = isset($xmlArr["ATTRIBUTES"]["DISPLAYUNIT"]) ? $xmlArr["ATTRIBUTES"]["DISPLAYUNIT"] : null;
        $this->m_Color = isset($xmlArr["ATTRIBUTES"]["COLOR"]) ? $xmlArr["ATTRIBUTES"]["COLOR"] : null;        
        $this->m_cssClass = isset($xmlArr["ATTRIBUTES"]["CSSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSCLASS"] : "column_bar";
        $this->m_Height = isset($xmlArr["ATTRIBUTES"]["HEIGHT"]) ? $xmlArr["ATTRIBUTES"]["HEIGHT"] : "14";
    }

    public function render(){    	
    	$value =  $this->m_Value;
    	if($this->m_Color)
    	{
    		$formObj = $this->getFormObj();
    		$color = Expression::evaluateExpression($this->m_Color, $formObj);    		
    		if(!$color){
    			$color = '33b5fb';
    		}
    		$bgcolor_str = "background-color: #".$color.";";    		    		
    	}else{
    		$bgcolor_str = "background-color: #33b5fb;";
    	}
    	
    	if($this->m_DisplayFormat)
        {
        	$value = sprintf($this->m_DisplayFormat,$value);
        }
    	if($this->m_Percent=='Y')
        {        	
        	$value = sprintf("%.2f",$value*100).'%';        
        }
        $style = $this->getStyle();
        $id = $this->m_Name;
        $func = $this->getFunction();
        $height = $this->m_Height;
        $width = $this->m_Width;        
        $max_value = Expression::evaluateExpression($this->m_MaxValue, $this->getFormObj());
       
        if($max_value)
        {        	        
        	$width_rate = ($value/$max_value);
        }else{
        	$width_rate = 0;
        }
        if($width_rate>1){
        	$width_rate=1;
        }
        $width_bar = (int)($width * $width_rate);
        if($width>0){
        	$width-=2;
        }
		if(!preg_match("/MSIE 6/si",$_SERVER['HTTP_USER_AGENT'])){
			$bar_overlay="<span class=\"bar_data_bg\" style=\"".$bgcolor_str."height:".$height."px;width:".$width_bar."px;\"></span>";
			$bar = "<span class=\"bar_data\" style=\"".$bgcolor_str."height:".$height."px;width:".$width_bar."px;\"></span>";
		}else{
			$bar = "<span class=\"bar_data\" style=\"".$bgcolor_str."height:".$height."px;width:".$width_bar."px;opacity: 0.4;filter: alpha(opacity=40);\"></span>";
		}
		$value = $this->m_Text ? $this->getText() : $this->m_Value;
        $sHTML = "
    	<span id=\"$id\" $func $style >
    		
    		<span class=\"bar_bg\" style=\"height:".$height."px;width:".$width."px;\">
    			
    		$bar_overlay
    		$bar	    		    			
    		</span>
    		<span class=\"value\">$value".$this->m_DisplayUnit."</span>
    	</span>
    	";
    	return $sHTML;
    }
   protected function getStyle()
    {        
		$formobj = $this->getFormObj();    	
        $htmlClass = Expression::evaluateExpression($this->m_cssClass, $formobj);
        $htmlClass = "CLASS='$htmlClass'";
        if(!$htmlClass){
        	$htmlClass = null;
        }
        $style ='';        
        if ($this->m_Style)
            $style .= $this->m_Style;
        if (!isset($style) && !$htmlClass)
            return null;
        if (isset($style))
        {
            
            $style = Expression::evaluateExpression($style, $formobj);
            $style = "STYLE='$style'";
        }
        if($formobj->m_Errors[$this->m_Name])
        {
      	    $htmlClass = "CLASS='".$this->m_cssErrorClass."'";
        }
        if ($htmlClass)
            $style = $htmlClass." ".$style;
        return $style;
    }    
}
?>