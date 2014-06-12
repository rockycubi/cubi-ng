<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.system.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ModuleForm.php 4677 2012-11-12 08:39:42Z hellojixian@gmail.com $
 */

include_once MODULE_PATH."/system/lib/ModuleLoader.php";

/**
 * ModuleForm class - implement the logic for manage modules
 *
 * @access public
 */
class ModuleForm extends EasyForm
{
    /**
     * load new modules from the modules/ directory
     *
     * @return void
     */
    public function loadNewModules($skipOld = true)
    {        
		BizSystem::getService(ACL_SERVICE)->clearACLCache();
       	$mods = array();
        $dir = MODULE_PATH;
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                $filepath = $dir.'/'.$file;
                if (is_dir($filepath)) {
                    $modfile = $filepath.'/mod.xml';
                    if (file_exists($modfile))
                        $mods[] = $file;
                }
            }
            closedir($dh);
        }        
        // find all modules
        foreach ($mods as $mod)
        {
            if ($skipOld ==true && ModuleLoader::isModuleInstalled($mod)){
            	continue;
            }
        	if (!ModuleLoader::isModuleOld($mod)){
            	continue;
            }
            $loader = new ModuleLoader($mod);
            $loader->debug = false;
            if (!$loader->loadModule()) {
            	$this->m_Errors[] = nl2br($this->GetMessage("MODULE_LOAD_ERROR",$mod)."\n".$loader->errors."\n".$loader->logs);
            }
            else {
            	$this->m_Notices[] = $this->GetMessage("MODULE_LOAD_COMPLETE",$mod);	//." ".$loader->logs;
            }
        }
        $this->rerender();
    }
    
    /**
     * load module from the modules/$module/ directory
     *
     * @return void
     */
    public function loadModule($module)
    {
        $loader = new ModuleLoader($module);
        $loader->debug = false;
    	if (!$loader->loadModule()) {
            $this->m_Errors[] = nl2br($this->GetMessage("MODULE_LOAD_ERROR")."\n".$loader->errors."\n".$loader->logs);
        }
        else {
            $this->m_Notices[] = $this->GetMessage("MODULE_LOAD_COMPLETE",$module);	//." ".$loader->logs;
        }
		
        $roles = BizSystem::getUserProfile("roles");
		$role_id = $roles[0];
		$this->giveActionAccess($module, $role_id);        
        
        //reload current profile
        BizSystem::getService(ACL_SERVICE)->clearACLCache();

        $this->rerender();
    }
    
    private function giveActionAccess($module,$role_id){
    	$where = " `module`='$module' ";    	
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
    
    public function PurgeRecord($id=null)
    {
    	return $this->DeleteRecord($id,true);
    }
    
	public function rrmdir($dir) {
	    foreach(glob($dir . '/*') as $file) {
	        if(is_dir($file))
	            $this->rrmdir($file);
	        else
	            unlink($file);
	    }
	    rmdir($dir);
	}
    
    public function DeleteRecord($id=null, $deleteFiles=false){
    	//delete menu items
        if ($this->m_Resource != "" && !$this->allowAccess($this->m_Resource.".delete"))
            return BizSystem::clientProxy()->redirectView(ACCESS_DENIED_VIEW);

        if ($id==null || $id=='')
            $id = BizSystem::clientProxy()->getFormInputs('_selectedId');

        $selIds = BizSystem::clientProxy()->getFormInputs('row_selections', false);
        if ($selIds == null)
            $selIds[] = $id;
        foreach ($selIds as $id)
        {
            $dataRec = $this->getDataObj()->fetchById($id);
            // take care of exception
            try
            {
                //also delete menu items                
                BizSystem::getObject("menu.do.MenuDO",1)->deleteRecords("[module]='".$dataRec->name."'");
                $dataRec->delete();
                
                //unload module      	                
	        	$mod = new ModuleLoader($dataRec['name']);
	        	$mod->unLoadModule();
                
	        	if($deleteFiles)
	        	{
	        		$modPath = MODULE_PATH.DIRECTORY_SEPARATOR.$dataRec['name']; 
	        		$this->rrmdir($modPath);
	        	}
                
            } catch (BDOException $e)
            {
                // call $this->processBDOException($e);
                $this->processBDOException($e);
                return;
            }
        }
        if (strtoupper($this->m_FormType) == "LIST")
            $this->rerender();

        $this->runEventLog();
        $this->processPostAction();
    	
    }
}  
?>