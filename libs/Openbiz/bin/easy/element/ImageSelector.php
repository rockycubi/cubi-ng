<?PHP
//include_once("OptionElement.php");

class ImageSelector extends OptionElement
{
    public $m_BlankOption;


    protected function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_BlankOption = isset($xmlArr["ATTRIBUTES"]["BLANKOPTION"]) ? $xmlArr["ATTRIBUTES"]["BLANKOPTION"] : null;
        $this->m_cssClass = isset($xmlArr["ATTRIBUTES"]["CSSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSCLASS"] : 'image_selector';
        $this->m_cssErrorClass = isset($xmlArr["ATTRIBUTES"]["CSSERRORCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSERRORCLASS"] : $this->m_cssClass . "_error";
        $this->m_cssFocusClass = isset($xmlArr["ATTRIBUTES"]["CSSFOCUSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CSSFOCUSCLASS"] : $this->m_cssClass . "_focus";
    }

   
    public function render()
    {
        $fromList = array();
        $this->getFromList($fromList);
        
        $value = $this->getValue()!='null' ? $this->getValue() : $this->getDefaultValue();
        
        $value = $value===null?$this->getDefaultValue():$value;
        
        $valueArray = explode(',', $value);
        $disabledStr = ($this->getEnabled() == "N") ? "DISABLED=\"true\"" : "";
        $style = $this->getStyle();
        $func = $this->getFunction();
		
        $formobj = $this->GetFormObj();
        if($formobj->m_Errors[$this->m_Name]){
			$func .= "onclick=\"this.className='$this->m_cssClass'\"";
		}else{
			$func .= "onmouseover=\"this.className='$this->m_cssFocusClass'\" onmouseout=\"this.className='$this->m_cssClass'\"";
		} 
		
        $sHTML = "<input type=\"hidden\" NAME=\"" . $this->m_Name . "\" ID=\"" . $this->m_Name ."\" value=\"".$value."\" $disabledStr $this->m_HTMLAttr />";
		$sHTML .= "<ul id=\"image_list_" . $this->m_Name ."\" $style $func >";
        if ($this->m_BlankOption) // ADD a blank option
        {
            $entry = explode(",",$this->m_BlankOption);
            $text = $entry[0];
            $value = ($entry[1]!= "") ? $entry[1] : null;
            $entryList = array(array("val" => $value, "txt" => $text ));
            $fromList = array_merge($entryList, $fromList);
        }

        foreach ($fromList as $option)
        {
            $test = array_search($option['val'], $valueArray);
            if ($test === false)
            {
                $selectedStr = 'normal';
            }
            else
            {
                $selectedStr = "current";
            }
	        if($this->m_Width){
	    		$width_str = " width=\"".$this->m_Width."\" ";
	    	}
	        if($this->m_Height){
	    		$height_str = " height=\"".$this->m_Height."\" ";
	    	}          
	    	$image_url = $option['pic'];
	    	if(preg_match("/\{.*\}/si",$image_url))
	        {
	        	$formobj = $this->getFormObj();
	        	$image_url =  Expression::evaluateExpression($image_url, $formobj);
	        }else{
	        	$image_url = Resource::getImageUrl()."/".$image_url;
	        }   
            $sHTML .= "<a title=\"" . $option['txt'] . "\" 
            				href=\"javascript:;\"
            				class=\"$selectedStr\"
            				onclick =\"$('".$this->m_Name."').value='". $option['val']."';            							
            							Openbiz.ImageSelector.reset('image_list_".$this->m_Name."');
            							this.className='current';
            							\"	
            			>
            			<img
            			    $width_str $height_str
            			    src=\"".$image_url."\" 
            				title=\"" . $option['txt'] . "\" 
            				 /></a>";
            
        }
        $sHTML .= "</ul>";        

        return $sHTML;
    }
}

?>
