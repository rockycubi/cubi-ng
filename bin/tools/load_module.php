#!/usr/bin/env php
<?php
/*
 * load module command line script
 */

if ($argc<2) {
	echo "usage: php load_module.php module_name [-i]".PHP_EOL;
	echo "usage: '-i' indicates if install module sql which is usually for fresh new installation".PHP_EOL;
	exit;
}

include_once (dirname(dirname(__FILE__))."/app_init.php");
if(!defined("CLI")){
	exit;
}

//include_once dirname(__FILE__)."/require_auth.php";

include_once (MODULE_PATH."/system/lib/ModuleLoader.php");

$moduleName = $argv[1];
$opt1 = isset($argv[2]) ? $argv[2] : '';
$installSql = ($opt1 == "-i") ? true : false;


if($moduleName!="all" && $moduleName!=""){
	$moduleArr = array($moduleName);
}
else
{
	$_moduleArr = glob(MODULE_PATH."/*");
    $moduleArr[0] = "system";
    $moduleArr[1] = "menu";
	foreach ($_moduleArr as $_module)
	{
		$_module = basename($_module);
        if ($_module == "system" || $_module == "menu") continue;
        $moduleArr[] = $_module;	
	}
}

foreach ($moduleArr as $moduleName)
{
	$loader = new ModuleLoader($moduleName);
	echo "Start loading $moduleName module ...".PHP_EOL; 
	echo "--------------------------------------------------------".PHP_EOL;
	$loader->loadModule($installSql);
	echo $loader->errors . "".PHP_EOL;
	// give predefined users access to actions
	echo "Give admin to access all actions of module '$moduleName'".PHP_EOL;
	giveActionAccess("module='$moduleName'", 1);	// admin to access all actions
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