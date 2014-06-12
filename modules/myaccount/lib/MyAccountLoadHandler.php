<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.myaccount.lib
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: MyAccountLoadHandler.php 3365 2012-05-31 06:07:55Z rockyswen@gmail.com $
 */

include_once (MODULE_PATH."/system/lib/ModuleLoadHandler.php");

class MyAccountLoadHandler implements ModuleLoadHandler
{
    public function beforeLoadingModule($moduelLoader)
    {
    }
    
    public function postLoadingModule($moduelLoader)
    {
    	
    	$roleRec = BizSystem::getObject("system.do.RoleDO")->fetchOne("[name]='Cubi Member'");
    	$roleId = $roleRec['Id'];

    	$actionRec = BizSystem::getObject("system.do.AclActionDO")->fetchOne("[module]='myaccount' AND [resource]='myaccount' AND [action]='access'");
    	$actionId = $actionRec["Id"];
    	
    	$aclRecord = array(
    		"role_id" =>  $roleId,
    		"action_id" => $actionId,
    		"access_level" => 1
    	);
    	BizSystem::getObject("system.do.AclRoleActionDO")->insertRecord($aclRecord);
    }
}

?>