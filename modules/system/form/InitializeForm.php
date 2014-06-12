<?php 
require_once dirname(__FILE__)."/UserForm.php";

class InitializeForm extends EasyForm
{
	public function SystemInit()
	{
	 	$currentRec = $this->fetchData();
        $recArr = $this->readInputRecord();

        if (count($recArr) == 0)
            return;

        try
        {
            $this->ValidateForm();
        }
        catch (ValidationException $e)
        {
            $this->processFormObjError($e->m_Errors);
            return;
        }
		
        // new save logic
        $user_id = 0;
        $prefDo = $this->getDataObj();
        
        foreach ($this->m_DataPanel as $element)
        {
            $value = $recArr[$element->m_FieldName];
            if ($value === null){ 
            	continue;
            } 
            if($element->m_FieldName=='password')
            {
            	//update admin password
            	$currentUserId = BizSystem::getUserProfile("Id");
            	$userRec = BizSystem::getObject("system.do.UserSystemDO")->fetchById($currentUserId);
            	$userRec['password'] = hash(HASH_ALG, $value);
            	$userRec->save();            	
            	continue;
            }
            if(substr($element->m_FieldName,0,1)=='_'){
	            $name = substr($element->m_FieldName,1);
            	$recArrParam = array(
            		"user_id" => $user_id,
            		"name"	  => $name,
            		"value"   => $value,
	            	"section" => $element->m_ElementSetCode,
	            	"type" 	  => $element->m_Class,	            
	            );
	            //check if its exsit
	            $record = $prefDo->fetchOne("[user_id]='$user_id' and [name]='$name'");
	            if($record){
	            	//update it
	            	$recArrParam["Id"] = $record->Id;
	            	$prefDo->updateRecord($recArrParam,$record->toArray());
	            }else{
	            	//insert it	            	
	            	$prefDo->insertRecord($recArrParam);
	            }
	            
	            //update default app_init setting
	            $config_file = APP_HOME.'/bin/app_init.php';
	            switch($name){	            	
	            	case "system_name":
	            		if($value!=DEFAULT_SYSTEM_NAME){
	            			//update default theme DEFAULT_THEME_NAME
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}DEFAULT_SYSTEM_NAME[\'\\\"]{1}.*?\)\;/i","define('DEFAULT_SYSTEM_NAME','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);
	            		}
	            		break;
					case "siteurl":
						//update default theme SITE_URL
            			$data = file_get_contents($config_file);	            			
            			$data = preg_replace("/define\([\'\\\"]{1}SITE_URL[\'\\\"]{1}.*?\)\;/i","define('SITE_URL','$value');",$data);	            			
            			@file_put_contents($config_file,$data);
	            		break;	   	            		    
	            	case "sessionstrict":
						//update default theme SESSION_STRICT
	            		if($value!=SESSION_STRICT){
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}SESSION_STRICT[\'\\\"]{1}.*?\)\;/i","define('SESSION_STRICT','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);
	            		}
	            		break;	
	            	     		
	            	case "language":
	            	    if($value!=DEFAULT_LANGUAGE){
	            			//update default theme DEFAULT_LANGUAGE
	            			$data = file_get_contents($config_file);	            			
	            			$data = preg_replace("/define\([\'\\\"]{1}DEFAULT_LANGUAGE[\'\\\"]{1}.*?\)\;/i","define('DEFAULT_LANGUAGE','$value');",$data);	            			
	            			@file_put_contents($config_file,$data);	    

	            			//make changes now
	            			BizSystem::sessionContext()->setVar("LANG",$value );
	            		}
	            		break;
	            	            			            			            		        		
	            }
            }
        }
        //set initialized.lock 
        $initLock = APP_HOME.'/files/initialize.lock';
        $data = '1';
        file_put_contents($initLock, $data);
        
        $this->processPostAction();
	}
	
	public function allowAccess($access=null)
	{
		$initLock = APP_HOME.'/files/initialize.lock';
		if(is_file($initLock))
		{
			$pageURL = APP_INDEX."/system/general_default";
			BizSystem::clientProxy()->redirectPage($pageURL);
			return;
		}
		return parent::allowAccess($access);
	}
	
	public function fetchData(){
        if ($this->m_ActiveRecord != null)
            return $this->m_ActiveRecord;
        
        $dataObj = $this->getDataObj();
        if ($dataObj == null) return;

		$this->m_FixSearchRule = "[user_id]='0'";
        
        if (!$this->m_FixSearchRule && !$this->m_SearchRule)
        	return array();
        
    	QueryStringParam::setBindValues($this->m_SearchRuleBindValues);
        
        	
        if ($this->m_RefreshData)   $dataObj->resetRules();
        else $dataObj->clearSearchRule();

        if ($this->m_FixSearchRule)
        {
            if ($this->m_SearchRule)
                $searchRule = $this->m_SearchRule . " AND " . $this->m_FixSearchRule;
            else
                $searchRule = $this->m_FixSearchRule;
        }

        $dataObj->setSearchRule($searchRule);
        QueryStringParam::setBindValues($this->m_SearchRuleBindValues);        

        $resultRecords = $dataObj->fetch();
        foreach($resultRecords as $record){
        	$prefRecord["_".$record['name']] = $record["value"];
        }
        $prefRecord["_siteurl"] = SITE_URL;
        $prefRecord["_system_name"] = DEFAULT_SYSTEM_NAME;
        
        $this->m_RecordId = $resultRecords[0]['Id'];
        $this->setActiveRecord($prefRecord);

        QueryStringParam::ReSet();
        return $prefRecord;    
    }
    
    
}
?>