<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   \
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ws.php 5473 2014-05-14 06:42:03Z rockyswen@gmail.com $
 */


/* cubi web service entry point
  request example:
  url
    http://host/cubi/ws.php/email/
  post
    service=EmailService
    method=sendEmail
    api_key=asdkjasdaslkj123123
    *secret=lksadasdklj23213129321lk3
    params[] ->
        toAddress = abc@mail.com
        emailBody = Hello
*/
//define("USE_CUSTOM_SESSION_HANDLER",true);  # why define session handler here?
include_once 'bin/app_init.php';
include_once OPENBIZ_HOME."/bin/ErrorHandler.php";

// find the module name and service name
$script = preg_quote("\\".$_SERVER['SCRIPT_NAME'],'/');
$pattern = "/^$script?\?\/?(.*?)(\.html)?$/si";
if(preg_match($pattern, $_SERVER['REQUEST_URI'],$match))
{
	//supports for http://localhost/?/user/login format
	//supports for http://localhost/index.php?/user/login format
	$url = $match[1];
}
elseif(strlen($_SERVER['REQUEST_URI'])>strlen($_SERVER['SCRIPT_NAME']))
{
	//supports for http://localhost/index.php/user/login format
    $pos = strpos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']);
	//$url = str_replace($_SERVER['SCRIPT_NAME'],"",$_SERVER['REQUEST_URI']);
    $url = substr($_SERVER['REQUEST_URI'], $pos+strlen($_SERVER['SCRIPT_NAME']));
	preg_match("/\/?(.*?)(\.html)?$/si", $url,$match);
	$url=$match[1];
}
$inputs = explode("/", $url);
$module = $inputs[0];
$service = isset($inputs[1]) ? $inputs[1] : $_REQUEST['service'];
if(isset($inputs[2]) && !preg_match("/^\?.*/si",$inputs[2])){
	//http://local.openbiz.me/ws.php/oauth/callback/login/?type=qzone
	$_REQUEST['method'] = $inputs[2];	
}
if(count($inputs)>=3)
{
	for($i=3;$i<count($inputs);$i++)
	{
		$param = $inputs[$i];
		if($param)
		{
			preg_match("/^(.*?)_(.*)$/s",$param,$match);
			$key = $match[1];
			$value = $match[2];
			$_REQUEST[$key]=$value;
		}
	}
}

OB_ErrorHandler::$errorMode = 'text';
if($module && $service){
if(!preg_match("/Service$/s",$service)){
	$service.="Service";
}
$websvc = $module.".websvc.".$service;
// get service object
$svcObj = BizSystem::getObject($websvc);

// invoke the method 
$svcObj->invoke();
}else{
	echo "Openbiz Webservice Ready!";
}
