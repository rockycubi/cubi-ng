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
 * @version   $Id: ModuleUnloader.php 3372 2012-05-31 06:19:06Z rockyswen@gmail.com $
 */

require_once 'ModuleLoader.php';
class ModuleUnloader extends ModuleLoader
{
	public function unloadModule()
    {
		$this->log("Unloading module ".$this->name);
        $module = $this->name;
		$modfile = MODULE_PATH."/$module/mod.xml";
        if (!file_exists($modfile)) {
        	$this->errors = "$module is not unload, mod.xml is not found in $module.";	
        	return false;
    	}
    	if (($db = $this->DBConnection()) == null) {
    		$this->errors = "ERROR: Cannot get database connection.";	
        	return false;
    	}
        
	    // uninstall mod.sql	    
        if (!$this->uninstallModuleSql())
            	return false;
	    
    	// uninstall mod.xml
        if (!$this->uninstallModule())
            return false;

        // remove resource files to proper folders
        $this->removeResourceFiles();

        $this->removeModuleFiles();
        
        $this->log("$module is unloaded.");
        return true;
    }
    
    protected function uninstallModuleSql()
    {
        $this->log("Uninstall Module Sql.");
    	$sqlfile = MODULE_PATH."/".$this->name."/mod.uninstall.sql";
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

    protected function removeResourceFiles()
    {
        $this->log("Remove resource files from /cubi/resources folder.");
    	$module = $this->name;
        $targetFolder = APP_HOME."/resources/$module";        
        recurse_delete($targetFolder);
    }    
    
 	protected function removeModuleFiles()
    {
        $this->log("Remove module files to /cubi/modules folder.");
    	$module = $this->name;
        $targetFolder = MODULE_PATH.DIRECTORY_SEPARATOR.$module;        
        recurse_delete($targetFolder);
    }       
    
    protected function uninstallModule()
    {
        $this->log("Uninstall Module ".$this->name);
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
        $sql = "DELETE FROM module WHERE name='$modName'";
        try {
            $rs = $db->query($sql);
        }
        catch (Exception $e) {
            $this->errors = $e->getMessage();
            return false;
        }
        
        // uninstall ACL
        $this->uninstallACL($xml);
        
        // uninstall Menu
        $this->uninstallMenu($xml);
                
        $this->uninstallChangeLog($xml);
        
        return true;
    }    

	protected function uninstallChangeLog($xml)
    {
    	$this->log("Install Module Change Logs.");
    	$module_name = $xml['Name'];    	
    	$changelogDO = BizSystem::GetObject("system.do.ModuleChangeLogDO");
    	$changelogDO->deleteRecords("[module]='$module_name'");
    }    
    
    protected function uninstallMenu($xml)
    {
    	$this->log("Uninstall Module Menu.");
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
			            
    	}
    	return true;
    }    
    
    protected function uninstallACL($xml)
    {
    	$this->log("Uninstall Module ACL.");
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
			
			$sql = "DELETE FROM acl_action WHERE module='$modName' ";
            $db->query($sql);  
        }
    }
        
}
?>