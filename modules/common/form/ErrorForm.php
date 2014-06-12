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
 * @version   $Id: ErrorForm.php 5017 2013-01-01 11:28:44Z hellojixian@gmail.com $
 */

class ErrorForm extends EasyForm
{
		protected $m_ShowError = false;
	    function __construct(&$xmlArr)
	    {
	        parent::readMetadata($xmlArr);     
	    }        

	    public function getSessionVars($sessionContext)
	    {
	        parent::getSessionVars($sessionContext);
	        $sessionContext->getObjVar($this->m_Name, "Errors", $this->m_Errors);	  
	        $sessionContext->getObjVar($this->m_Name, "showError", $this->m_ShowError);      	          
	    }    
	    
	    public function setSessionVars($sessionContext)
	    {
	    	parent::setSessionVars($sessionContext);
	        $sessionContext->setObjVar($this->m_Name, "Errors", $this->m_Errors);   
	        $sessionContext->setObjVar($this->m_Name, "showError", $this->m_ShowError);   
	    }
	    
	    public function getViewObject()
	    {
	    	$viewObj = BizSystem::getObject("common.view.ErrorView");
	    	return $viewObj;
	    }
   
	    public function fetchData()
	    {
	    	if($_GET['ob_err_msg'])
        	{
				$this->m_Errors = array("system"=>$_GET['ob_err_msg']);
        	}	 
	    	return parent::fetchData();
	    }
	    
	    public function showError()
	    {
	    	if($this->m_ShowError)
	    	{
	    		$this->m_ShowError=false;
	    	}else{
	    		$this->m_ShowError=true;
	    	}
	    	$this->rerender();
	    }
	    
	    public function outputAttrs()
	    {
	    	$result = parent::outputAttrs();
	    	$result['show_error'] = $this->m_ShowError;
	    	return $result;	
	    }
	    
        public function Report()
        {
        	//send an email to admin includes error messages;
        	$system_uuid = BizSystem::getService("system.lib.CubiService")->getSystemUUID();
        	
        	$report = array(
        		"system_uuid"   =>$system_uuid,
        		"error_info"	=>$this->m_Errors["system"],
        		"server_info"	=>$_SERVER,
        		"php_version"	=>phpversion(),
        		"php_extension"	=>get_loaded_extensions()
        	);

        	$reportId = BizSystem::getObject("common.lib.ErrorReportService")->report($report);
        	$this->m_Notices = array("status"=>"REPORTED",
        							"report_id"=>$reportId);
        	$this->ReRender();
        }
}
?>
