<?php 
class EasyFormORM extends EasyForm
{
	protected $m_AssocDOs = array();
	
	/**
	 * 
	 * Get all assocsiated data object which has been
	 * referenced on current form's data panel's element
	 * 
	 * @return array of BizDataObj 
	 */
	protected function getAssocDOs()
	{
		if($this->m_AssocDOs)
		{
			return $this->m_AssocDOs;
		}
		$formMainDO = $this->getDataObj();
		foreach ($this->m_DataPanel as $element)
        {           
            $objName = $element->m_BizDataObj;
            $refObj = $formMainDO->getRefObject($objName);
            if($refObj)
            {
         		$this->m_AssocDOs[$refObj->m_Name] = $refObj;   
            }
        }
        return $this->m_AssocDOs;
	}
	
	/**
	 * 
	 * get an input record by specified DO name
	 * @param array $inputRecord
	 */
	protected function getAssocRec($doName)
	{
		$recArr = array();
		foreach ($this->m_DataPanel as $element)
        {           
            if( $element->m_BizDataObj == $doName){
            	$value = BizSystem::clientProxy()->getFormInputs($element->m_Name);
            	if ($value ===null && (
	            	   !is_a($element,"FileUploader")
	            	&& !is_subclass_of($element,"FileUploader")
	            	&& !is_a($element,"Checkbox")    
	            	&& !is_a($element,"FormElement")            	
	            	)){           
	            	continue;
	            }
	            $element->setValue($value);
	            $this->m_FormInputs[$element->m_Name] = $value;
	            $value = $element->getValue();
            	if ( $element->m_FieldName)
                	$recArr[$element->m_FieldName] = $value;
            }            
        }
        return $recArr;
	}
		
    protected function _doInsert($inputRecord)
    {
    	$recId = parent::_doInsert($inputRecord);
    	$formMainDO = $this->getDataObj();
    	foreach( $this->getAssocDOs() as $refDO)
    	{
    		
    		$inputRefRecord = $this->getAssocRec($refDO->m_Name);
    		$refRecId = $refDO->insertRecord($inputRefRecord);
    		$inputRefRecord['Id'] = $refRecId;
    		$refRec = $inputRefRecord;  
    		$refDO->addRecord($refRec, $isParentObjUpdated);
    	}
    }
}
?>