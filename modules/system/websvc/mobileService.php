<?php 
require_once MODULE_PATH.'/websvc/lib/WebsvcService.php';
class mobileService extends  WebsvcService
{
	public function getServerInfo()
	{
		$result = array(
			'system_name' => DEFAULT_SYSTEM_NAME,
			'system_icon' => SITE_URL.'/images/cubi_logo_large.png'
		);
		return $result;
	}	
	
	public function login()
	{
		$username = $_GET['username'];
		$password = $_GET['password'];
		$svcobj 	= BizSystem::getService(AUTH_SERVICE);
		
		if ($svcobj->authenticateUser($username,$password)) 
    	{
                // after authenticate user: 1. init profile
    			$profile = BizSystem::instance()->InitUserProfile($username);
    			
    			// after authenticate user: 2. insert login event
    			$eventlog 	= BizSystem::getService(EVENTLOG_SERVICE);
    			$logComment=array(	$username, $_SERVER['REMOTE_ADDR']);
    			$eventlog->log("LOGIN", "MSG_LOGIN_SUCCESSFUL", $logComment);
    			
    			// after authenticate user: 3. update login time in user record
    	 		$userObj = BizSystem::getObject('system.do.UserDO');
    	 		        
            	$userRec = $userObj->fetchOne("[username]='$username'");
            	$userRec['lastlogin'] = date("Y-m-d H:i:s");
            	$userId = $userRec['Id'];
            	$userRec->save();
            	
           		
    	}    	
		$result = array(
			"user_id" => $userId
		);
		return $result;
	}
}
?>