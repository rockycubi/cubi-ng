<?php
include_once OPENBIZ_BIN.'/easy/element/Radio.php'; 
class ActiveLogicRadio extends Radio
{
    public function getFromList(&$list, $selectFrom=null)
    {
    	parent::getFromList($list, $selectFrom);
    	$appInfo = $this->getFormObj()->getAppInfo();
    	$trailDays = $appInfo['APP_TRAIL_DAYS'];
    	if((int)$trailDays){
    		unset($list[3]);
    		$this->m_Value = 'FREETRIAL';
    	}else{
    		unset($list[1]);
    		$this->m_Value = 'PURCHASE';
    	}
    	foreach($list as $key=>$value)
    	{
    		$value = str_replace("[strong]","<strong>",$value);
    		$value = str_replace("[/strong]","</strong>",$value);
    		$value = str_replace("[days]",$trailDays,$value);
    		$list[$key]=$value;
    	}    	
    }
}
?>