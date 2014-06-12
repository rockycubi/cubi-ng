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
 * @version   $Id: AttrsForm.php 3355 2012-05-31 05:43:33Z rockyswen@gmail.com $
 */

class AttrsForm extends EasyForm
{
	
	public function insertRecord(){
		$recArr = $this->readInputRecord();
        $this->setActiveRecord($recArr);
        if (count($recArr) == 0)
            return;

		$newRecArr = array();
        $newParameterArr = array();
        foreach($recArr as $key=>$value){
        	if(substr($key,0,1)!='_'){
        		$newRecArr[$key]=$value;
        	}else{
        		$key = substr($key,1);
        		$newParameterArr[$key]=$value;
        	}
        }            
        $newRecArr['attrs']="";
	    foreach($newParameterArr as $key=>$value){
        	$newRecArr['attrs'].=$key."=".$value.";";
        }
            
        try
        {
            $this->ValidateForm();
        }
        catch (ValidationException $e)
        {
            $this->processFormObjError($e->m_Errors);
            return;
        }

        $this->_doInsert($newRecArr);
        
        

        // in case of popup form, close it, then rerender the parent form
        if ($this->m_ParentFormName)
        {
            $this->close();

            $this->renderParent();
        }
		
         $this->processPostAction();
		
		return $result;
	}

	public function updateRecord()
    {
        $currentRec = $this->fetchData();
        $recArr = $this->readInputRecord();
        //$this->setActiveRecord($recArr);
        if (count($recArr) == 0)
            return;
                    
		$newRecArr = array();
        $newParameterArr = array();
        foreach($recArr as $key=>$value){
        	if(substr($key,0,1)!='_'){
        		$newRecArr[$key]=$value;
        	}else{
        		$key = substr($key,1);
        		$newParameterArr[$key]=$value;
        	}
        }
        $newRecArr['attrs']="";
        foreach($newParameterArr as $key=>$value){
        	$newRecArr['attrs'].=$key."=".$value.";";
        }
        
        try
        {
            $this->ValidateForm();
        }
        catch (ValidationException $e)
        {
            $this->processFormObjError($e->m_Errors);
            return;
        }

        $this->_doUpdate($newRecArr, $currentRec);

        // in case of popup form, close it, then rerender the parent form
        if ($this->m_ParentFormName)
        {
            $this->close();

            $this->renderParent();
        }

        $this->processPostAction();

    }		
	
	public function fetchData(){
		$result = parent::fetchData();
		$attr_str = $result['attrs'];
		$attrArr = explode(";",$attr_str);
		foreach($attrArr as $value){
			$itemArr = explode("=",$value);
			$result["_".$itemArr[0]]=$itemArr[1];
		}
		
		$defaultRec = $this->getNewRecord();
		foreach($defaultRec as $key => $value){
			if(!isset($result[$key])){
				$result[$key] = $value;
			}
		}
		return $result;
	}	
	
    protected function getNewRecord()
    {
        $recArr = $this->getDataObj()->newRecord();
        if (! $recArr)
            return null;
        // load default values if new record value is empty
        $defaultRecArr = array();
        foreach ($this->m_DataPanel as $element)
        {
            if ($element->m_FieldName)
            {
                $defaultRecArr[$element->m_FieldName] = $element->getDefaultValue();
            }
        }
        return $defaultRecArr;
    }	
}
?>