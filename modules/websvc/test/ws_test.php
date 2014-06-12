<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.websvc.test
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ws_test.php 5452 2014-05-09 06:19:04Z rockyswen@gmail.com $
 */


/* 
 * Test web service API
 */

include_once 'httpClient.php';

$url="http://localhost/cubing/cubi/ws.php/user/userService";

echo "\n#######################";
echo "\n### Test xml format ###";
echo "\n#######################\n";
$argsJson = json_encode(array("username"=>"admin","password"=>"admin"));
$query = array(	"method=login","format=json",
				"argsJson=$argsJson");
testSvc($url, $query);
return;

echo "\n\n#######################";
echo "\n### Test xml format ###";
echo "\n#######################\n";
$argsJson = json_encode(array("searchrule"=>"[Id]=1","limit"=>10));
$query = array(	"username=admin","api_key=f79fe4a8bffcb044490a720a3295a842","secret=",
                "service=TicketService","method=fetch","format=json",
                "argsJson=$argsJson");
testSvc($url, $query);

function testSvc($url, $query=null, $cookie=null)
{
	$httpClient = new HttpClient('POST');
	$httpClient->setCookie($cookie);
	foreach ($query as $q)
		$httpClient->addQuery($q);
    
    $headerList = array();
	$out = $httpClient->fetchContents($url, $headerList, false);
	echo $out;
}

?>