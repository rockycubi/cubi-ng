<?php

define ('HASH_ALG','sha1');

include_once MODULE_PATH.'/websvc/lib/RestService.php';

class UserRestService extends RestService
{
	protected $resourceDOMap = array('users'=>'system.do.UserDO');

	public function post($resource, $request, $response) {
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