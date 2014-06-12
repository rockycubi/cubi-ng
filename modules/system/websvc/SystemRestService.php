<?php

include_once MODULE_PATH.'/websvc/lib/RestService.php';

class SystemRestService extends RestService
{
	protected $resourceDOMap = array('users'=>'system.do.UserDO',
									 'roles'=>'system.do.RoleDO',
									 'groups'=>'system.do.GroupDO',
									 'modules'=>'system.do.ModuleDO',
									 'aclactions'=>'system.do.AclActionDO',
									 'modulechangelogs'=>'system.do.ModuleChangeLogDO',
									 'sessions'=>'system.do.SessionDO');
}
?>