<?PHP
//include_once("LabelText.php");


class LabelTextPaging extends LabelText
{
 
	public $m_CurrentCss;
	public $m_CurrentPage;
	public $m_TotalPage;
	
    protected function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_CurrentCss = isset($xmlArr["ATTRIBUTES"]["CURRENTCSSCLASS"]) ? $xmlArr["ATTRIBUTES"]["CURRENTCSSCLASS"] : null;
        $this->m_CurrentPage = isset($xmlArr["ATTRIBUTES"]["CURRENTPAGE"]) ? $xmlArr["ATTRIBUTES"]["CURRENTPAGE"]  : null;
        $this->m_TotalPage = isset($xmlArr["ATTRIBUTES"]["TOTALPAGE"]) ? $xmlArr["ATTRIBUTES"]["TOTALPAGE"]  : null;        
    }

 
    public function render()
    {
		$formobj = $this->getFormObj();
        $this->m_TotalPage 		= Expression::evaluateExpression($this->m_TotalPage, $formobj);
        $this->m_CurrentPage 	= Expression::evaluateExpression($this->m_CurrentPage, $formobj);
    	
        $style = $this->getStyle();
        $id = $this->m_Name;
        $func = $this->getFunction();
		$sHTML="";
		$link = $this->getLink();
        $target = $this->getTarget();
        
        for ($i=1; $i<$this->m_TotalPage+1; $i++){
           if($i == $this->m_CurrentPage){
           		$sHTML .= "<a id=\"$id\" href=\"".$link.$i."\" $target $func class=\"".$this->m_CurrentCss."\">" . $i . "</a>";
           }else{
            	$sHTML .= "<a id=\"$id\" href=\"".$link.$i."\" $target $func $style>" . $i . "</a>";	
           }
    	}       

        return $sHTML;
    }

}

?>