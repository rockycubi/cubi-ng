<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.common.element
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: DataShareUserList.php 3355 2012-05-31 05:43:33Z rockyswen@gmail.com $
 */

include_once (OPENBIZ_BIN."/easy/element/Listbox.php");
class DataShareUserList extends Listbox
{
	public function getFromList(&$list, $selectFrom=null)
    {
        parent::getFromList($list, $selectFrom);
        $rawlist = $list;
        $list = array();
        foreach ($rawlist as $item)
        {
        	if($this->allowDisplay($item['val'])){
        		$list[] = $item;
        	}
        }
        return;
    }

    protected function allowDisplay($user_id)
    {
    	if(BizSystem::allowUserAccess("data_manage.manage")){
    		return true;	
    	}
    	//get user acl info
    	$actionRec = BizSystem::getObject("system.do.AclActionDO")->fetchOne("[module]='common' AND [resource]='data_assign' AND [action]='accept_other_assigned'");
    	$actionId = $actionRec['Id'];
    	if(!$actionId){
    		//the system doesnt support accept_other_assigned feature then return true;
    		return true; 
    	}
    	
    	//get list of all roles which enabled this action
    	$roleList = BizSystem::getObject("system.do.AclRoleActionDO")->directFetch("[action_id]='$actionId' AND ([access_level]='1' OR [access_level]='2')");
    	foreach ($roleList as $roleRec)
    	{
    		$roleId = $roleRec['role_id'];
    		//check if target user has this role
    		$AssocRecs = BizSystem::getObject("system.do.UserRoleDO")->directFetch("[role_id]='$roleId' AND [user_id]='$user_id'");
    		if($AssocRecs->count()){
    			return true;
    		}	
    	}
    	
    	//if we are in same group return true
    	//get user groups info
    	$user_id = (int)$user_id;
    	$groups=BizSystem::getUserProfile("groups");
    	$groupset = BizSystem::getObject("system.do.UserGroupDO")->directFetch("[user_id]='$user_id'");
    	foreach($groupset as $groupRec){
	    	$user_group_id = $groupRec['group_id'];
	    	foreach($groups as $group_id)
	    	{
	    		if($group_id == $user_group_id){
	    			return true;
	    		}
	    	}
    	}
    	return false;
    }
}
?>