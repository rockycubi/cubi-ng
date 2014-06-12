<?php

include_once MODULE_PATH.'/websvc/lib/RestService.php';

class UserRestService extends RestService
{
	protected $resourceDOMap = array('users'=>'system.do.UserDO');
}
?>