<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.user.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ForgetPassForm.php 3375 2012-05-31 06:23:11Z rockyswen@gmail.com $
 */

/**
 * Openbiz Cubi 
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   user.form
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: ForgetPassForm.php 3375 2012-05-31 06:23:11Z rockyswen@gmail.com $
 */

/**
 * ForgetPassForm class - implement the logic of forget password form
 *
 * @package user.form
 * @author Jixian Wang
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class ForgetPassForm extends EasyForm
{
    /**
     * Reset the password
     *
     * @return void
     */
    public function resetPassword()
    {
    	global $g_BizSystem;
        $recArr = $this->readInputs();

        $this->setActiveRecord($recArr);
        if (count($recArr) == 0)
            return;

        try
        {
            $this->ValidateForm();
	        if($this->ValidateEmail($recArr['username'], $recArr['email']))
	        {
	        	//Init user profile for event logging
	        	$profile = $g_BizSystem->InituserProfile($recArr['username']);
	        }else{
	        	return;
	        }
	        
	        //generate pass_token
	        $token = $this->GenerateToken($profile);
	        
	        if($token){
	        	//event log	        		        	
	        	
	        	$eventlog 	= BizSystem::getService(EVENTLOG_SERVICE);
	        	$logComment=array($username,$_SERVER['REMOTE_ADDR']);
	    		$eventlog->log("USER_MANAGEMENT", "MSG_GET_PASSWORD_TOKEN", $logComment);
					    		
	        	//send user email
	        	$emailObj 	= BizSystem::getService(USER_EMAIL_SERVICE);
	        	$emailObj->UserResetPassword($token['Id']);

	        	BizSystem::SessionContext()->destroy();
	    		//goto URL
	    		$this->processPostAction();
	        }
        }
        catch (ValidationException $e)
        {
            $this->processFormObjError($e->m_Errors);
            return;
        }  
    }
   
    /**
     * Generate an unique token for future validation
     *
     * @param array $userProfile user profile array
     * @return mixed $token array or false
     */
    protected function GenerateToken($userProfile)
    {   		   	
   		$token = uniqid();
   		$recArr = array(
   					"user_id" => $userProfile['Id'],
   					"token" => $token,
   					"expiration" => date("Y-m-d H:i:s", time()+86400*2),   					
   					);
   		$tokenObj = BizSystem::getObject('system.do.UserPassTokenDO');
   		try
        {
   			if($tokenObj->insertRecord($recArr))
   			{
   				$recArr = $tokenObj->getActiveRecord();
   				return $recArr;   					
   			}else{
   				return false;
   			}
   		} 
        catch (BDOException $e) 
        {
	          $errorMsg = $e->getMessage();
	          BizSystem::log(LOG_ERR, "DATAOBJ", "DataObj error = ".$errorMsg);
	          BizSystem::ClientProxy()->showErrorMessage($errorMsg);
	          return false;
	    }
    }

    /**
     * Validate user and email by matching user and email with existing user record
     *
     * @param string $username 
     * @param string $email     
     * @return boolean
     */
    protected function validateEmail($username, $email)
    {
		$userObj = BizSystem::getObject('system.do.UserDO');
		try 
        {
		   	$userProfile = $userObj->directFetch("[username]='".$username."' and status='1'", 1);
		   	$userProfile = $userProfile[0];
            
		   	if($userProfile['email'] != $email){
		   		$errorMessage = $this->getMessage("EMAIL_INVALID");
                $this->m_ValidateErrors['email'] 	= $errorMessage;                
				throw new ValidationException($this->m_ValidateErrors);
				return false;
		   	}		   	
	      } 
	        catch (ValidationException $e)
	        {
	            $this->processFormObjError($e->m_Errors);
	            return;
	        }  
	      return true;
	}
}  
?>   