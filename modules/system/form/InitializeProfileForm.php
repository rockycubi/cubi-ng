<?php 
require_once dirname(__FILE__)."/UserForm.php";

class InitializeProfileForm extends EasyForm
{
	public function ProfileInit()
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
		
        
        $name = $this->parseName($recArr['fullname']);        

		if(BizSystem::getUserPreference("Id")!=0)
        {
        	$user_email = BizSystem::getObject("system.do.UserDO",1)->fetchById($currentRec['user_id'])->email;
        }
        
        if($user_email!=$recArr['email'] 
        	&& BizSystem::getUserPreference("Id")!=0
        	&& $recArr['email']!=''
        	)
        {
        	//check if email address duplicate
        	if ($this->_checkDupEmail($recArr['email'],BizSystem::getUserPreference("Id")))
	        {
	        	$this->setActiveRecord($recArr);
	            $errorMessage = $this->GetMessage("EMAIL_USED");
				$errors['fld_email'] = $errorMessage;
				$this->processFormObjError($errors);
				return;
	        }  
	        
			//auto update user's email
			$email = $currentRec['email'];
			$userRec = BizSystem::getObject("system.do.UserDO",1)->fetchById($currentRec['user_id']);
			$userRec['email'] = $recArr['email'];
			$userRec->save();
        }
        
        $profileId = BizSystem::getUserProfile("profile_Id");
        $contactRec = BizSystem::getObject("contact.do.ContactDO")->fetchById($profileId);
        $contactRec['first_name'] 	= $name['first_name'];
        $contactRec['last_name'] 	= $name['last_name'];
        $contactRec['display_name'] = $name['display_name'];
        $contactRec['fast_index'] 	= $name['fast_index'];
        $contactRec['company'] 		= $recArr['company'];
        $contactRec['email'] 		= $recArr['email'];
        $contactRec['mobile'] 		= $recArr['mobile'];
        $contactRec['phone'] 		= $recArr['phone'];
        $contactRec->save();
        
        //send user data to Openbiz 
        BizSystem::getService("system.lib.CubiService")->collectUserData($recArr['subscribe']);
       
        //set initialized.lock 
        $initLock = APP_HOME.'/files/initialize_profile.lock';
        $data = '1';
        file_put_contents($initLock, $data);
        
        $this->processPostAction();
	}
	
	public function fetchData()
	{
		$profileId = BizSystem::getUserProfile("profile_Id");
        $contactRec = BizSystem::getObject("contact.do.ContactDO")->fetchById($profileId);
        $contactRec['fullname'] = $contactRec['display_name'];
        if($contactRec && $contactRec['display_name']!='System, Admin'){
        	$result =  $contactRec->toArray();
        }
        else
        {
        	$result = array();
        }
        return $result;
	}

	public function allowAccess($access=null)
	{
		$initLock = APP_HOME.'/files/initialize_profile.lock';
		if(is_file($initLock))
		{
			$pageURL = APP_INDEX."/system/general_default";
			BizSystem::clientProxy()->redirectPage($pageURL);
			return;
		}
		return parent::allowAccess($access);
	}	
	
    protected function parseName($name)
    {
    	$svcobj=BizSystem::getService("service.chineseService");
    	if($svcobj->isChinese($name)){
        	$fast_index = $svcobj->Chinese2Pinyin($name);
        }else{
        	$fast_index = $recArr['display_name'];
        }
        $nameArr['fast_index'] = substr($fast_index,0,1); 
    	$nameArr['display_name'] = $name;
		if($svcobj->isChinese($name))
		{
			//chinese name			
			switch(mb_strlen($name,'UTF-8'))
			{
				case 5:
				case 4:
					$nameArr['last_name'] = mb_substr($name,0,2,'UTF-8');
					$nameArr['first_name'] = mb_substr($name,2,mb_strlen($name,'UTF-8')-1,'UTF-8');					
					break;
				default:
					$nameArr['last_name'] = mb_substr($name,0,1,'UTF-8');
					$nameArr['first_name'] = mb_substr($name,1,mb_strlen($name,'UTF-8')-1,'UTF-8');					
					break;
			}
			
		}else{
			//english name			
			if(preg_match("/(\S*?)\s(\S*)/si", $name,$match))
			{
				$nameArr['last_name'] = $match[1];
				$nameArr['first_name'] = $match[2];			
			}
		}
		return $nameArr;
    }
    
}
?>