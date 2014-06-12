<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.service
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: userEmailService.php 4380 2012-09-28 09:45:42Z hellojixian@gmail.com $
 */

/**
 * User email service 
 */
class userEmailService extends MetaObject
{
    public $m_Tempaltes;
	public $m_EmailDataObj;
	public $m_SendtoQueue;
    
    function __construct(&$xmlArr)
    {
        $this->readMetadata($xmlArr);
    } 
       	
    protected function readMetadata(&$xmlArr)
    {
        parent::readMetadata($xmlArr);
    	$this->m_Tempaltes	 	= $this->readTemplates($xmlArr["PLUGINSERVICE"]["TEMPLATE"]);
        $this->m_EmailDataObj 	= isset($xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["BIZDATAOBJ"]) ? $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["BIZDATAOBJ"]: "email.do.EmailQueueDO";
        $this->m_SendtoQueue	= isset($xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["SENDTOQUEUE"]) ? $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["SENDTOQUEUE"] : "Y";
    }

    protected function readTemplates($templateArray)
    {
    	$templates = array();
    	foreach($templateArray as $template){
    		$templates[$template['ATTRIBUTES']['NAME']] = $template['ATTRIBUTES'];
    	}
    	return $templates;
    }
    
    public function UserWelcomeEmail($userId)
	{
		//init email info
		$template = $this->m_Tempaltes["WelcomeEmail"]["TEMPLATE"];
		$subject  = $this->m_Tempaltes["WelcomeEmail"]["TITLE"];
		$sender   = $this->m_Tempaltes["WelcomeEmail"]["EMAILACCOUNT"];
		
		//prepare data     
        $userDO = BizSystem::getObject("system.do.UserDO");
        $data = $userDO->directFetch("[Id]='".$userId."'", 1);

        if(!count($data))
        	return false;
        	        
        $userData = $data[0];
        $data 	  = array("userinfo"=>$userData);
        $data['app_index'] = APP_INDEX;
		$data['app_url'] = APP_URL;
		$data['operator_name'] = BizSystem::GetProfileName(BizSystem::getUserProfile("Id"));
		$data['refer_url'] = SITE_URL;
		       
		//render the email tempalte
		$tplFile = BizSystem::getTplFileWithPath($template, "email");
		$content = $this->renderEmail($data, $tplFile);

		//prepare recipient info
		$recipient['email'] = $userData['email'];
		$recipient['name']  = $userData['username'];
		
		//send it to the queue		
		$result = $this->sendEmail($sender,$recipient,$subject,$content);
		return $result;
	}
	
    public function resetUserPassword($tokenId) {
		//init email info
		$template = $this->m_Tempaltes["ForgetPasswordEmail"]["TEMPLATE"];
		$subject  = $this->m_Tempaltes["ForgetPasswordEmail"]["TITLE"];
		$sender   = $this->m_Tempaltes["ForgetPasswordEmail"]["EMAILACCOUNT"];
		
		//prepare data
        /* @var $tokenDO BizDataObj */
		$tokenDO = BizSystem::getObject("system.do.UserPassTokenDO");
        $data = $tokenDO->directFetch("[Id]='".$tokenId."'", 1);
		if(!count($data))
        	return false;        
        $userId = $data[0]['user_id'];
		$data 	 = $data[0];
		$data['app_index'] = APP_INDEX;
		$data['app_url'] = APP_URL;
		$data['operator_name'] = BizSystem::GetProfileName(BizSystem::getUserProfile("Id"));
		$data['refer_url'] = SITE_URL;
		
        $userObj = BizSystem::getObject("system.do.UserDO");
        $userData = $userObj->directFetch("[Id]='".$userId."'", 1);                	        
        if(!count($data))
        	return false;
        $userData = $userData[0];
        
        $data = array(  "userinfo"=>$userData,
        				"token"=>$data	);
        
		//render the email tempalte
		$tplFile = BizSystem::getTplFileWithPath($template, "email");
		$content = $this->renderEmail($data, $tplFile);
		
		//prepare recipient info
		$recipient['email'] = $userData['email'];
		$recipient['name']  = $userData['username'];
		
		//send it to the queue		
		$result = $this->sendEmail($sender,$recipient,$subject,$content);
		return $result;		
        
    }
    
