<?php 
class InitializeGroupForm extends EasyForm
{

	protected $m_GroupDO = "system.do.GroupDO";
	
	public function Initialize()
	{
	    $currentRec = $this->fetchData();
        $recArr = $this->readInputRecord();
        $this->setActiveRecord($recArr);
        if (count($recArr) != 0){
            	
	        try
	        {
	            $this->ValidateForm();
	        }
	        catch (ValidationException $e)
	        {
	            $this->processFormObjError($e->m_Errors);
	            return;
	        }
	
	        $groupDO = BizSystem::getObject($this->m_GroupDO);
	        //rename default group
	        if((int)$recArr['rename_default_group']==1)
	        {
	        	$defaultGroupRec = $groupDO->fetchOne("","[Id] ASC");
	        	$defaultGroupRec['name'] = $recArr['rename_default_group_name'];
	        	$defaultGroupRec->save();
	        }
	        
	        //add new groups	        
	        foreach (array(
	        	"add_group_1",
	        	"add_group_2",
	        	"add_group_3",
	        	"add_group_4",
	        	"add_group_5"
	        		) as $addGroup){
		        if((int)$recArr[$addGroup]==1)
		        {
		        	$groupRec = array(
		        		"name" => $recArr[$addGroup.'_name'],
		        		"status" => 1,
		        	);
		        	$groupDO->insertRecord($groupRec);
		        }
	        }
	        
	        //default data sharing setting
	        $prefDo = BizSystem::getObject("myaccount.do.PreferenceDO");
	        $config_file = APP_HOME.'/bin/app_init.php';
        	$value = $recArr['_data_acl'];
			//update default theme DATA_ACL
            if($value!=DATA_ACL){
          		$data = file_get_contents($config_file);	            			
           		$data = preg_replace("/define\([\'\\\"]{1}DATA_ACL[\'\\\"]{1}.*?\)\;/i","define('DATA_ACL','$value');",$data);	            			
        		@file_put_contents($config_file,$data);
            }            	
            $recArrParam = array(
            		"user_id" => 0,
            		"name"	  => 'data_acl',
            		"value"   => $value,
	            	"section" => 'General',
	            	"type" 	  => 'DropDownList',	            
	        );
	        //check if its exsit
	        $record = $prefDo->fetchOne("[user_id]='0' and [name]='data_acl'");
	        if($record){
	            	//update it
	            	$recArrParam["Id"] = $record->Id;
	            	$prefDo->updateRecord($recArrParam,$record->toArray());
	        }else{
	            	//insert it	            	
	            	$prefDo->insertRecord($recArrParam);
	        }
            
            
			$value = $recArr['_group_data_share'];
            if($value!=GROUP_DATA_SHARE){
            	$data = file_get_contents($config_file);	            			
            	$data = preg_replace("/define\([\'\\\"]{1}GROUP_DATA_SHARE[\'\\\"]{1}.*?\)\;/i","define('GROUP_DATA_SHARE','$value');",$data);	            			
            	@file_put_contents($config_file,$data);	            			
            }
            $recArrParam = array(
            		"user_id" => 0,
            		"name"	  => 'group_data_share',
            		"value"   => $value,
	            	"section" => 'General',
	            	"type" 	  => 'DropDownList',	            
	        );
	        //check if its exsit
	        $record = $prefDo->fetchOne("[user_id]='0' and [name]='group_data_share'");
	        if($record){
	            	//update it
	            	$recArrParam["Id"] = $record->Id;
	            	$prefDo->updateRecord($recArrParam,$record->toArray());
	        }else{
	            	//insert it	            	
	            	$prefDo->insertRecord($recArrParam);
	        }
            
	            		
	        //put init lock
	        $group_init_lock = APP_FILE_PATH.DIRECTORY_SEPARATOR.'initialize_group.lock';
	        file_put_contents($group_init_lock, '1');
	        
			//redirect back to last view
	        $lastViewURL = $this->getViewObject()->getLastViewURL();
	        BizSystem::clientProxy()->redirectPage($lastViewURL);
	        return;
        }		
	}
}
?>