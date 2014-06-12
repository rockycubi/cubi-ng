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
 * @version   $Id: ModuleLoader.php 5328 2013-03-27 06:59:19Z rockyswen@gmail.com $
 */

include_once(MODULE_PATH."/common/lib/fileUtil.php");

// install openbiz modules

class ModuleLoader
{
    public $name;
    public $dbName;
	public $errors;
    public $logs;
    public $debug = 1;
    
    public function __construct($name, $dbName=null)
    {
    	$this->name = $name;
    	$this->dbName = $dbName;
    }
    
    public function DBConnection()
    {
    	return BizSystem::dbConnection($this->dbName);
    }
    
    public function loadModule($installSql=false)
    {
		$this->log("Loading module ".$this->name);
        $module = $this->name;
		$modfile = MODULE_PATH."/$module/mod.xml";
        if (!file_exists($modfile)) {
        	$this->errors = "$module is not loaded, mod.xml is not found in $module.";	
        	return false;
    	}
    	if (($db = $this->DBConnection()) == null) {
    		$this->errors = "ERROR: Cannot get database connection.";	
        	return false;
    	}
        
        // invoke custom beforeLoadingModule method
        $this->invokeLoadHandler("beforeLoadingModule");

    	// dependency check
    	$depModules = $this->checkDependency();
    	$depCount = 0;
    	foreach ($depModules as $mod=>$val) {
    		if ($val == 1) {
    			$this->errors = "Dependent module $mod is NOT loaded.";
    			$depCount++;
    		}
    	}
    	if ($depCount > 0)
    		return false;
		
	    // install mod.sql
	    if ($installSql) {
        	if (!$this->installModuleSql())
            	return false;
	    }
	    else {
	    	if (!self::isModuleInstalled($module))	// check if the module has been installed
	    		if (!$this->installModuleSql())	// if not, install it anyway
            		return false;
	    }
        
    	// install mod.xml
        if (!$this->installModule())
            return false;
        /*
        // load metadata. Only needed by cubi studio (not yet implemented) 
        $this->installMetaDo();
        $this->installMetaForm();
        $this->installMetaView();
        */
        
        // copy resource files to proper folders
        $this->copyResourceFiles();
        
        //give permission to role 1
        $this->giveActionAccess(1);
        
        // invoke custom beforeLoadingModule method
        $this->invokeLoadHandler("postLoadingModule");
        
        $this->log("$module is loaded.");
        //clear Module Cache DO
        BizSystem::getObject("system.do.ModuleCachedDO")->cleanCache();        
        return true;
    }
    
    protected function invokeLoadHandler($event)
    {
        $modfile = MODULE_PATH."/".$this->name."/mod.xml";
        $xml = simplexml_load_file($modfile);
        if (!isset($xml['LoadHandler']) && !empty($xml['LoadHandler'])) return;
        $modLoadHandler = $xml['LoadHandler'];
        $dotPos = strrpos($modLoadHandler, ".");
        $package = $dotPos>0 ? substr($modLoadHandler, 0, $dotPos) : null;
        $class = $dotPos>0 ? substr($modLoadHandler, $dotPos+1) : $modLoadHandler;
        if (BizSystem::loadClass($class, $package)) {
            $loadHandler = new $class();
            switch($event)
            {
            	case "beforeLoadingModule":
            	case "postLoadingModule":
            	case "beforeUnloadModule":
            	case "postUnloadModule":
            		$loadHandler->$event($this);
            		break;
            }
        }
    }
    
