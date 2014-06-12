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
 * @version   $Id: WidgetForm.php 3355 2012-05-31 05:43:33Z rockyswen@gmail.com $
 */

class WidgetForm extends EasyForm
{
	public $configable = 0;
	public $configForm;
	public $widgetForm;
	
	private $m_UserWidgetDO = "myaccount.do.UserWidgetDO";
	
	public function is_Configable()
	{
		return $this->configable;
	}
	
	protected function readMetadata(&$xmlArr)
	{
		parent::readMetaData($xmlArr);	
		if(isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["CONFIGFORM"]))
		{		
			 $this->configable = 1;	
			 $this->configForm = $xmlArr["EASYFORM"]["ATTRIBUTES"]["CONFIGFORM"];
		}
		if(isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["WIDGETFORM"]))
		{
			 $this->widgetForm = $xmlArr["EASYFORM"]["ATTRIBUTES"]["WIDGETFORM"];
		}
	}
	
	public function fetchData()
	{
		$rawRec = $this->fetchRawData();
		$config = $rawRec['config'];
		$configArr = unserialize($config);
		return $configArr;
	}
	
	protected function fetchRawData()
	{
		$user_id = BizSystem::GetUserProfile("Id");
		$searchRule="[widget]='$this->widgetForm' AND [user_id]='$user_id'";
		$do = BizSystem::GetObject($this->m_UserWidgetDO);
		$rawRec = $do->fetchOne($searchRule);
		return $rawRec;
	}
	
	public function updateConfig()
	{
        $currentRec = $this->fetchRawData();
        
        $recArr = $this->readInputRecord();
        if (count($recArr) == 0)
            return;

        try
        {
            $this->ValidateForm();
        }
        catch (ValidationException $e)
        {
            $this->processFormObjError($e->m_Errors);
            return;
        }

        $config = serialize($recArr);
        $newArr = array(
        	"Id"	=>$currentRec["Id"],
        	"config"=>$config
        );
        $do = BizSystem::GetObject($this->m_UserWidgetDO);
        if ($do->updateRecord($newArr, $currentRec) == false)
            return;

        // in case of popup form, close it, then rerender the parent form
        if ($this->m_ParentFormName)
        {
            $this->close();

            $this->renderParent();
        }

        $this->processPostAction();
	}
	
	public function outputAttrs()
	{
		$data = parent::outputAttrs();
		$data['config'] = $this->getConfig();
		return $data;
	}
	
	public function getConfig($widget=null)
	{
		if(!$widget)
		{
			if($this->widgetForm)
			{
				$widget = $this->widgetForm; 	
			}
			else
			{
				$widget = $this->m_Name;
			}
		}
		$user_id = BizSystem::GetUserProfile("Id");
		$searchRule="[widget]='$widget' AND [user_id]='$user_id'";
		$do = BizSystem::GetObject($this->m_UserWidgetDO);
		$configRec = $do->fetchOne($searchRule);
		$config = $configRec['config'];
		$configArr = unserialize($config);
		return $configArr;
	}
}
?>