	public function UserResetPassword($tokenId)
	{
        return $this->resetUserPassword($tokenId);
	}

	public function DataSharingEmail($recipient_user_id, $data)
	{
		//init email info
		$template = $this->m_Tempaltes["DataSharingEmail"]["TEMPLATE"];
		$subject  = $this->m_Tempaltes["DataSharingEmail"]["TITLE"];
		$sender   = $this->m_Tempaltes["DataSharingEmail"]["EMAILACCOUNT"];
				        
		//render the email tempalte
		$tplFile = BizSystem::getTplFileWithPath($template, "email");
		$content = $this->renderEmail($data, $tplFile);
		
		//prepare recipient info
		$userObj = BizSystem::getObject("system.do.UserDO");
        $userData = $userObj->directFetch("[Id]='".$recipient_user_id."'", 1);                	        
        if(!count($data))
        	return false;
        $userData = $userData[0];
        
		$recipient['email'] = $userData['email'];
		$recipient['name']  = $userData['username'];
		
		//send it to the queue		
		$result = $this->sendEmail($sender,$recipient,$subject,$content);
		return $result;		
	}	
	
	public function TaskUpdateEmail($recipient_user_id, $data)
	{
		//init email info
		$template = $this->m_Tempaltes["TaskUpdateEmail"]["TEMPLATE"];
		$subject  = $this->m_Tempaltes["TaskUpdateEmail"]["TITLE"];
		$sender   = $this->m_Tempaltes["TaskUpdateEmail"]["EMAILACCOUNT"];
				        
		//render the email tempalte
		$tplFile = BizSystem::getTplFileWithPath($template, "email");
		$content = $this->renderEmail($data, $tplFile);
		
		//prepare recipient info
		$userObj = BizSystem::getObject("system.do.UserDO");
        $userData = $userObj->directFetch("[Id]='".$recipient_user_id."'", 1);                	        
        if(!count($data))
        	return false;
        $userData = $userData[0];
        
		$recipient['email'] = $userData['email'];
		$recipient['name']  = $userData['username'];
		
		//send it to the queue		
		$result = $this->sendEmail($sender,$recipient,$subject,$content);
		return $result;		
	}

	public function NewMessageEmail($recipient_user_id, $data)
	{
		//init email info
		$template = $this->m_Tempaltes["NewMessageEmail"]["TEMPLATE"];
		$subject  = $this->m_Tempaltes["NewMessageEmail"]["TITLE"];
		$sender   = $this->m_Tempaltes["NewMessageEmail"]["EMAILACCOUNT"];
				        
		//render the email tempalte
		$tplFile = BizSystem::getTplFileWithPath($template, "email");
		$content = $this->renderEmail($data, $tplFile);
		
		//prepare recipient info
		$userObj = BizSystem::getObject("system.do.UserDO");
        $userData = $userObj->directFetch("[Id]='".$recipient_user_id."'", 1);                	        
        if(!count($data))
        	return false;
        $userData = $userData[0];
        
		$recipient['email'] = $userData['email'];
		$recipient['name']  = $userData['username'];
		
		//send it to the queue		
		$result = $this->sendEmail($sender,$recipient,$subject,$content);
		return $result;		
	}	
	
	public function DataAssignedEmail($recipient_user_id, $data)
	{
		//init email info
		$template = $this->m_Tempaltes["DataAssignedEmail"]["TEMPLATE"];
		$subject  = $this->m_Tempaltes["DataAssignedEmail"]["TITLE"];
		$sender   = $this->m_Tempaltes["DataAssignedEmail"]["EMAILACCOUNT"];
				        
		//render the email tempalte
		$tplFile = BizSystem::getTplFileWithPath($template, "email");
		$content = $this->renderEmail($data, $tplFile);
		
		//prepare recipient info
		$userObj = BizSystem::getObject("system.do.UserDO");
        $userData = $userObj->directFetch("[Id]='".$recipient_user_id."'", 1);                	        
        if(!count($data))
        	return false;
        $userData = $userData[0];
        
		$recipient['email'] = $userData['email'];
		$recipient['name']  = $userData['username'];
		
		//send it to the queue		
		$result = $this->sendEmail($sender,$recipient,$subject,$content);
		return $result;		
	}		
	