	public function giveActionAccess($role_id)
	{
		
		$db = BizSystem::dbConnection();
		try {
			$where = "module='".$this->name."'"; 
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
    
	public function loadChangeLog()
    {
		$module = $this->name;
		$modfile = MODULE_PATH."/$module/mod.xml";
        if (!file_exists($modfile)) {
        	$this->errors = "$module is not loaded, mod.xml is not found in $module.";	
        	return false;
    	}
    	if (($db = $this->DBConnection()) == null) {
    		$this->errors = "ERROR: Cannot get database connection.";	
        	return false;
    	}

    	$modfile = MODULE_PATH."/".$this->name."/mod.xml";        
    	$xml = simplexml_load_file($modfile);
    	
        if (!$this->installChangeLog($xml))
            return false;
                    
        return true;
    }
    
    public function unLoadModule()
    {
		$module = $this->name;
    	$db = $this->DBConnection();
		$this->invokeLoadHandler("beforeUnloadModule");
		
    	// check all modules depending on this module
    	try {
    		$sql ="SELECT name FROM module WHERE depend_on LIKE '%$module%'";
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
            $rs = $db->fetchAll($sql);
        }
        catch (Exception $e) {
            $this->errors = $e->getMessage();
            return false;
        }
        if ($rs && count($rs)>0) {
        	foreach ($rs as $r) {
        		$mods[]= $r[0][0];
        	}
        	$modList = implode(",",$mods);
        	$this->log("The module cannot be unloaded because it module '$modList' depending on it.");
        	return false;
        }
		
		// delete all records
    	try {
    		$sql ="DELETE FROM menu WHERE module='$module'; ";
    		$sql .="DELETE FROM meta_view WHERE module='$module'; ";
    		$sql .="DELETE FROM meta_form WHERE module='$module'; ";
    		$sql .="DELETE FROM meta_do WHERE module='$module'; ";
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
            $db->query($sql);
        }
        catch (Exception $e) {
            $this->errors = $e->getMessage();
            return false;
        }
    	
    	// uninstall Sql
    	$this->log("Install Module Sql.");
    	$sqlfile = MODULE_PATH."/".$this->name."/mod.uninstall.sql";
        if (!file_exists($sqlfile))
        	return true;
        
    	// Getting the SQL file content        
        $query = file_get_contents($sqlfile);
		try {
	    	$db->exec($query);
	    } catch (Exception $e) {
	        $this->errors = $e->getMessage();	        
	   	}
	   	
	   	$this->invokeLoadHandler("postUnloadModule");
	   	return;
    }
    
    public function upgradeModule($forceUpgrade=false)
    {
        $module = $this->name;
    	$db = $this->DBConnection();
        
        $modFolder = MODULE_PATH."/".$this->name;
        $upgradeFolder = APP_HOME."/upgrade/modules/".$this->name;
        $backupFolder = APP_HOME."/backup/modules/".$this->name;
        
        // read in mod.xml
        $modfile = $modFolder."/mod.xml";
        if(file_exists($modfile))
        {
        	$xml = simplexml_load_file($modfile);
        }else{
    		$xml['Version'] = 0;
        }
        
        $upgradeModfile = $upgradeFolder."/mod.xml";
        if (!file_exists($upgradeModfile)) {
            $this->errors = "Cannot find upgrade module source $upgradeModfile.";
            return false;
        }
        $u_xml = simplexml_load_file($upgradeModfile);
        
        // get the version
        $ver = $xml['Version'];
        $u_ver = $u_xml['Version'];
        
        // check if upgrade folder has new source and the new source has higher version than current module
        if (version_compare($u_ver, $ver) <= 0 && $forceUpgrade==false) {
            $this->errors = "The upgrade module does not have higher version ($u_ver) than current module ($ver).";
            return false;
        }
        if(CLI) {
            // ask user to backup the module and confirm the upgrade
            echo "Upgrade '$module' module from version $ver to $u_ver. Please backup data first.".PHP_EOL;
            echo "Press enter to continue ... ";
            $selection = trim(fgets(STDIN));
        }
        // backup the current source to /cubi/backup/modules/mod_name/version
        $backupFolder = $backupFolder."/$ver";
        if(CLI) echo PHP_EOL."Backup source files to $backupFolder ...".PHP_EOL;
        $this->log("Backup source files to $backupFolder ...");
        recurse_copy($modFolder, $backupFolder);
        
        // copy the source first
        if(CLI) echo PHP_EOL."Copy source files from $upgradeFolder to $modFolder...".PHP_EOL;
        $this->log("Copy source files from $upgradeFolder to $modFolder...");
        recurse_copy($upgradeFolder, $modFolder);
        
        // run the right upgrade sql
        if(CLI) echo PHP_EOL."Execute upgrade sql files ...".PHP_EOL;
        $this->log("Execute upgrade sql files ...");
        $this->upgradeSQLs($ver, $u_ver);
        return true;
    }
    
    static public function isModuleInstalled($module, $dbName=null)
    {

    	$db = BizSystem::DBConnection($dbName);
        $sql = "SELECT * from module where name='$module'";
        try {
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
            $rs = $db->fetchAll($sql);
        }
        catch (Exception $e)
        {
            //$this->errors = $e->getMessage();
            var_dump($e->getMessage());
            return false;
        }
        if (count($rs)>0) {
        	return true;
        }
        return false;
    }
    
	static public function isModuleOld($module, $dbName=null)
    {

    	$db = BizSystem::DBConnection($dbName);
        $sql = "SELECT * from module where name='$module'";
        try {
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
            $rs = $db->fetchAll($sql,array(),Zend_Db::FETCH_ASSOC);
        }
        catch (Exception $e)
        {
            //$this->errors = $e->getMessage();
            var_dump($e->getMessage());
            return true;
        }
        if (count($rs)>0) {
        	$installed_version = $rs[0]['version'];
        }else{
        	return true;
        }
        
        
        $modFolder = MODULE_PATH."/".$module;
        $modfile = $modFolder."/mod.xml";
    	$xml = simplexml_load_file($modfile);        
        $mod_ver = $xml['Version'];
                
    	if (version_compare($installed_version, $mod_ver) < 0) {
            return true;
        }
        return false;
    }
    
    protected function upgradeSQLs($baseVersion, $targetVersion)
    {
        include_once (MODULE_PATH."/system/lib/MySQLDumpParser.php");
        $db = $this->DBConnection();
        
        //$upgradeFolder = APP_HOME."/upgrade/modules/".$this->name;
        //$upgradeFile = $upgradeFolder."/upgrade.xml";
        $upgradeFile = MODULE_PATH."/".$this->name."/upgrade.xml";
        if(!is_file($upgradeFile))
        {
        	return;
        }
        // read upgrade.xml
        $xml = simplexml_load_file($upgradeFile, 'SimpleXMLElement', LIBXML_NOCDATA);
        $versions = $xml->Version;
        $start = false;
        foreach ($versions as $v) {
            $ver = $v['Name'];
            if (version_compare($baseVersion, $ver) < 0 && version_compare($targetVersion, $ver) >= 0) {
                $UpgradeSql = $v->UpgradeSql;
                if (!$UpgradeSql) continue;
                if (CLI) echo "Upgrade from version $baseVersion to $ver ...".PHP_EOL;
                $this->log("Upgrade from version $baseVersion to $ver ...");
                
                //$db->exec($UpgradeSql);
                $queryArr = MySQLDumpParser::parse($UpgradeSql);
                foreach($queryArr as $query){
                    try {
                        if (CLI) echo "Execute #$query#".PHP_EOL;
                        $this->log("Execute #$query#");
                        $db->exec(trim($query));
                        //$db->exec("ALTER TABLE  `help` ADD `add1` varchar(255) default NULL AFTER `content`");
                    } catch (Exception $e) {
                        $this->errors = $e->getMessage();
                        $this->log($e->getMessage());
                        return false;
                    }
                }
            }
        }
    }
    
    protected function checkDependency()
    {
    	$modfile = MODULE_PATH."/".$this->name."/mod.xml";
        
    	$xml = simplexml_load_file($modfile);
    	
    	$depModules = array();
    	if (isset($xml->Dependency) && isset($xml->Dependency->Module))
    	{
    		foreach ($xml->Dependency->Module as $mod)
    		{
    			$modName = trim($mod['Name']);
    			if (!self::isModuleInstalled($modName,$this->dbName))
    				$depModules[$modName] = 1;
    			else {
    			 	$depModules[$modName] = 0;
    			}
    		}
    	}
    	return $depModules;
    }
    
    protected function installModuleSql()
    {
        $this->log("Install Module Sql.");
    	$sqlfile = MODULE_PATH."/".$this->name."/mod.install.sql";
        if (!file_exists($sqlfile))
        	return true;
        
    	// Getting the SQL file content        
        $query = trim(file_get_contents($sqlfile));
        if (empty($query))
        	return true;

        $db = $this->DBConnection();
        include_once (MODULE_PATH."/system/lib/MySQLDumpParser.php");
        
        $queryArr = MySQLDumpParser::parse($query);
        foreach($queryArr as $query){
			try {
		    	$db->exec($query);
		    } catch (Exception $e) {
		        $this->errors = $e->getMessage();
		        $this->log($e->getMessage());
		        return false;
		   	}
	    }
	   	return true;
    }
        
    protected function installModule($forceInstall=true)
    {
        $this->log("Install Module ".$this->name);
    	$modfile = MODULE_PATH."/".$this->name."/mod.xml";
        
    	$xml = simplexml_load_file($modfile);
        
        $db = $this->DBConnection();
        
        // write mod info in module table
        $modName = $xml['Name'];
        $modDesc = $xml['Description'];
        $modAuthor = $xml['Author'];
        $modVersion = $xml['Version'];
        $modObVersion = $xml['OpenbizVersion'];
        $depModules = $this->checkDependency();
        $depModString = implode(",",array_keys($depModules));
        $sql = "SELECT id, name, version from module where name='$modName'";
        try {
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
            $rs = $db->fetchAll($sql);
        }
        catch (Exception $e) {
            $this->errors = $e->getMessage();
            return false;
        }
        
        // if the installed version is newer than (or equal to) the local module, only do "installResource"
        $skipDBChanges = false;
        if (count($rs)>0) {
            $record = $rs[0];
            $version = $record[2];
            if (version_compare($modVersion, $version) <= 0 && $forceInstall==false) {
                $this->errors = "NOTE: The upgrade module does not have higher version ($modVersion) than current module ($version).";
                $skipDBChanges = true;
            }
        }
        
        if (count($rs)>0)
            $sql = "UPDATE module SET description='$modDesc', version='$modVersion', author='$modAuthor', openbiz_version='$modObVersion' WHERE name='$modName'";
        else
            $sql = "INSERT INTO module (name, description, version, author, openbiz_version, depend_on) VALUES ('$modName','$modDesc','$modVersion','$modAuthor','$modObVersion','$depModString');";
        try {
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
            $db->query($sql);
        }
        catch (Exception $e) {
            $this->errors = $e->getMessage();
            return false;
        }
        
        // install ACL
        //if (!$skipDBChanges) 
        // about ACL changes install it anyway
        $this->installACL($xml);
        
        // install Menu
        //if (!$skipDBChanges)
        //install it anyway
         $this->installMenu($xml);
        
        // install widget
        if (!$skipDBChanges) $this->installWidgets($xml);
		
		// install event observer
        if (!$skipDBChanges) $this->installEventObservers($xml);
        
        // install resource
        $this->installResource($xml);
        
        if (!$skipDBChanges) $this->installChangeLog($xml);
        
        if (!$skipDBChanges) $this->installModuleAsPackage($xml);
        
        // invoke upgrade SQL 
        if($version){//if installed, then upgrade, if not installed just fresh install, dont process upgradesql
        	if (!$skipDBChanges) $this->upgradeSQLs($version, $modVersion);
        }
        
        return true;
    }
    
    protected function installModuleAsPackage($xml)
    {
        $db = $this->DBConnection();
        try {
            $tblDesc = $db->describeTable("package_local");
        }
        catch (Exception $e)  {
            return;
        }
        
        $this->log("Install Module as a Package.");
        
        // write mod info in package table
        $modName = $xml['Name'];
        $modDesc = $xml['Description'];
        $modAuthor = $xml['Author'];
        $modVersion = $xml['Version'];
        $modObVersion = $xml['OpenbizVersion'];
        $depModules = $this->checkDependency();
        $depModString = implode(",",array_keys($depModules));
        $sql = "SELECT * from package_local where name='$modName'";
        try {
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
            $rs = $db->fetchAll($sql);
        }
        catch (Exception $e) {
            $this->errors = $e->getMessage();
            return false;
        }
        $package_id = "cubi.module.".$modName;
        $modName = "Cubi $modName Module";
        if (count($rs)>0)
            $sql = "UPDATE package_local SET package_id='$package_id', type='Module', category='Cubi Module', description='$modDesc', version='$modVersion', inst_version='$modVersion', author='$modAuthor', status=1, pltfm_ver='$modObVersion' WHERE name='$modName'";
        else {
            $sql = "INSERT INTO package_local (package_id, name, type, category, description, version, inst_version, author, status, pltfm_ver) VALUES ('$package_id', '$modName','Module','Cubi Module','$modDesc','$modVersion','$modVersion','$modAuthor',1,'$modObVersion');";
        }
        try {
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
            $db->query($sql);
        }
        catch (Exception $e) {
            $this->errors = $e->getMessage();
            return false;
        }
    }
    
    protected function installResource($xml)
    {
    	$this->log("Install Module Resource.");
    	$module = $this->name;
        
        if (isset($xml->Files) && isset($xml->Files->Copy)) {
            foreach ($xml->Files->Copy as $copy) {
                // echo "Copy ".MODULE_PATH.'/'.$this->name.'/'.$copy['From'].' > '.APP_HOME.'/'.$copy['ToDir'].PHP_EOL;
                $toDirs = glob(APP_HOME.'/'.$copy['ToDir']);
                //print_r($toDirs);
                $fromFiles = glob(MODULE_PATH.'/'.$this->name.'/'.$copy['From']);
                //print_r($fromFiles);
                foreach ($toDirs as $dir) {
                    foreach ($fromFiles as $file) {
                        //echo "copy $file to $dir/".basename($file).PHP_EOL;
                        copy($file, $dir.'/'.basename($file));
                    }
                }
            }
        }
    }
    
    protected function installChangeLog($xml)
    {
    	$this->log("Install Module Change Logs.");
    	$module_name = $xml['Name'];
    	
    	if(!isset($xml->ChangeLog->Version))
    	{
    		return true;
    	}
    	
    	foreach($xml->ChangeLog->Version as $version)
    	{
    		
    		$version_name = (string)$version['Name'];
    		if(isset($version->Change))
    		{
    			$changelogDO = BizSystem::GetObject("system.do.ModuleChangeLogDO");
    			foreach($version->Change as $change)
    			{
    				$changelogRec = array();
    				
    				$changelogRec['module']			=	(string)$module_name;
    				$changelogRec['name']			=	(string)$change['Name'];
    				$changelogRec['description']	=	(string)$change['Description'];
    				$changelogRec['status']			=	(string)$change['Status'];
    				$changelogRec['version']		=	(string)$version_name;
    				$changelogRec['type']			=	(string)$change['Type'];
    				$changelogRec['publish_date']	=	(string)$change['PublishDate'];
    				try{
	    				if(strtolower($changelogRec['status'])!=''){
	    					$oldRec = $changelogDO->fetchOne("[name]='".$changelogRec['name']."'");
	    					if($oldRec)
	    					{
	    						$changelogRec['Id']= $oldRec['Id'];
	    						$changelogDO->updateRecord($changelogRec,$oldRec);
	    					}
	    					else
	    					{
	    						$changelogDO->insertRecord($changelogRec);	
	    					}
	    				}
    				}catch (Exception $e)
    				{
    					//var_dump($e->getMessage());
    				}
    			}
    		}
    	}
    }
    
    protected function installMenu($xml)
    {
    	$this->log("Install Module Menu.");
    	$module = $this->name;
    	if (isset($xml->Menu) && isset($xml->Menu->MenuItem))
    	{
	    	// delete all menu item first
	    	$db = $this->DBConnection();
            $sql = "DELETE FROM menu WHERE module='$module'";
	        try {
	            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
	            $db->query($sql);
	        }
	        catch (Exception $e) {
	            $this->errors = $e->getMessage();
	            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $this->errors." $sql");
	            return false;
	        }
	        //clean menu obj cache
	        $menuTreeObj = BizSystem::getObject("menu.do.MenuTreeDO");
			$menuTreeObj->CleanCache();	
			
			$menuObj = BizSystem::getObject("menu.do.MenuDO");
			$menuObj->CleanCache();
			
            foreach ($xml->Menu->MenuItem as $m) {
            	if ($this->loadMenuItem($m) == false) return false;
            } 
    	}
    	return true;
    }
    
 
    
    protected function loadMenuItem($menuItem, $parentMenuName='')
    {
    	$module = $this->name;
    	$db = $this->DBConnection();
    	$name = $menuItem['Name'];
    	$title = $menuItem['Title'];
    	$link = $menuItem['URL'];
    	$url_match = $menuItem['URLMatch'];
        $access = $menuItem['Access'];
    	$order = isset($menuItem['Order']) ? $menuItem['Order'] : 10;
    	if (isset($menuItem['Parent']) && $menuItem['Parent']!="")
    		$parentMenuName = $menuItem['Parent'];
    	// IconImage and IconCssClass
    	$icon = $menuItem['IconImage'];
    	$icon_css = $menuItem['IconCssClass']; 
    	$description = $menuItem['Description'];   	
    	
    	$sql = "DELETE FROM menu WHERE name='$name' ";
    	$db->query($sql);
    	
    	$sql = "INSERT INTO menu (`name`,description,module,title,link,url_match,parent,access,ordering,icon,icon_css,published) ";
    	$sql .= "VALUES ('$name','$description','$module','$title','$link','$url_match','$parentMenuName','$access','$order','$icon','$icon_css','1');";
    	try {
        	//BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
            $db->query($sql);
        }
        catch (Exception $e) {
        	$this->errors = $e->getMessage();
        	echo $e->getMessage();
            return false;
        }
        foreach ($menuItem->MenuItem as $m)
        {
        	if ($this->loadMenuItem($m,$name) == false) return false;
        }
        return true;
    }
    
    protected function installWidgets($xml)
    {
    	$this->log("Install Module Widget.");
    	$module = $this->name;
    	if (isset($xml->Widgets) && isset($xml->Widgets->Widget))
    	{
	    	// delete all menu item first
	    	$db = $this->DBConnection();
            $sql = "DELETE FROM widget WHERE module='$module'";
	        try {
	            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
	            $db->query($sql);
	        }
	        catch (Exception $e) {
	            $this->errors = $e->getMessage();
	            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $this->errors." $sql");
	            return false;
	        }
	        //clean  obj cache
			$menuObj = BizSystem::getObject("system.do.WidgetDO");
			$menuObj->CleanCache();
			
            foreach ($xml->Widgets->Widget as $m) {            	
            	if ($this->loadWidget($m) == false) return false;
            } 
    	}
    	return true;
    }

