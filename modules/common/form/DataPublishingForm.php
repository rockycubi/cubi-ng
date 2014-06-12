<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.common.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: DataPublishingForm.php 3355 2012-05-31 05:43:33Z rockyswen@gmail.com $
 */

include_once (MODULE_PATH.'/common/form/DataSharingForm.php');
class DataPublishingForm extends  DataSharingForm
{
	public function ShareRecord()
	{
		$prtForm = $this->m_ParentFormName;
		$prtFormObj = BizSystem::GetObject($prtForm);
		$recId = $this->m_RecordId;
		$dataObj = $prtFormObj->getDataObj();
		$dataRec = $dataObj->fetchById($recId);
		
		$recArr = $this->readInputRecord();
		$DataRec = $dataRec;
		
		//notice users has new published data
		//test if changed a new owner
		if($recArr['notify_user'] && $recArr['group_perm']){
			$data = $this->fetchData();			
			$data['app_index'] = APP_INDEX;
			$data['app_url'] = APP_URL;
			$data['operator_name'] = BizSystem::GetProfileName(BizSystem::getUserProfile("Id"));
			
			$emailSvc = BizSystem::getService(USER_EMAIL_SERVICE);
			
			//test if changes for group level visiable
			if($recArr['group_perm']>=1)
			{
				$group_id = $recArr['group_id'];
				$userList = $this->_getGroupUserList($group_id);
				foreach($userList as $user_id)
				{
					$emailSvc->DataPublishEmail($user_id, $data);
				}				
			}
			//test if changes for other group level visiable
			if($recArr['other_perm']>=1)
			{				
				$groupList = $this->_getGroupList();
				foreach($groupList as $group_id){								
					$userList = $this->_getGroupUserList($group_id);
					foreach($userList as $user_id)
					{
						$emailSvc->DataPublishEmail($user_id, $data);
					}				
				}
			}
		}
		
		if(isset($recArr['group_perm']))
		{
			$DataRec['group_perm'] = $recArr['group_perm'];
		}
		
		if(isset($recArr['other_perm']))
		{
			$DataRec['other_perm'] = $recArr['other_perm'];
		}
		
		if(isset($recArr['group_id']))
		{
			$DataRec['group_id']	= $recArr['group_id'];	
		}		
		
		if(isset($recArr['owner_id'])){
			$DataRec['owner_id']	= $recArr['owner_id'];
		}
		
		if($DataRec['group_perm']=='0'){
			$DataRec['other_perm']='0';
		}
		
		$DataRec->save();
		//$prtFormObj->getDataObj()->updateRecord($newDataRec,$dataRec);
		
		
		
		if($recArr['update_ref_data']){
			if($dataObj->m_ObjReferences->count()){
				$this->_casacadeUpdate($dataObj, $recArr);
			}			
		}
		
		if ($this->m_ParentFormName)
        {
            $this->close();
            $this->renderParent();
        }
        $this->processPostAction();
	}	
}
?>