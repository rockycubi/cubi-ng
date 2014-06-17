<?php

function getSystemStatus()
{
	$status[0]['item'] = STR_OPERATION_SYSTEM;
	$status[0]['value'] = PHP_OS;
	$status[0]['status'] = 'OK';
	
	$status[1]['item'] = STR_PHP_VERSION;
	$status[1]['value'] = PHP_VERSION;
	$status[1]['status'] = version_compare(PHP_VERSION, "5.1.4") >= 0 ? 'OK' : STR_PHP_VERSION_FAIL;
	
	$status[2]['item'] = STR_OPENBIZ_PATH;
	$status[2]['value'] = OPENBIZ_HOME;
	$status[2]['status'] = "OK";
	if (!file_exists(OPENBIZ_HOME))
		$status[2]['status'] = STR_OPENBIZ_PATH_FAIL;
	
	$status[3]['item'] = STR_ZEND_PATH;
	$status[3]['value'] = defined('ZEND_FRWK_HOME') ? ZEND_FRWK_HOME : 'Undefined';
	if (defined('ZEND_FRWK_HOME') && !file_exists(ZEND_FRWK_HOME))
		$status[3]['status'] = STR_ZEND_PATH_FAIL;
	else if (defined('ZEND_FRWK_HOME') && file_exists(ZEND_FRWK_HOME)) {
		require_once 'Zend/Version.php';
        $status[3]['status'] = Zend_Version::compareVersion('1.0.0') < 0 ? 'OK - Version 1.0.0 or later is recommended' : 'FAIL';
    }
	else
		$status[3]['status'] = 'FAIL';
	
	/*if ($status[3]['status'] == 'OK')  {
		require_once 'Zend/Version.php';
		$status[4]['item'] = 'Zend Framework';
		$status[4]['value'] = Zend_Version::VERSION;
		$status[4]['status'] = Zend_Version::compareVersion('1.0.0') < 0 ? 'OK - Version 1.0.0 or later is recommended' : 'FAIL';
	}*/
	
	$status[5]['item'] = STR_PDO_EXTENSION;
	$pdos = array();
	if (extension_loaded('pdo')) $pdos[] = "pdo";
	if (extension_loaded('pdo_mysql')) $pdos[] = "pdo_mysql";
	if (extension_loaded('pdo_mssql')) $pdos[] = "pdo_mssql";
	if (extension_loaded('pdo_oci')) $pdos[] = "pdo_oci";
	if (extension_loaded('pdo_pgsql')) $pdos[] = "pdo_pgsql";
	$status[5]['value'] = implode(", ", $pdos);
	$status[5]['status'] = ($pdos[0]=='pdo' && $pdos[1]=='pdo_mysql') ? 'OK' : STR_PDO_EXTENSION_FAIL;
	//$status[5]['status'] = ($pdos[0]=='pdo') ? 'OK' : STR_PDO_EXTENSION_FAIL;
	return $status;
}

function getApplicationStatus()
{
	$status[0]['item'] = 'Resources path';
	$status[0]['value'] = RESOURCE_PATH;
	$status[0]['status'] = is_writable(RESOURCE_PATH) ? 'OK' : 'FAIL - not writable';
	
	$status[2]['item'] = 'Session path';
	$status[2]['value'] = SESSION_PATH;
	$status[2]['status'] = is_writable(SESSION_PATH) ? 'OK' : 'FAIL - not writable';
	
	//$status[3]['item'] = 'Smarty template path';
	//$status[3]['value'] = THEME_PATH."/default/template"; // SMARTY_TPL_PATH;
	//$status[3]['status'] = is_writable(THEME_PATH."/default/template") ? 'OK' : 'FAIL - not writable';
	
	$status[4]['item'] = 'Log path';
	$status[4]['value'] = LOG_PATH;
	$status[4]['status'] = is_writable(LOG_PATH) ? 'OK' : 'FAIL - not writable';
	
	$status[5]['item'] = 'Cache files path';
	$status[5]['value'] = APP_FILE_PATH;
	$status[5]['status'] = is_writable(APP_FILE_PATH) ? 'OK' : 'FAIL - not writable';
	
	return $status;
}

function connectDB($noDB=false) {
    require_once 'Zend/Db.php';

    // Automatically load class Zend_Db_Adapter_Pdo_Mysql and create an instance of it.
    $param = array(
        'host'     => $_REQUEST['dbHostName'],
        'username' => $_REQUEST['dbUserName'],
        'password' => $_REQUEST['dbPassword'],
        'port'     => $_REQUEST['dbHostPort'],
        'dbname'   => $_REQUEST['dbName']
    );
    if ($noDB) $param['dbname'] = '';
    
    try {
        $db = Zend_Db::factory($_REQUEST['dbtype'], $param);
        $conn = $db->getConnection();
    } catch (Zend_Db_Adapter_Exception $e) {
        // perhaps a failed login credential, or perhaps the RDBMS is not running
        echo 'ERROR: '.$e->getMessage(); 
        exit;
    } catch (Zend_Exception $e) {
        // perhaps factory() failed to load the specified Adapter class
        echo 'ERROR: '.$e->getMessage(); exit;
    }
    
    //if its connected then test is database empty
    if (!$noDB)
    {
	    $tables = $db->listTables();    
	    if(count($tables))
	    {
	    	echo 'ERROR: '.STR_DATABASE_NOT_EMPTY; exit;
	    }
    }
    return $conn;
}

