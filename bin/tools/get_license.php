#!/usr/bin/env php
<?php
/*
 * Cubi license acquisition
 */

include_once ("../app_init.php");
if(!defined("CLI")){
	exit;
}

$licenseClient = "service.licenseClient";

// get package service 
//echo "get license client service";
$licsvc = BizSystem::GetObject($licenseClient);

$activationCode = "hacq2b";
$contactEmail = "rocky@gmail.com";
$serverData = ""; //base64_encode(ioncube_server_data());

$license = $licsvc->acquireLicense($activationCode, $contactEmail, $serverData);
print_r($license);

?>