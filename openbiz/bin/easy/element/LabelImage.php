<?PHP


//include_once("LabelText.php");


class LabelImage extends LabelText
{

	private $m_Prefix ;

    protected function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_Prefix = isset($xmlArr["ATTRIBUTES"]["URLPREFIX"]) ? $xmlArr["ATTRIBUTES"]["URLPREFIX"] : null;
        $this->m_Prefix =  Expression::evaluateExpression($this->m_Prefix,$this);
    }
	
    /**
     * Render, draw the control according to the mode
     *
     * @return string HTML text
     */
    public function render()
    {
    	$this->m_Prefix = Expression::evaluateExpression($this->m_Prefix, $formobj);
    	$func = $this->getFunction();
    	if($this->m_Width){
    		$width_str = " width=\"".$this->m_Width."\" ";
    	}
        if($this->m_Height){
    		$height_str = " height=\"".$this->m_Height."\" ";
    	}    	
    	$value = $this->getText()?$this->getText():$this->getValue();
    	if($value){
    		
    		if ($this->m_Link)
            {
                $link = $this->getLink();
                $target = $this->getTarget();
                $sHTML = "<a href=\"$link\" $target $func $style>" . "<img src=\"".$this->m_Prefix.$value."\"  border=\"0\" $width_str $height_str />" . "</a>";
            }
            else
            {
                $sHTML = "<img border=\"0\" src=\"".$this->m_Prefix.$value."\" $func $width_str $height_str />";
            }
    		
        	
    	}
        return $sHTML;
    }

}

?>