	public function DataPublishEmail($recipient_user_id, $data)
	{
		//init email info
		$template = $this->m_Tempaltes["DataPublishEmail"]["TEMPLATE"];
		$subject  = $this->m_Tempaltes["DataPublishEmail"]["TITLE"];
		$sender   = $this->m_Tempaltes["DataPublishEmail"]["EMAILACCOUNT"];
				        
		//render the email tempalte
		$tplFile = BizSystem::getTplFileWithPath($template, "email");
		$content = $this->renderEmail($data, $tplFile);
		
		//prepare recipient info
		$userObj = BizSystem::getObject("system.do.UserDO");
        $userData = $userObj->directFetch("[Id]='".$recipient_user_id."'", 1);                	        
        if(!count($data))
        	return false;
        $userData = $userData[0];
        
		$recipient['email'] = $userData['email'];
		$recipient['name']  = $userData['username'];
		
		//send it to the queue		
		$result = $this->sendEmail($sender,$recipient,$subject,$content);
		return $result;		
	}		
	
	public function SendEmailToUser($template_name, $recipient_user_id, $data)
	{
		//init email info
		$template = $this->m_Tempaltes[$template_name]["TEMPLATE"];
		$subject  = $this->m_Tempaltes[$template_name]["TITLE"];
		$sender   = $this->m_Tempaltes[$template_name]["EMAILACCOUNT"];
				        
		//render the email tempalte		
		$data['app_index'] = APP_INDEX;
		$data['app_url'] = APP_URL;
		$data['operator_name'] = BizSystem::GetProfileName(BizSystem::getUserProfile("Id"));
		$data['refer_url'] = SITE_URL;
		
		$tplFile = BizSystem::getTplFileWithPath($template, "email");
		$content = $this->renderEmail($data, $tplFile);
		
		//prepare recipient info
		$userObj = BizSystem::getObject("system.do.UserDO");
        $userData = $userObj->directFetch("[Id]='".$recipient_user_id."'", 1);                	        
        if(!count($data))
        	return false;
        $userData = $userData[0];
        
		$recipient['email'] = $userData['email'];
		$recipient['name']  = $userData['username'];
		
		//send it to the queue		
		$result = $this->sendEmail($sender,$recipient,$subject,$content);
		return $result;		
	}
	
	public function SendEmailToContact($template_name, $recipient_contact_id, $data)
	{
		//init email info
		$template = $this->m_Tempaltes[$template_name]["TEMPLATE"];
		$subject  = $this->m_Tempaltes[$template_name]["TITLE"];
		$sender   = $this->m_Tempaltes[$template_name]["EMAILACCOUNT"];
				        
		//render the email tempalte	
		$data['app_index'] = APP_INDEX;
		$data['app_url'] = APP_URL;

		
		$data['operator_name'] = BizSystem::GetProfileName($data['create_by']);
		$data['operator_email'] = BizSystem::GetProfileEmail($data['create_by']);
		$data['refer_url'] = SITE_URL;
		
		//prepare recipient info
		$userObj = BizSystem::getObject("contact.do.ContactSystemDO");
        $userData = $userObj->directFetch("[Id]='".$recipient_contact_id."'", 1);                	        
        if(!count($data))
        	return false;
        $userData = $userData[0];
        
		$recipient['email'] = $userData['email'];
		$recipient['name']  = $userData['display_name'];
		
		
		$data['contact_display_name'] = $userData['display_name'];
		
		
		$tplFile = BizSystem::getTplFileWithPath($template, "email");
		$content = $this->renderEmail($data, $tplFile);
		if($userData['email']==''){
			//if no email address , then do nothing
			return ;
		}
		
		//send it to the queue		
		$result = $this->sendEmail($sender,$recipient,$subject,$content);
		return $result;		
	}
	
