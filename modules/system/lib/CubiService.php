<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.system.lib
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ModuleService.php 4449 2012-10-22 06:21:42Z hellojixian@gmail.com $
 */

include_once(MODULE_PATH."/common/lib/fileUtil.php");
include_once(MODULE_PATH."/common/lib/httpClient.php");

class CubiService extends  MetaObject
{
	const CUBI_VERSION = "3.0";	
	protected $m_UDC_Server;
  	
	function __construct(&$xmlArr)
   	{      
   	   $this->readMetadata($xmlArr);
   	}	
	
   	protected function readMetadata(&$xmlArr)
   	{      
     	 $this->m_UDC_Server 	= $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["UDCSERVER"].'/ws.php/udc/CollectService';      
   	}
	
	public function getVersion()
	{
		return self::CUBI_VERSION;
	}
	
	public function collectUserData($sendContact=1)
	{
		$method = "CollectUserData";
		$params = $this->getSystemUserData($sendContact);				
		$argsJson = urlencode(json_encode($params));
        $query = array(	"method=$method","format=json","argsJson=$argsJson");		        

		$httpClient = new HttpClient('POST');
		foreach ($query as $q)
		        $httpClient->addQuery($q);
		$headerList = array();
		$out = $httpClient->fetchContents($this->m_UDC_Server, $headerList);		        
		$cats = json_decode($out, true);
		$result = $cats['data'];
		return $result;
	}
	
	public function getSystemUserData($sendContact=1)
	{
		//sendContact = 0 ; don't send contact info
		//sendContact = 1 ; send contact info
		$contactRec = array();
		if($sendContact)
		{
			$profileId = BizSystem::getUserProfile("profile_Id");
	        $recArr = BizSystem::getObject("contact.do.ContactDO")->fetchById($profileId);
	        $contactRec['name'] 		= $recArr['display_name'];
	        $contactRec['company'] 		= $recArr['company'];
	        $contactRec['email'] 		= $recArr['email'];
	        $contactRec['mobile'] 		= $recArr['mobile'];
	        $contactRec['phone'] 		= $recArr['phone'];
		}
		$system_uuid 	= $this->getSystemUUID();
		$system_name 	= DEFAULT_SYSTEM_NAME;
		$system_language = DEFAULT_LANGUAGE;
		$system_url		= SITE_URL;		
		$system_cubi_ver	= $this->getVersion();
		$system_openbiz_ver	= BizSystem::getVersion();		
		$system_port	= $_SERVER['SERVER_PORT'];
		$system_admin	= $_SERVER['SERVER_ADMIN'];
		$internal_ip_address = $_SERVER['SERVER_ADDR'];
		
		if(function_exists("ioncube_server_data")){
			$server_data = ioncube_server_data();
		}else{
			$server_data = "";
		}
		
		$systemRec = array(
			"internal_ipaddr" => $internal_ip_address,
			"language" => $system_language,
			"system_name" => $system_name,
			"system_uuid" => $system_uuid,
			"system_url"  => $system_url,
			"system_admin" => $system_admin,
			"system_port" => $system_port,			
			"system_cubi_ver" => $system_cubi_ver,
			"system_openbiz_ver" => $system_openbiz_ver,			
			"system_server_data" => $server_data,
		);
		
		
		
		$params = array(
			"contact_data" => $contactRec,
			"system_data" => $systemRec
		);
		
		return $params;
	}
	
	public function GetSystemUUIDfromRemote()
	{
		$method = "CetSystemUUID";
		if(function_exists("ioncube_server_data")){
			$system_server_data = ioncube_server_data();
		}else{
			$system_server_data = "";
		}
		$params = array(
			"system_server_data" => $system_server_data
		);
		$argsJson = urlencode(json_encode($params));
        $query = array(	"method=$method","format=json","argsJson=$argsJson");		        

		$httpClient = new HttpClient('POST');
		foreach ($query as $q)
		        $httpClient->addQuery($q);
		$headerList = array();
		$out = $httpClient->fetchContents($this->m_UDC_Server, $headerList);		        
		$cats = json_decode($out, true);
		$result = $cats['data'];
		if($result)
		{
			$dataFile = APP_HOME.'/files/system_uuid.data';
			file_put_contents($dataFile,$result);
		}
		return $result;
	}	
	
	public function getSystemUUID()
	{		
		$uuid = $this->GetSystemUUIDfromRemote();
		if($uuid)
		{
			return $uuid;
		}
		
		$dataFile = APP_HOME.'/files/system_uuid.data';
		if(is_file($dataFile))
		{
			$uuid = file_get_contents($dataFile);
			$uuid = trim($uuid);
		}
		else
		{
			$uuid = uniqid('openbiz-cubi-');
			file_put_contents($dataFile,$uuid);	
		}
		return $uuid;
	}
}
?>