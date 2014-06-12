<?php 
require_once MODULE_PATH.'/websvc/lib/WebsvcService.php';
class userService extends  WebsvcService
{
	protected $username;
	protected $password;
	protected $smartcard;

    /**
     * login action
     * @input string username
	 * @input string password
	 * on success return ('redirect'=>url), on error return ('errors'=>(field1=>errormsg1,field2=>errormsg2))
     * @return array. 
     */
    public function login($username=null, $password=null)
    {
	  	/*try
        {
            $this->ValidateForm();
        }
        catch (ValidationException $e)
        {        	
            $this->processFormObjError($e->m_Errors);
            return;
        }*/

	  	// get the username and password	
		$this->username = $username;
		$this->password = $password;		
		$this->smartcard = null;
		
		global $g_BizSystem;		
		try {
    		if ($this->authUser()) 
    		{
                // after authenticate user: 1. init profile
    			$profile = $g_BizSystem->InitUserProfile($this->username);
    			
    			// after authenticate user: 3. update login time in user record
    	   	    $this->UpdateloginTime();
    	   	   		
    	   	    $redirectPage = APP_INDEX.$profile['roleStartpage'][0];
    	   	   	if(!$profile['roleStartpage'][0])
    	   	   	{
    	   	   		$result['errors']['password'] = $this->getMessage("PERM_INCORRECT");
					$result['errors']['login_status'] = $this->getMessage("LOGIN_FAILED");
    				return $result;
    	   	   	}
    	   	    
    	   	    if($this->m_LastViewedPage!=""){
					return array('redirect'=>$this->m_LastViewedPage);
    	   	    }
				return array('redirect'=>$redirectPage);
    		}
    		else { 
				$result['errors']['password'] = $this->getMessage("PASSWORD_INCORRECT");  
				$result['errors']['login_status'] = $this->getMessage("LOGIN_FAILED");						
				break;		    			   			
    		}
    	}
    	catch (Exception $e) {
			//print_r($e);
    		$result['errors']['login_status'] = $this->getMessage("LOGIN_FAILED");
    	}
		
		return $result;
    }
    
    protected function authUser()
    {
    	$svcobj 	= BizSystem::getService(AUTH_SERVICE);    	 			
		$result = $svcobj->authenticateUser($this->username,$this->password);  	
    	return $result;
    }
   
    /**
     * Update login time
     *
     * @return void
     */
    protected function UpdateloginTime()
    {
        $userObj = BizSystem::getObject('system.do.UserDO');
		$curRecs = $userObj->directFetch("[username]='".$this->username."'", 1);
		if(count($curRecs)==0){
			return false;
		}
		$dataRec = new DataRecord($curRecs[0], $userObj);            
		$dataRec['lastlogin'] = date("Y-m-d H:i:s");
		$ok = $dataRec->save();
        return true;
   }

}
?>