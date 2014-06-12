<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.websvc.lib
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: WebsvcService.php 5452 2014-05-09 06:19:04Z rockyswen@gmail.com $
 */


include_once 'WebsvcError.php';
include_once 'WebsvcResponse.php';

class WebsvcService extends MetaObject
{   
    public $errorCode = 0;
    public $m_WebsvcDO = "websvc.do.WebsvcDO";
    public $m_PublicMethods;
    public $m_MessageFile;
    public $m_Messages;
    public $m_RequireAuth = "N";

    function __construct(&$xmlArr)
    {      
        $this->readMetadata($xmlArr);
    }

    protected function readMetadata(&$xmlArr)
    {      
        $this->m_RequireAuth = isset($xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["REQUIREAUTH"]) ? $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["REQUIREAUTH"] : 'N';
        $this->m_RequireAuth = strtoupper($this->m_RequireAuth);
        $this->m_PublicMethods = new MetaIterator($xmlArr["PLUGINSERVICE"]["PUBLICMETHOD"],"PublicMethod",$this);
        $this->m_MessageFile = isset($xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["MESSAGEFILE"]) ? $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["MESSAGEFILE"] : null;
        $this->m_Messages = Resource::loadMessage($this->m_MessageFile);
    }
/*
      - authenticate($api_key, $secret)
      - checkAccess($api_key, $secret, $method)
      - invoke($method, $params)
      - printOutput($format, $response)
      - Error_Code
*/
    public function invoke()
    {
        $username = $this->getInput('username');
        $api_key = $this->getInput('api_key');
        $secret = $this->getInput('secret');
        $format = $this->getInput('format');
        
        if($this->m_RequireAuth=='Y'){
	        if ($this->authenticate($username, $api_key, $secret) == false) {
	            $this->output(null, $format);
	            return;
	        }
        }
        
        $service = $this->getInput('service');
        $method = $this->getInput('method');
        
        if ($this->checkAccess($username, $method) == false) {
            $this->output(null, $format);
            return;
        }
        
        // read inputs
        $args = $this->getInputArgs('args');
        
        // call function
        if(is_array($args)){
        	$response = call_user_func_array(array($this, $method), $args);
        }
        else
        {        	 
        	$response = $this->$method();
        }
        $this->output($response, $format);
    }
    
    protected function getInput($name)
    {
        $val = isset($_REQUEST[$name]) ? $_REQUEST[$name] : null;
        return $val;
    }
    
    protected function getInputArgs()
    {
        if (isset($_REQUEST['argsJson'])) {
            $argsJson = $_REQUEST['argsJson'];
            $args = json_decode($argsJson, true);
            return $args;
        }
        // read 'arg_name' or 'argsJson'
        $args = array();
        foreach ($_REQUEST as $name=>$val) {
            if (strpos($name, 'arg_') === 0) {
                list($arg, $key) = explode('_', $name);
                $args[$key] = $val;
            }
        }
        return $args;
    }
    
    protected function authenticate($username, $api_key, $secret=null)
    {
        $websvcDO = BizSystem::getObject($this->m_WebsvcDO);
        $searchRule = "[username]='$username' AND [api_key]='$api_key'";
        if ($secret)
            $searchRule .= " AND [secret]='$secret'";
        $record = $websvcDO->fetchOne($searchRule);
        if (!$record) {
            $this->errorCode = WebsvcError::INVALID_APIKEY;
            return false;
        }
        return true;        
    }
    
    /*
      <Service Name=...>
      <PublicMethod Name=... Access=.../>
      <PublicMethod Name=... Access=.../>
    */
    protected function checkAccess($username, $method)
    {
        // check if the method is defined in public methods
        $validMethod = false;
        foreach ($this->m_PublicMethods as $pmethod)
        {
            if (strtolower($method) == strtolower($pmethod->m_Name)) {
                $validMethod = true;
                break;
            }
        }
        if (!$validMethod) {
            $this->errorCode = WebsvcError::INVALID_METHOD;
            return false;
        }
        
        $access = $pmethod->m_Access;
        return $this->checkPermission($username, $access);
    }
    
    protected function checkPermission($username, $access)
    {
        if (!$access) return true;
        // check user ACL 
        // load user profile first and check profile against public method Access
        $profileSvc = BizSystem::getService(PROFILE_SERVICE);
        $profile = $profileSvc->InitProfile($username);
        //echo $access; print_r($profile); exit;
        $aclSvc = BizSystem::getService(ACL_SERVICE);
        if (!$aclSvc->checkUserPerm($profile, $access)) {
            $this->errorCode = WebsvcError::NOT_AUTH;
            return false;
        }
        return true;
    }
	
	/**
     * Get message, and translate it
     *
     * @param string $messageId message Id
     * @param array $params
     * @return string message string
     */
    public function getMessage($messageId, $params=array())
    {
        $message = isset($this->m_Messages[$messageId]) ? $this->m_Messages[$messageId] : constant($messageId);
        //$message = I18n::getInstance()->translate($message);
        $message = I18n::t($message, $messageId, $this->getModuleName($this->m_Name));        
        $msg = @vsprintf($message,$params);
        if(!$msg){ //maybe in translation missing some %s can cause it returns null
        	$msg = $message;
        }
        return $msg;
    }
    
    /**
     * 
     * output result to remtoe client 
     * @param unknown_type $response
     * @param unknown_type $format
     * @param String  $checksumKey  remote client may use this key to validate response data, this logic has been used in app cloud cluster countrol
     */
    protected function output($response=null, $format='xml', $checksumKey = null)
    {
        $errMsg = WebsvcError::getErrorMessage($this->errorCode);
        $wsResp = new WebsvcResponse();
        $wsResp->setChecksumKey($checksumKey);
        $wsResp->setError($this->errorCode, $errMsg);
        $wsResp->setData($response);
        $wsResp->output($format);
    }
}

class PublicMethod
{
    public $m_Name;
    public $m_Access;

    /**
     * Contructor, store form info from array to variable of class
     *
     * @param array $xmlArr array of form information
     */
    public function __construct($xmlArr)
    {
        $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
        $this->m_Access = $xmlArr["ATTRIBUTES"]["ACCESS"];
    }
}
?>