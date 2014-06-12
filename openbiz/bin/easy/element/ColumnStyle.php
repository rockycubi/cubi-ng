<?php 
//include_once("RawData.php");

class ColumnStyle extends RawData{

    public function setSortFlag($flag=null)
    {
        $this->m_SortFlag = $flag;
    }
	
    public function renderLabel()
    {
        return null;
    }	
}

?>