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
 * @version   $Id: ModuleLoadHandler.php 4963 2012-12-28 08:35:35Z hellojixian@gmail.com $
 */

include_once (MODULE_PATH."/system/lib/ModuleLoader.php");

interface ModuleLoadHandler
{
    public function beforeLoadingModule($moduelLoader);
    
    public function postLoadingModule($moduelLoader);
}

class DefaultModuleLoadHandler implements ModuleLoadHandler
{
	protected $m_RoleName;
	protected $m_ModuleName;
	
    public function beforeLoadingModule($moduelLoader)
    {
    }
    
    public function postLoadingModule($moduelLoader)
    {

    	$roleRec = BizSystem::getObject("system.do.RoleDO")->fetchOne("[name]='{$this->m_RoleName}'");
    	$memberRoleId = $roleRec['Id'];
    	
    	$actionList = BizSystem::getObject("system.do.AclActionDO")->directfetch("[module]='{$this->m_ModuleName}'");
    	foreach ($actionList as $actionRec){
	    	$actionId = $actionRec["Id"];
	    	
	    	$aclRecord = array(
	    		"role_id" =>  $memberRoleId,
	    		"action_id" => $actionId,
	    		"access_level" => 1
	    	);
	    	BizSystem::getObject("system.do.AclRoleActionDO")->insertRecord($aclRecord);

    	}    	
    }
    
    public function beforeUnloadModule($moduelLoader)
    {
    }    
    
    public function postUnloadModule($moduleLoader)
    {
    	$roleRec = BizSystem::getObject("system.do.RoleDO")->fetchOne("[name]='{$this->m_RoleName}'");
    	$memberRoleId = $roleRec['Id'];
    	$roleRec->delete();
    	
    	$actionList = BizSystem::getObject("system.do.AclActionDO")->directfetch("[module]='{$this->m_ModuleName}'");
    	foreach ($actionList as $actionRec){
	    	$actionId = $actionRec["Id"];	    		    
	    	BizSystem::getObject("system.do.AclRoleActionDO")->deleteRecords("[action_id]='$actionId' AND [role_id]='$memberRoleId'");
    	}
    	
    	BizSystem::getObject("system.do.AclActionDO")->deleteRecords("[module]='{$this->m_ModuleName}'");
    }
}
?>