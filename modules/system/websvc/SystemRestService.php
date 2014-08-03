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
	
	public function postUser($request, $response) {
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
            $dataRec[$k] = $v; // or $dataRec->$k = $v;
		}
        try {
           $dataRec->save();
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