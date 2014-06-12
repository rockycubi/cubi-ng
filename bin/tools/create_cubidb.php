#!/usr/bin/env php
<?php
/*
 * 
 */
echo "****************************************************".PHP_EOL;
echo "This script is to create cubi installation database.".PHP_EOL;
echo "Please create a CubiInstall database in cubi/Config.xml.".PHP_EOL;
echo "Is CubiInstall created? (y/n) ";
// Read the input
$answer = trim(fgets(STDIN));
echo "Your answer is $answer".PHP_EOL;
if ($answer != 'y')
	exit;

include_once (dirname(dirname(__FILE__))."/app_init.php");
if(!defined("CLI")){
	exit;
}
include_once (MODULE_PATH."/system/lib/ModuleLoader.php");

$cubiInstallDb = "CubiInstall";

$db = BizSystem::dbConnection($cubiInstallDb);
if (!$db) {
	echo "Please create a CubiInstall database in cubi/Config.xml.".PHP_EOL;
}

$modules = array ('system','menu');
foreach (glob(MODULE_PATH.DIRECTORY_SEPARATOR."*") as $dir){
	$modName = str_replace(MODULE_PATH.DIRECTORY_SEPARATOR,"",$dir);
	if($modName != "system" && $modName !="menu"){
		array_push($modules,$modName);		
	}
}
// find all modules
foreach ($modules as $mod)
{
    echo PHP_EOL."---------------------------------------------------".PHP_EOL;
	echo "> Start loading '$mod' module ...".PHP_EOL;
	$loader = new ModuleLoader($mod, $cubiInstallDb);
    $loader->loadModule(true);
    echo "> End loading '$mod' module".PHP_EOL;
}

echo PHP_EOL."---------------------------------------------------".PHP_EOL;
// give predefined users access to actions
echo "> Give admin to access all actions ".PHP_EOL;
giveActionAccess("", 1);	// admin to access all actions
echo "> Give member access to user related actions ".PHP_EOL;
giveActionAccess("module='user'", 2);	// member access to user related actions

echo PHP_EOL."---------------------------------------------------".PHP_EOL;
echo "Cubi install database is created successfully!".PHP_EOL;

// give predefined users access to actions
function giveActionAccess($where, $role_id)
{
	global $db;
	try {
		$sql = "DELETE FROM acl_role_action WHERE role_id=$role_id";
		$db->query($sql);
		
		if (empty($where))
			$sql = "SELECT * FROM acl_action";
		else
			$sql = "SELECT * FROM acl_action WHERE $where";
	    BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
	    $rs = $db->fetchAll($sql);
	    
	    $sql = "";
		foreach ($rs as $r) {
			$sql = "INSERT INTO acl_role_action (role_id, action_id, access_level) VALUES ($role_id,$r[0],1)";
			BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
	    	$db->query($sql);
		}
	}
	catch (Exception $e) {
	    echo "ERROR: ".$e->getMessage()."".PHP_EOL;
	    return false;
	}
}

?>