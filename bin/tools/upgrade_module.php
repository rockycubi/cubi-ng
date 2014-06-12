#!/usr/bin/env php
<?php
/*
 * Cubi upgrade command line script
 * - first copy the new module source to /cubi/upgrade/modules/mod_name/ folder
 * - then run php /cubi/tool/upgrade.php mod_name
 */

if ($argc<2) {
	echo "usage: php upgrade_module.php module_name [version]".PHP_EOL;
	exit;
}

include_once ("../app_init.php");
if(!defined("CLI")){
	exit;
}

include_once (MODULE_PATH."/system/lib/ModuleLoader.php");

$moduleName = $argv[1];
$version = isset($argv[2]) ? $argv[2] : 'latest';

if($moduleName!="all" && $moduleName!=""){
	$moduleArr = array($moduleName);
}
else
{
	$moduleArr = glob(MODULE_PATH."/*");			
	for($i=0;$i<count($moduleArr);$i++)
	{
		$moduleArr[$i]=basename($moduleArr[$i]);	
	}
}

foreach ($moduleArr as $moduleName)
{
	$loader = new ModuleLoader($moduleName);
	echo "Start upgrading $moduleName module ...".PHP_EOL;
	echo "--------------------------------------------------------".PHP_EOL;
	$loader->upgradeModule();
	echo $loader->errors . "".PHP_EOL;
    
    // load the module again
    echo PHP_EOL."Reload module ...".PHP_EOL;
    $loader->loadModule($installSql);
	echo $loader->errors . "".PHP_EOL;
    
	// give predefined users access to actions
	echo "Give admin to access all actions of module '$moduleName'".PHP_EOL;
	//giveActionAccess("module='$moduleName'", 1);	// admin to access all actions
	echo "--------------------------------------------------------".PHP_EOL;
	echo "End loading $moduleName module".PHP_EOL;
	
}


// give predefined users access to actions
function giveActionAccess($where, $role_id)
{
	$db = BizSystem::dbConnection();
	try {
		if (empty($where))
			$sql = "SELECT * FROM acl_action";
		else
			$sql = "SELECT * FROM acl_action WHERE $where";
	    BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
	    $rs = $db->fetchAll($sql);
	    
	    $sql = "";
		foreach ($rs as $r) {
			$sql = "DELETE FROM acl_role_action WHERE role_id=$role_id AND action_id=$r[0]; ";
			BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
			$db->query($sql);
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