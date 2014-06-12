<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.common.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: LicenseForm.php 4992 2012-12-31 06:43:41Z hellojixian@gmail.com $
 */

class LicenseForm extends EasyForm
{
	public $m_ErrorCode;
	public $m_ErrorParams;
	public $m_SourceURL;
	public $m_ModuleName;
	public $m_ModuleRegister;
	public $m_ModuleTrial;	
	
	public function outputAttrs()
	{
		$result = parent::outputAttrs();		
		$result['appInfo'] = $this->getAppInfo();
		return $result;
	}
	
	public function getViewObject()
	{
		$viewObj = BizSystem::getObject("common.view.LicenseInvalidView");
        return $viewObj;
		
	}
	
 	public function setSessionVars($sessionContext)
    {       
        if($this->m_ErrorParams){
	 		$sessionContext->setObjVar("common.form.LicenseForm", "SourceURL", $this->m_SourceURL);
	        $sessionContext->setObjVar("common.form.LicenseForm", "ErrorCode", $this->m_ErrorCode);        	
	        $sessionContext->setObjVar("common.form.LicenseForm", "ErrorParams", $this->m_ErrorParams);
        }
     	parent::setSessionVars($sessionContext);        
    }	
	
	public function getSessionVars($sessionContext)
    {    	
        $sessionContext->getObjVar("common.form.LicenseForm", "SourceURL", $this->m_SourceURL);
        $sessionContext->getObjVar("common.form.LicenseForm", "ErrorCode", $this->m_ErrorCode);
        $sessionContext->getObjVar("common.form.LicenseForm", "ErrorParams", $this->m_ErrorParams);
     	parent::getSessionVars($sessionContext);        
    }	    
    
    public function getAppInfo()
    {
    	if($this->getAppRegister())
		{
			$func = $this->m_ModuleName.'_get_product_info';
			$result = $func();
		}    	
		return $result;
    }
    
	public function getAppRegister()
	{
		if(!$this->m_ModuleName)
		{
			$this->getAppModuleName();
		}
		if($this->m_ModuleName)
		{
			$filename = MODULE_PATH.DIRECTORY_SEPARATOR.$this->m_ModuleName.DIRECTORY_SEPARATOR.'register_handler.php';
			if(file_exists($filename))
			{
				require_once($filename);
				$mod_register_func = strtolower($this->m_ModuleName).'_register_handler';
				if(function_exists($mod_register_func))
				{
					$this->m_ModuleRegister = $mod_register_func;					
				}
				$mod_trial_func = strtolower($this->m_ModuleName).'_trial_handler';
				if(function_exists($mod_trial_func))
				{
					$this->m_ModuleTrial = $mod_trial_func;					
				}
				return $mod_register_func;
			}
		}
		return ;
	}
	
	public function getAppModuleName()
	{
		$current_file = $this->m_ErrorParams['current_file'];
		$current_file = str_replace(MODULE_PATH, "", $current_file);
		preg_match("|[\\\/]?(.*?)[\\\/]{1}|si",$current_file,$matches);
		$this->m_ModuleName = $matches[1];
		return $this->m_ModuleName;
	}
	
	public function GoRegister()
	{
		$this->getAppRegister();
		if($this->m_ModuleRegister && function_exists($this->m_ModuleRegister))
		{
			$param = null;
			if(function_exists("ioncube_server_data")){
				$param = ioncube_server_data();				
			}
			return call_user_func($this->m_ModuleRegister,$param);
		}
	}
	
	public function getErrorMessage()
	{
		switch((int)$this->m_ErrorCode)
		{
			case 1:
				$msg = "ION_CORRUPT_FILE";
				break;
			case 2:
				$msg = "ION_EXPIRED_FILE";
				break;
			case 3:
				$msg = "ION_NO_PERMISSIONS";
				break;
			case 4:
				$msg = "ION_CLOCK_SKEW";
				break;				
			case 5:
				$msg = "ION_UNTRUSTED_EXTENSION";
				break;
			case 6:
				$msg = "ION_LICENSE_NOT_FOUND";
				break;
			case 7:
				$msg = "ION_LICENSE_CORRUPT";
				break;
			case 8:
				$msg = "ION_LICENSE_EXPIRED";
				break;
			case 9:
				$msg = "ION_LICENSE_PROPERTY_INVALID";
				break;
			case 10:
				$msg = "ION_LICENSE_HEADER_INVALID";
				break;
			case 11:
				$msg = "ION_LICENSE_SERVER_INVALID";
				break;
			case 12:
				$msg = "ION_UNAUTH_INCLUDING_FILE";
				break;
			case 13:
				$msg = "ION_UNAUTH_INCLUDED_FILE";
				break;
			case 14:
				$msg = "ION_UNAUTH_APPEND_PREPEND_FILE";
				break;				
		}
		$msg = $this->getMessage($msg);
		return $msg;
	}	
}
?>