	public function SystemInternalErrorEmail($recipient, $errMsg)
	{
		//init email info
		$template = $this->m_Tempaltes["SystemInternalError"]["TEMPLATE"];
		$subject  = $this->m_Tempaltes["SystemInternalError"]["TITLE"];
		$sender   = $this->m_Tempaltes["SystemInternalError"]["EMAILACCOUNT"];
		
		//prepare data
		$system 	=  array("error_message"=>$errMsg);
		$data		=  array("system"=>$system);
        
		//render the email tempalte
		$tplFile = BizSystem::getTplFileWithPath($template, "email");
		$content = $this->renderEmail($data, $tplFile);
				
		//send it to the queue		
		$result = $this->sendEmail($sender,$recipient,$subject,$content);
		return $result;		
	}
	
	public function CronJobEmail($recipientEmail, $job_name, $output)
	{
		//init email info
		$template = $this->m_Tempaltes["CronjobEmail"]["TEMPLATE"];
		$subject  = $this->m_Tempaltes["CronjobEmail"]["TITLE"];
		$sender   = $this->m_Tempaltes["CronjobEmail"]["EMAILACCOUNT"];
		
		//prepare data
		$data["job_name"] = $job_name;
		$data["job_output"] = $output;
        
		//render the email tempalte
		$tplFile = BizSystem::getTplFileWithPath($template, "email");
		$content = $this->renderEmail($data, $tplFile);
		
		//prepare recipient info
		$recipient['email'] = $recipientEmail;
		$recipient['name']  = $recipientEmail;
				
		//send it to the queue		
		$result = $this->sendEmail($sender,$recipient,$subject,$content);
		return $result;		
	}
	
	protected function renderEmail($content, $tplFile)
	{
        $smarty  = BizSystem::getSmartyTemplate();
        foreach ($content as $key=>$value){
        	$smarty->assign($key, $value);
        }
        return $smarty->fetch($tplFile);		
	}
	
	protected function sendEmail($sender,$recipient,$subject,$content)
	{		

		$dataObj = BizSystem::getObject($this->m_EmailDataObj);
		
		if(is_array($recipient)){
			$recipient_name = $recipient['name'];
			$recipient		= $recipient['email'];
		}else{
			$recipient_name = "";
		}
		
		$recArr['sender'] 			= $sender;
	    $recArr['recipient_name'] 	= $recipient_name;
	    $recArr['recipient'] 		= $recipient;
	    $recArr['subject'] 			= $subject;
	    $recArr['content'] 			= $content;		    
	    
	    $ok = $dataObj->insertRecord($recArr);
		    
		if($this->m_SendtoQueue=='Y')
		{	
			return $ok;
		}
		else
		{			
			//send email now
			$recArr = $dataObj->getActiveRecord();
			$email_id = $recArr['Id'];
			$this->sendEmailNow($email_id);			
		}
		
	}
	
//	this function should be called by cronjob.php 
//	or called by SendEmail
	public function sendEmailNow($email_id){
		//prepare email data				
		$dataObj = BizSystem::getObject($this->m_EmailDataObj);				
		$dataObj->setSearchRule("[Id]='".$email_id."' and [status]!='sending' ", true);	
		$data = $dataObj->fetch();
		$dataObj->setActiveRecord($data[0]);	
		if(!count($data))
        	return false;        
		$data 	 = $data[0];
		
		$sender = $data["sender"];
		$recipient = array(
					 array("email"=>$data["recipient"],
						   "name" =>$data["recipient_name"])
					 );
		$subject = $data["subject"];
		$content = $data["content"];							
		
		//update queue status to sending
		$recArr = array("status"=>"sending");
		$dataObj->updateRecord($recArr);
		
		//init email service
		$emailObj 	= BizSystem::getService(EMAIL_SERVICE);
		$emailObj->useAccount($sender);
	    $emailObj->sendEmail ($recipient, null,null, $subject, $content, null, true);

		//update queue status to sent
		$recArr = array("status"=>"sent");
		$dataObj->updateRecord($recArr);
	    return;
	}
	
	public function sendEmailFromQueue()
	{
		$dataObj = BizSystem::getObject($this->m_EmailDataObj);
		$dataObj->setSortRule("[Id] ASC");
		$dataObj->setSearchRule("[status]='pending'", true);
		$data = $dataObj->fetch();
		
		foreach($data as $email){
			$this->sendEmailNow($email['Id']);
		}
		return ;
	}
}

?>