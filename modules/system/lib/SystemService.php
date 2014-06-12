<?php 
class SystemService extends MetaObject
{
	public function GetDefaultGroupID()
	{
		$groupRec = BizSystem::getObject("system.do.GroupDO")->fetchOne("[default]='1'","[Id] DESC");
		if($groupRec)
		{
			$Id = $groupRec['Id'];
		}		
		return (int)$Id;
	}
	
	public function GetDefaultRoleID()
	{
		$roleRec = BizSystem::getObject("system.do.RoleDO")->fetchOne("[default]='1'","[Id] DESC");
		if($roleRec)
		{
			$Id = $roleRec['Id'];
		}		
		return (int)$Id;
	}
}
?>