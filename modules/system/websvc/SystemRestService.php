<?php

define ('HASH_ALG','sha1');

include_once MODULE_PATH.'/websvc/lib/RestService.php';
include_once 'UserRestService.php';
include_once 'RoleRestService.php';

class SystemRestService extends RestService
{
	protected $resourceDOMap = array('users'=>'system.do.UserDO',
									 'roles'=>'system.do.RoleDO',
									 'groups'=>'system.do.GroupDO',
									 'modules'=>'system.do.ModuleDO',
									 'aclactions'=>'system.do.AclActionDO',
									 'aclroleactions'=>'system.do.AclRoleActionDO',
									 'modulechangelogs'=>'system.do.ModuleChangeLogDO',
									 'sessions'=>'system.do.SessionDO');

	public function post($resource, $request, $response) {
		if ($resource == 'users') {
			// new user rest service
			$userRestSvc = new UserRestService();
			return  $userRestSvc->post($resource, $request, $response);
		}
		return parent::post($resource, $request, $response);
	}
	
	public function putChildren($resource, $id, $childresource, $request, $response) {
		if ($resource == 'roles') {
			// new role rest service
			$roleRestSvc = new RoleRestService();
			return  $roleRestSvc->putChildren($resource, $id, $childresource, $request, $response);
		}
		return parent::putChildren($resource, $id, $childresource, $request, $response);
	}
	
	public function queryChildren($resource, $id, $childresource, $request, $response) {
		if ($resource == 'roles') {
			// new role rest service
			$roleRestSvc = new RoleRestService();
			return  $roleRestSvc->queryChildren($resource, $id, $childresource, $request, $response);
		}
		return parent::queryChildren($resource, $id, $childresource, $request, $response);
	}
}

?>