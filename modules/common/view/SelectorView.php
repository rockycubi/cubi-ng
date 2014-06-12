<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.common.view
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: SelectorView.php 4734 2012-11-14 14:18:37Z hellojixian@gmail.com $
 */

class SelectorView extends EasyView
{
	public $m_FormSelector;
	protected function readMetadata(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        unset($this->m_FormRefs);
        $this->m_FormSelector = isset($xmlArr["EASYVIEW"]["ATTRIBUTES"]["FROMSELECOTR"]) ? $xmlArr["EASYVIEW"]["ATTRIBUTES"]["FROMSELECOTR"] : null;
        $formRefXML = $this->getDefaultMainForm($xmlArr["EASYVIEW"]["FORMREFERENCES"]["REFERENCE"]);
        $this->m_FormRefs = new MetaIterator($formRefXML,"FormReference",$this);
    }
    
    public function getDefaultMainForm(&$xmlArr)
    {
    	$newForm = array(
			"ATTRIBUTES"=>array(
				"NAME"=>$this->m_FormSelector
				),
			"VALUE"=>null
		);
		$xmlArr = $newForm;
        $formObj=BizSystem::GetObject($this->m_FormSelector);
    	if(!$formObj){
			return;
		}
        $targetForm = $formObj->getViewMode();
        $newForm = array(
			"ATTRIBUTES"=>array(
				"NAME"=>$targetForm
				),
			"VALUE"=>null
		);
		$newArr = array($xmlArr,$newForm);
		return $newArr;
    }
}