function createDB() {
	// check if the application.xml is writable
    $app_xml = APP_HOME.'/application.xml';
    if (!is_writable($app_xml)) {
        echo "ERROR: please give file $app_xml write permission to web server user. Example of linux command: chmod a+w $app_xml";
        exit;
    }
    
    $conn = connectDB(true);
    
	try {
	   $conn->exec("CREATE DATABASE " . $_REQUEST['dbName']);
	}
	catch (Exception $e) {
	   echo 'ERROR: '.$e->getMessage(); exit;
   }
   unset($conn);

	$conn = connectDB();
    if (!$conn) {
		echo 'ERROR: Unable to create Database!';
		return;
	}
	
	replaceDbConfig();
	
    echo 'SUCCESS: Database '.$_REQUEST['dbName'].' is created';
}

function loadModules()
{   
	include_once (MODULE_PATH."/system/lib/ModuleLoader.php");

	$modules = array ('system','menu','help','contact','cronjob');
	foreach (glob(MODULE_PATH.DIRECTORY_SEPARATOR."*") as $dir){
		$modName = str_replace(MODULE_PATH.DIRECTORY_SEPARATOR,"",$dir);
		if(!in_array($modName, $modules)) {
			array_push($modules,$modName);		
		}
	}
    $logs = "";
	// find all modules	
	foreach ($modules as $mod)
	{
		$logs .= "Loading Module: $mod\n";
		$loader = new ModuleLoader($mod);
		$loader->debug=0;
	    $loader->loadModule(true);
        $logs .= $loader->logs;
        $logs .= $loader->errors;
        $logs .= "\n";
	}
   	giveActionAccess("", 1);	// admin to access all actions
	//giveActionAccess("module='user'", 2);
	file_put_contents(APP_FILE_PATH.'/install.log', $logs);
    echo "SUCCESS. Modules are loaded in Cubi. ###\n".$logs;
}

function giveActionAccess($where, $role_id)
{
	$db = BizSystem::dbConnection();
	try {
		$sql = "DELETE FROM acl_role_action WHERE role_id=$role_id";
		$db->query($sql);
		
		if (empty($where))
			$sql = "SELECT * FROM acl_action";
		else
			$sql = "SELECT * FROM acl_action WHERE $where";
	    BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
	    $stmt = $db->prepare($sql);
	    $stmt->execute();
	    $rs = $stmt->fetchAll();
	    unset($stmt);
	    
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

function replaceDbConfig()
{
	$conn = connectDB();
    if (!$conn) {
		echo 'ERROR: Unable to create Database!';
		return false;
	}
   $filename = APP_HOME.'/application.xml';
   $xml = simplexml_load_file($filename);
   $xml->DataSource->Database[0]['Driver'] = $_REQUEST['dbtype'];
   $xml->DataSource->Database[0]['Server'] = $_REQUEST['dbHostName'];
   $xml->DataSource->Database[0]['User'] = $_REQUEST['dbUserName'];
   $xml->DataSource->Database[0]['Password'] = $_REQUEST['dbPassword'];
   $xml->DataSource->Database[0]['DBName'] = $_REQUEST['dbName'];
   $xml->DataSource->Database[0]['Port'] = $_REQUEST['dbHostPort'];
   if (isset($xml->DataSource->Database[0]['Status'])) {
      $xml->DataSource->Database[0]['Status'] = 1;
   }
   $fp = fopen ($filename, 'w');
   if (fwrite($fp, $xml->asXML()) === FALSE) {
        echo "ERROR: Cannot write to file ($filename)";
		return false;
   }
    fclose($fp);
    //showDBConfig();
    echo "SUCCESS";
    return true;
}

function loadDBConfig(){
   $filename = APP_HOME.'/application.xml';
   $xml = simplexml_load_file($filename);
   $_REQUEST['dbtype'] = $xml->DataSource->Database[0]['Driver'];
   $_REQUEST['dbtype'] = $xml->DataSource->Database[0]['Server'];
   $_REQUEST['dbUserName'] = $xml->DataSource->Database[0]['User'];
   $_REQUEST['dbPassword'] = $xml->DataSource->Database[0]['Password'] ;
   $_REQUEST['dbName'] = $xml->DataSource->Database[0]['DBName'] ;
   $_REQUEST['dbHostPort'] = $xml->DataSource->Database[0]['Port'] ;
   $_REQUEST['create_db'] = "N";
   return true;
}

function getDefaultDB()
{
	$filename = APP_HOME.'/application.xml';
   	$xml = simplexml_load_file($filename);
   	$db['Name'] = $xml->DataSource->Database[0]['Name'];
   	$db['Driver'] = $xml->DataSource->Database[0]['Driver'];
   	$db['Server'] = $xml->DataSource->Database[0]['Server'];
   	$db['User'] = $xml->DataSource->Database[0]['User'];
   	$db['Password'] = $xml->DataSource->Database[0]['Password'];
   	$db['DBName'] = $xml->DataSource->Database[0]['DBName'];
   	$db['Port'] = $xml->DataSource->Database[0]['Port'];
   	return $db;
}

function showDBConfig()
{
   $xml = simplexml_load_file(APP_HOME.'/application.xml');
   //print_r($xml);
   echo "<b>Current setting of Default Database:</b>";
   echo '<table><tr>';
   echo '<th>Name</th><th>Driver</th><th>Server</th><th>Port</th><th>DBName</th><th>User</th><th>Password</th></tr>';
   echo '<tr>';
   echo '<td>'.$xml->DataSource->Database[0]['Name'].'</td>';
   echo '<td>'.$xml->DataSource->Database[0]['Driver'].'</td>';
   echo '<td>'.$xml->DataSource->Database[0]['Server'].'</td>';
   echo '<td>'.$xml->DataSource->Database[0]['Port'].'</td>';
   echo '<td>'.$xml->DataSource->Database[0]['DBName'].'</td>';
   echo '<td>'.$xml->DataSource->Database[0]['User'].'</td>';
   echo '<td>'.$xml->DataSource->Database[0]['Password'].'</td>'; 
   echo '</tr></table>';  
}
?>