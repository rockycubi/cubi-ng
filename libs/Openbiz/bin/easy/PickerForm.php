<?php
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.easy
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: PickerForm.php 4016 2011-04-29 12:25:27Z jixian2003 $
 */

/**
 * PickerForm class - contains form object metadata functions for picker
 *
 * @package openbiz.bin.easy
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class PickerForm extends EasyForm
{
    /**
     *
     * @var string
     */
    public $m_ParentFormElemName = "";

    /**
     *
     * @var string
     */
    public $m_PickerMap = "";

    public $m_ParentFormRecord ;
    /**
     * Get/Retrieve Session data of this object
     *
     * @param SessionContext $sessionContext
     * @return void
     */
    public function getSessionVars($sessionContext)
    {
        parent::getSessionVars($sessionContext);
        $sessionContext->getObjVar($this->m_Name, "ParentFormElemName", $this->m_ParentFormElemName);
        $sessionContext->getObjVar($this->m_Name, "PickerMap", $this->m_PickerMap);
        $sessionContext->getObjVar($this->m_Name, "ParentFormRecord", $this->m_ParentFormRecord);
    }

    /**
     * Save object variable to session context
     *
     * @param SessionContext $sessionContext
     * @return void
     */
    public function setSessionVars($sessionContext)
    {
        parent::setSessionVars($sessionContext);
        $sessionContext->setObjVar($this->m_Name, "ParentFormElemName", $this->m_ParentFormElemName);
        $sessionContext->setObjVar($this->m_Name, "PickerMap", $this->m_PickerMap);
        $sessionContext->setObjVar($this->m_Name, "ParentFormRecord", $this->m_ParentFormRecord);
    }

    /**
     * Set parent form data/informations
     *
     * @param string $formName
     * @param string $elemName
     * @param string $pickerMap
     * @return void
     */
    public function setParentFormData($formName, $elemName=null, $pickerMap=null)
    {
        $this->m_ParentFormName = $formName;
        $this->m_ParentFormElemName = $elemName;
        $this->m_PickerMap = $pickerMap;
    }

    /**
     * Pick data to parent form
     *
     * @param <type> $recId
     * @return void
     * @access remote
     */
    public function pickToParent($recId=null)
    {        
    	if ($recId==null || $recId=='')
            $recId = BizSystem::clientProxy()->getFormInputs('_selectedId');

        $selIds = BizSystem::clientProxy()->getFormInputs('row_selections', false);
        if ($selIds == null)
            $selIds[] = $recId;
            
        // if no parent elem or picker map, call AddToParent
        if (!$this->m_ParentFormElemName)
        {        	
            $this->addToParent($selIds);
        }                

        // if has parent elem and picker map, call JoinToParent
        if ($this->m_ParentFormElemName && $this->m_PickerMap)
        {
            $this->joinToParent($selIds);
        }
        
    }

    public function insertToParent()
    {        
    	 
		$recArr = $this->readInputRecord();
        $this->setActiveRecord($recArr);
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
        

        if (!$this->m_ParentFormElemName)
        {
        	//its only supports 1-m assoc now	        	        
	        $parentForm = BizSystem::objectFactory()->getObject($this->m_ParentFormName);
        	//$parentForm->getDataObj()->clearSearchRule();
	        $parentDo = $parentForm->getDataObj();
	        
	        $column = $parentDo->m_Association['Column'];
	    	$field = $parentDo->getFieldNameByColumn($column);	    	    	
	    	$parentRefVal = $parentDo->m_Association["FieldRefVal"];
	    	
			$recArr[$field] = $parentRefVal;
	    	if($parentDo->m_Association['Relationship']=='1-M'){	    			    	
		    	$cond_column = $parentDo->m_Association['CondColumn'];
		    	$cond_value = $parentDo->m_Association['CondValue'];
		    	if($cond_column)
		    	{
		    		$cond_field = $parentDo->getFieldNameByColumn($cond_column);
		    		$recArr[$cond_field] = $cond_value;
		    	}    
		    	$recId = $parentDo->InsertRecord($recArr);	
	    	}else{
	    		$recId = $this->getDataObj()->InsertRecord($recArr);	    			    		
	    		$this->addToParent($recId);
	    	}
        }                

        if ($this->m_ParentFormElemName && $this->m_PickerMap)
        {
            return ; //not supported yet
        }
       
        
        $selIds[] = $recId;
        
        $this->close();	      
        if($parentForm->m_ParentFormName){
        	$parentParentForm = BizSystem::objectFactory()->getObject($parentForm->m_ParentFormName);
        	$parentParentForm->rerender();
        }
        else
        {       
        	$parentForm->rerender();
        }
    }    
    
    /**
     * Join a record (popup) to parent form
     *
     * @param <type> $recId
     * @return void
     */
    public function joinToParent($recIds=null)
    {    	
    	if(!is_array($recIds))
    	{    		
    		$recIdArr = array();
    		$recIdArr[] = $recIds;
    	}else{
    		$recIdArr = $recIds;
    	}
    	
    	$parentForm = BizSystem::objectFactory()->getObject($this->m_ParentFormName);
    	$updArray = array();
    	$updRec = $this->m_ParentFormRecord;

    	foreach($recIdArr as $recId)
    	{
        	$rec = $this->getDataObj()->fetchById($recId);
              
	        // get the picker map of the control
	        if ($this->m_PickerMap)
	        {
	            $pickerList = $this->_parsePickerMap($this->m_PickerMap);	            
	            foreach ($pickerList as $ctrlPair)
	            {
	                $this_ctrl = $this->getElement($ctrlPair[1]);
	                if (!$this_ctrl)
	                    continue;
	                	                
	                $this_ctrl_val = $rec[$this_ctrl->m_FieldName];
	                $other_ctrl = $parentForm->getElement($ctrlPair[0]);
	                if ($other_ctrl)
	                {
	                	if(!$updArray[$other_ctrl->m_Name]){
	                		$updArray[$other_ctrl->m_Name] = $this_ctrl_val;
	                		$updRec[$other_ctrl->m_FieldName] = $this_ctrl_val;	
	                	}else{
	                		$updArray[$other_ctrl->m_Name] .= ';'.$this_ctrl_val;
	                		$updRec[$other_ctrl->m_FieldName] .= ';'.$this_ctrl_val;
	                	}
	                }
	            }	            
	        }
	        else
	            return;
    	}
    	    	
        $this->close();	                                               
        $elem = $parentForm->getElement($this->m_ParentFormElemName);
        if($elem->m_UpdateForm=='Y'){
        	$parentForm->setActiveRecord($updRec);        	
        	$parentForm->rerender();
        }else{
        	BizSystem::clientProxy()->updateFormElements($parentForm->m_Name, $updArray);
        	foreach($updArray as $elemName=>$value)
        	{
        		$elem = $parentForm->getElement($elemName);
        			$elemEvents = $elem->getEvents();
        			foreach($elemEvents as $event=>$function)
        			{
        				if(strtolower($event)=='onchange')
        				{
        					
	        				if(is_array($function)){
								foreach($function as $f){
									$function_str.=$f.";";
								}
							}else{
								$function_str .= $function;
							}
        					BizSystem::clientProxy()->runClientScript("<script>$function_str</script>");		
        				}
        			} 
        	}
        }                               
    }

    /**
     * Add a record (popup) to the parent form if OK button clicked, (M-M or M-1/1-1)
     *
     * @return void
     */
    public function addToParent($recIds=null)
    {
    	if(!is_array($recIds))
    	{    		
    		$recIdArr = array();
    		$recIdArr[] = $recIds;
    	}else{
    		$recIdArr = $recIds;
    	}
    	
    	/* @var $parentForm EasyForm */
    	$parentForm = BizSystem::objectFactory()->getObject($this->m_ParentFormName);
    	foreach($recIdArr as $recId)
    	{
	               	        	
	        //clear parent form search rules
	        $this->m_SearchRule="";
	        $parentForm->getDataObj()->clearSearchRule();
	        
	        $do = $this->getDataObj();
	        $baseSearchRule = $do->m_BaseSearchRule;
	        $do->m_BaseSearchRule = "";
	        $do->clearSearchRule();	        	        
	        $rec = $do->fetchById($recId);	
			$do->m_BaseSearchRule = $baseSearchRule;
			
			if(!$rec){
				$rec=BizSystem::getObject($do->m_Name,1)->fetchById($recId);
			}
			
	        // add record to parent form's dataObj who is M-M or M-1/1-1 to its parent dataobj
	        $ok = $parentForm->getDataObj()->addRecord($rec, $bPrtObjUpdated);
	        if (!$ok){	        	
	            return $parentForm->processDataObjError($ok);
	        }
    	}   
        
        $this->close();

        $parentForm->rerender();
		if($parentForm->m_ParentFormName){
			$parentForm->renderParent();
		}
    }


    /**
     * Parse Picker Map into an array
     *
     * @param string $pickerMap pickerMap defined in metadata
     * @return array picker map array
     */
    protected function _parsePickerMap($pickerMap)
    {
        $returnList = array();
        $pickerList = explode(",", $pickerMap);
        foreach ($pickerList as $pair)
        {
            $controlMap = explode(":", $pair);
            $controlMap[0] = trim($controlMap[0]);
            $controlMap[1] = trim($controlMap[1]);
            $returnList[] = $controlMap;
        }
        return $returnList;
    }
}
?>