<?php 
require_once MODULE_PATH.'/websvc/lib/WebsvcService.php';
class userService extends  WebsvcService
{
	public function getStatus()
	{
		$result = array();
		$userId = BizSystem::getUserProfile("Id");
		if($userId)
		{
			$result['login_status'] = 1;
			$result['display_name'] = BizSystem::getUserProfile("profile_display_name");
			$result['email'] 		= BizSystem::getUserProfile("email");
		}
		else
		{
			$result['login_status'] = 0;			
		}
		return $result;
	}
}
?>