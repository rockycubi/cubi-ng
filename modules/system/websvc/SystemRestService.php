<?php

define ('HASH_ALG','sha1');

include_once MODULE_PATH.'/websvc/lib/RestService.php';

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
			return  $this->postUser($request, $response);
		}
		else {
			return parent::post($resource, $request, $response);
		}
	}
	
	public function putChildren($resource, $id, $childresource, $request, $response) {
		if ($resource == 'roles' && $childresource == 'aclroleactions') {
			return  $this->postAclRoleAction($id, $request, $response);
		}
		else {
			return parent::putChildren($resource, $id, $childresource, $request, $response);
		}
	}
	
	public function queryChildren($resource, $id, $childresource, $request, $response) {
		if ($resource == 'roles' && $childresource == 'aclroleactions') {
			return  $this->queryAclRoleAction($id, $request, $response);
		}
		else {
			return parent::queryChildren($resource, $id, $childresource, $request, $response);
		}
	}
	
	protected function queryAclRoleAction($roleId, $request, $response)
    {
		// get page and sort parameters
		$allGetVars = $request->get();
		$queryParams = array();
		foreach ($allGetVars as $key=>$value) {
			if ($key == 'page' || $key == 'rows' || $key == 'sort' || $key == 'sorder' || $key == 'format') {
				continue;
			}
			//if ($value !== null && $value !== '') {
				$queryParams[$key] = $value;			
			//}
		}
		$page = $request->params('page');
		if (!$page) $page = 0;
		if ($page>=1) $page--;
		$rows = $request->params('rows');
		if (!$rows) $rows = 10;
		$sort = $request->params('sort');
		$sorder = $request->params('sorder');
		
		// fetch acl_action records
		$aclActionDo = BizSystem::getObject("system.do.AclActionDO");
		$aclActionDo->resetRules();
		$aclActionDo->setQueryParameters($queryParams);
		$aclActionDo->setLimit($rows, $page*$rows);
		if ($sort && $sorder) {
			$aclActionDo->setSortRule("[$sort] $sorder");
		}
		$dataSet = $aclActionDo->fetch();
		$total = $aclActionDo->count();
		$totalPage = ceil($total/$rows);
		
		// fetch role and access records
		$dataObj = BizSystem::getObject("system.do.AclRoleActionDO");
		$dataObj->setSearchRule("[role_id]=$roleId");
		$dataObj->setQueryParameters($queryParams);
		if ($sort && $sorder) {
			$dataObj->setSortRule("[$sort] $sorder");
		}
		$dataSet1 = $dataObj->fetch();
		foreach ($dataSet1 as $rec) {
            $actionRoleAccess[$rec['action_id']] = $rec;
        }
		
		$dataArr = $dataSet->toArray();
		// merge 2 datasets
		for ($i=0; $i<count($dataArr); $i++) {
            $actionId = $dataArr[$i]['Id'];
            $dataArr[$i]['access_level'] = "0";
            if (isset($actionRoleAccess[$actionId])) {
				$dataArr[$i]['role_id'] = $roleId;
                $dataArr[$i]['access_level'] = $actionRoleAccess[$actionId]['access_level'];
            }
        }
		
		$format = strtolower($request->params('format'));
		return $this->setResponse(array('totalPage'=>$totalPage,'data'=>$dataArr), $response, $format);
    }
	
	protected function postAclRoleAction($roleId, $request, $response) {
		$reqArray = json_decode($request->getBody());
		$dataObj = BizSystem::getObject("system.do.AclRoleActionDO");
		
		// get actionIds and accessLevels from request
		foreach ($reqArray as $reqRecord) {
            $actionId = $reqRecord->Id;
            $accessLevel = $reqRecord->access_level;
            // if find the record, update it, or insert a new one
            try {
                $rs = $dataObj->directFetch("[role_id]=$roleId AND [action_id]=$actionId", 1);
                if (count($rs) == 1) {
                    if ($rs[0]['access_level'] != $accessLevel) { // update
                        $recArr = $rs[0];
                        $recArr['access_level'] = $accessLevel;
                        $dataObj->updateRecord($recArr, $rs[0]);
                    }
                }
                else  {  // insert          	
                    if ($accessLevel !== null && $accessLevel !== "") {
                        $recArr = array("role_id"=>$roleId, "action_id"=>$actionId, "access_level"=>$accessLevel);
                        $dataObj->insertRecord($recArr);
                    }
                }
            }
            catch (BDOException $e) {
                $response->status(400);
				$response->body($e->getMessage());
            }
        }
		$response->body("Successfully update role access levels.");
	}
	
	protected function postUser($request, $response) {
	
		$resource = "users";
		$DOName = $this->getDOName($resource);
		if (empty($DOName)) {
			$response->status(404);
			$response->body("Resource '$resource' is not found.");
			return;
		}
		
		$dataObj = BizSystem::getObject($DOName);
		$dataRec = new DataRecord(null, $dataObj);
		$inputRecord = json_decode($request->getBody());
		if ($inputRecord->password != $inputRecord->password_repeat) {
			$response->status(400);
			$errmsg = "Invalid password";
			$response->body($errmsg);
			return;
		}
        foreach ($inputRecord as $k => $v) {
			if ($k == 'password') {
				$v = hash(HASH_ALG, $v);
			}
			if ($k == 'password_repeat') {
				continue;
			}
			if ($k == 'default_role') {
				$roleId = $v;
				continue;
			}
			if ($k == 'default_group') {
				$groupId = $v;
				continue;
			}
            $dataRec[$k] = $v; // or $dataRec->$k = $v;
		}
        try {
			$dataRec->save();
			$userId = $dataRec['Id'];
			
			// set default role for this user
			$userRoleDo = BizSystem::getObject("system.do.UserRoleDO");
			$userRoleDo->insertRecord(array('user_id'=>$userId,"role_id"=>$roleId,"default"=>1));
			// set default group for this user
			$userGroupDo = BizSystem::getObject("system.do.UserGroupDO");
			$userGroupDo->insertRecord(array('user_id'=>$userId,"group_id"=>$groupId,"default"=>1));
        }
        catch (ValidationException $e) {
            $response->status(400);
			$errmsg = implode("\n",$e->m_Errors);
			$response->body($errmsg);
			return;
        }
        catch (BDOException $e) {
            $response->status(400);
			$response->body($e->getMessage());
			return;
        }
		
		$format = strtolower($request->params('format'));
		return $this->setResponse($dataRec->toArray(), $response, $format);
	}
}

?>