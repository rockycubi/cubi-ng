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
 * @version   $Id: UserRolesForm.php 3372 2012-05-31 06:19:06Z rockyswen@gmail.com $
 */

class UserRolesForm extends EasyForm{
	public function SetDefault($role_id=null){
		if($role_id==null)
		{
			$role_id =  (int)BizSystem::clientProxy()->getFormInputs('_selectedId');
		}
		$user_id = (int)BizSystem::objectFactory()->getObject('system.form.UserDetailForm')->m_RecordId;
		
		$roleDo = BizSystem::getObject("system.do.UserRoleDO",1);
		$roleDo->updateRecords("[default]=0","[user_id]='$user_id'");		
		$roleDo->updateRecords("[default]=1","[user_id]='$user_id' and [role_id]='$role_id'");
		
		$this->m_RecordId = $role_id;
		$this->UpdateForm();
	}
}
?>