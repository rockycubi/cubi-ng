<?php

define ('HASH_ALG','sha1');

include_once MODULE_PATH.'/websvc/lib/RestService.php';

class RoleRestService extends RestService
{
	protected $resourceDOMap = array('roles'=>'system.do.RoleDO');
	
	public function queryChildren($resource, $id, $childresource, $request, $response)
    {
		$roleId = $id;
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
	
	public function putChildren($resource, $id, $childresource, $request, $response) {
		$roleId = $id;
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
}

?>