    protected function loadWidget($widget,$moduleName='')
    {
		$module 	= $this->name;
    	$db 		= $this->DBConnection();
    	$name 		= (string)$widget['Name'];
    	$title 		= (string)$widget['Title'];
    	$description= (string)$widget['Description'];   	
    	$sortorder	= (string)$widget['Order'];
    	
    	
    	$configable = BizSystem::getObject($name)->configable;
    	$configable = (int)$configable;

    	$do = BizSystem::getObject("system.do.WidgetDO");
    	$recArr = array(
    		"name"		=>$name,	
    		"module"	=>$module,
    		"title"		=>$title,
    		"description"=>$description,
    		"sortorder"	=>$sortorder,
    		"configable"=>$configable,
    		"published"	=>1,    		
    	);
    	    	    	
    	try{
		$do->insertRecord($recArr);    	
    	}catch (Exception $e)
    	{
    		var_dump($e->getMessage());
    	}
        return true;
    }
		
	protected function installEventObservers($xml)
	{
		$this->log("Install Module EventObservers.");
    	$module = $this->name;
    	if (isset($xml->EventObservers) && isset($xml->EventObservers->Observer))
    	{
	    	// delete all menu item first
	    	$db = $this->DBConnection();
            $sql = "DELETE FROM event_observer WHERE module='$module'";
	        try {
	            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
	            $db->query($sql);
	        }
	        catch (Exception $e) {
	            $this->errors = $e->getMessage();
	            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $this->errors." $sql");
	            return false;
	        }
	        //clean  obj cache
			$obsObj = BizSystem::getObject("eventmgr.do.EventObserverDO");
			$obsObj->CleanCache();
			
            for ($i=0; $i<count($xml->EventObservers->Observer); $i++) {
				$m = $xml->EventObservers->Observer[$i];
            	if ($this->loadObserver($m) == false) return false;
            } 
    	}
    	return true;
	}
	
	protected function loadObserver($observer,$moduleName='')
	{
		$module 	= $this->name;
    	$db 		= $this->DBConnection();
    	$name 		= (string)$observer['Name'];
		$observer_name = (string)$observer['ObserverName'];
    	$event_target = (string)$observer['EventTarget'];
    	$event_name	= (string)$observer['EventName'];   	
    	$priority	= $observer['Priority'];

    	$do = BizSystem::getObject("eventmgr.do.EventObserverDO");
    	$recArr = array(
    		"name"		=>$name,
			"observer_name"	=>$observer_name,
    		"module"	=>$module,
    		"event_target"	=>$event_target,
    		"event_name"=>$event_name,
    		"priority"	=>$priority,
    		"status"	=>1,    		
    	);
	    	
    	try{
		$do->insertRecord($recArr);    	
    	}catch (Exception $e)
    	{
    		var_dump($e->getMessage());
    	}
        return true;
	}
    
    protected function installACL($xml)
    {
    	$this->log("Install Module ACL.");
    	$modName = $this->name;
    	if (isset($xml->ACL) && isset($xml->ACL->Resource))
        {
			$db = $this->DBConnection();
        	// write mod/acl in acl_action table
            foreach ($xml->ACL->Resource as $res)
            {
                $resName = $res['Name'];
                foreach ($res->Action as $act)
                {
                    $actName = $act['Name'];
                    $actDesc = $act['Description'];
                    $sql = "SELECT * FROM acl_action WHERE module='$modName' AND resource='$resName' AND action='$actName'";
                    try {
                        //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
                        $rs = $db->fetchAll($sql);
                        
                        if (count($rs)>0) {
                        	$actionIds[] = $rs[0][0];
                        	$sql = "UPDATE acl_action SET description='$actDesc' WHERE module='$modName' AND resource='$resName' AND action='$actName'";
                        	//BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
                        	$db->query($sql);
                        }
	                    else {
    	                    $insertSqls[] = "INSERT INTO acl_action (module, resource, action, description) VALUES ('$modName', '$resName','$actName', '$actDesc');";
	                    }
                    }
                    catch (Exception $e) {
                        $this->errors = $e->getMessage();
                        return false;
                    }
                }
            }
            if (isset($actionIds)) {
	            // delete old records from acl_role_action and acl_action who are not in the action list
	            $actionIdList = implode(",", $actionIds);
				$sql = "SELECT * FROM acl_action WHERE module='$modName' AND id NOT IN ($actionIdList)";
	        	try {
	        	    //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
	    			$rs = $db->fetchAll($sql);
					if (count($rs)>0) {
						foreach ($rs as $r)
	                		$delIds[] = $r[0];
						$delIdList = implode(",",$delIds);
						$sql = "DELETE FROM acl_role_action WHERE action_id IN ($delIdList)";
						//BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
		                $db->query($sql);
		                $sql = "DELETE FROM acl_action WHERE id IN ($delIdList)";
						//BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
		                $db->query($sql);
					}
				}
				catch (Exception $e) {
				    $this->errors = $e->getMessage();
				    return false;
				}
            }
			
			// insert new records
			if (isset($insertSqls) && count($insertSqls)>0) {
				foreach ($insertSqls as $sql) {
					try {
						//BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
	                    $db->query($sql);
					}
					catch (Exception $e) {
					    $this->errors = $e->getMessage();
					    return false;
					}
				}
			}
        }
    }
    
    protected function copyResourceFiles()
    {
        $this->log("Copy resource files to /cubi/resources folder.");
    	$module = $this->name;
    	$modulePath = MODULE_PATH."/$module";
        $resourceFolder = $modulePath."/resource";
        $targetFolder = APP_HOME."/resources/$module";
        
        // copy resource/* to /cubi/resources/module_name/
        recurse_copy($resourceFolder, $targetFolder);
    }
    
    protected function installMetaDo()
    {
    	$this->log("Install Module DO metadata.");
    	$module = $this->name;
    	$modulePath = MODULE_PATH."/$module";
    	global $g_MetaFiles;
    	$g_MetaFiles = array();
        php_grep("<BizDataObj", $modulePath);
        if (empty($g_MetaFiles))
        	return;
        
        $db = $this->DBConnection();
    	$sql = "DELETE FROM meta_do WHERE module='$module'";
        try {
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
            $db->query($sql);
        }
        catch (Exception $e) {
            $this->errors = $e->getMessage();
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $this->errors." $sql");
            return false;
        }
        foreach ($g_MetaFiles as $metaFile)
        {
            $metaName = str_replace('/','.',str_replace(array(MODULE_PATH.'/','.xml'),'', $metaFile));
	    	// load do
	    	$xml = simplexml_load_file($metaFile);

	        // write mod info in module table
	        $name = $xml['Name'];
	        $class = $xml['Class'];
	        $dbName = $xml['DBName'];
	        $table = $xml['Table'];
	        $data = addslashes(file_get_contents($metaFile));
	        unset($fields); $fields = array();
	        if (!isset($xml->BizFieldList) || !isset($xml->BizFieldList->BizField))
	        	continue;
	        foreach ($xml->BizFieldList->BizField as $fld)
	        	$fields[] = $fld['Name'];
	        $fieldStr = implode(',',$fields);
	    	$sql = "INSERT INTO meta_do (`name`,`module`,`class`,`dbname`,`table`,`data`,`fields`) 
	    			VALUES ('$metaName','$module','$class','$dbName','$table','$data','$fieldStr');";
	        try {
	            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
	            $db->query($sql);
	        }
	        catch (Exception $e) {
	            $this->errors = $e->getMessage();
	            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $this->errors." $sql");
	            return false;
	        }
        }
    }

	protected function installMetaForm()
    {
    	$this->log("Install Module Form metadata.");
    	$module = $this->name;
    	$modulePath = MODULE_PATH."/$module";
    	global $g_MetaFiles;
    	$g_MetaFiles = array();
        php_grep("<EasyForm", $modulePath);
        if (empty($g_MetaFiles))
        	return;
        
        $db = $this->DBConnection();
    	$sql = "DELETE FROM meta_form WHERE module='$module'";
        try {
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
            $db->query($sql);
        }
        catch (Exception $e) {
            $this->errors = $e->getMessage();
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $this->errors." $sql");
            return false;
        }
        foreach ($g_MetaFiles as $metaFile)
        {
            $metaName = str_replace('/','.',str_replace(array(MODULE_PATH.'/','.xml'),'', $metaFile));
	    	// load do
	    	$xml = simplexml_load_file($metaFile);

	        // write mod info in module table
	        $name = $xml['Name'];
	        $class = $xml['Class'];
	        $dataobj = $xml['BizDataObj'];
	        $template = $xml['TemplateFile'];
	        $data = addslashes(file_get_contents($metaFile));
	        unset($elems); $elems = array();
	        if (!isset($xml->DataPanel) || !isset($xml->DataPanel->Element))
	        	continue;
	        if ($xml->DataPanel->Element) {
	        	foreach ($xml->DataPanel->Element as $elem)
	        		$elems[] = $elem['Name'];
	        }
	        $elemStr = implode(',',$elems);
	    	$sql = "INSERT INTO meta_form (`name`,`module`,`class`,`dataobj`,`template`,`data`,`elements`) 
	    			VALUES ('$metaName','$module','$class','$dataobj','$template','$data','$elemStr');";
	        try {
	            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
	            $db->query($sql);
	        }
	        catch (Exception $e) {
	            $this->errors = $e->getMessage();
	            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $this->errors." $sql");
	            return false;
	        }
        }
    }
    
	protected function installMetaView()
    {
    	$this->log("Install Module View metadata.");
    	$module = $this->name;
    	$modulePath = MODULE_PATH."/$module";
    	global $g_MetaFiles;
    	$g_MetaFiles = array();
        php_grep("<EasyView", $modulePath);
        if (empty($g_MetaFiles))
        	return;
        
        $db = $this->DBConnection();
    	$sql = "DELETE FROM meta_view WHERE module='$module'";
        try {
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
            $db->query($sql);
        }
        catch (Exception $e) {
            $this->errors = $e->getMessage();
            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $this->errors." $sql");
            return false;
        }
        foreach ($g_MetaFiles as $metaFile)
        {
            $metaName = str_replace('/','.',str_replace(array(MODULE_PATH.'/','.xml'),'', $metaFile));
	    	// load do
	    	$xml = simplexml_load_file($metaFile);

	        // write mod info in module table
	        $name = $xml['Name'];
	        $class = $xml['Class'];
	        $template = $xml['TemplateFile'];
	        $data = addslashes(file_get_contents($metaFile));
	        unset($refs); $refs = array();
	        if (!isset($xml->FormReferences) || !isset($xml->FormReferences->Reference))
	        	continue;
	        if ($xml->FormReferences->Reference) {
	        	foreach ($xml->FormReferences->Reference as $ref)
	        		$refs[] = $ref['Name'];
	        }
	        $refStr = implode(',',$refs);
	    	$sql = "INSERT INTO meta_view (`name`,`module`,`class`,`template`,`data`,`forms`) 
	    			VALUES ('$metaName','$module','$class','$template','$data','$refStr');";
	        try {
	            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $sql);
	            $db->query($sql);
	        }
	        catch (Exception $e) {
	            $this->errors = $e->getMessage();
	            //BizSystem::log(LOG_DEBUG, "DATAOBJ", $this->errors." $sql");
	            return false;
	        }
        }
    }
    
    protected function log($message)
    {
    	
	    	$date = date('c', time());
	    	if ($this->debug && CLI){
	    		echo "[$date] $message\n";
	    	}
	    	$this->logs .= "[$date] $message \n";
    	
    }
}

$g_MetaFiles = array();

function php_grep($q, $path)
{    
    global $g_MetaFiles;
    $fp = opendir($path);
    while($f = readdir($fp))
    {
    	if ( preg_match("#^\.+$#", $f) ) continue; // ignore symbolic links
    	$file_full_path = $path.'/'.$f;
    	if(is_dir($file_full_path)) 
    	{
    		php_grep($q, $file_full_path);
    	} 
    	else 
    	{
    		$path_parts = pathinfo($f);
    		if ($path_parts['extension'] != 'xml') continue; // consider only xml files
    		
    		//echo file_get_contents($file_full_path); exit;
    		if( stristr(file_get_contents($file_full_path), $q) ) 
    		    $g_MetaFiles[] = $file_full_path;
    	}
    }
}

?>