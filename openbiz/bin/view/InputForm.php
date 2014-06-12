<?php
/**
 * InputForm class
 *
 * @package 
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
 
include_once "BaseForm.php";
 /*
  * protected methods: validateForm, readInputRecord, readInputs
  */
class InputForm extends BaseForm
{
	//list of method that can directly from browser
	protected $m_DirectMethodList = array('saverecord','switchform'); 
	
	public $m_RecordId;
	public $m_ActiveRecord;
	
    /**
     * Read user input data from UI
     *
     * @return array - record array
     */
    protected function readInputRecord()
    {
        $recArr = array();
        foreach ($this->m_DataPanel as $element)
        {
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
		$this->m_ActiveRecord = $recArr;
		return $recArr;
    }

    /**
     * Read inputs
     *
     * @return array array of input
     */
    protected function readInputs()
    {
        $inputArr = array();
        foreach ($this->m_DataPanel as $element)
        {
            $value = BizSystem::clientProxy()->getFormInputs($element->m_Name);
            $element->setValue($value);
            $inputArr[$element->m_Name] = $value;
        }

        foreach ($this->m_SearchPanel as $element)
        {
            $value = BizSystem::clientProxy()->getFormInputs($element->m_Name);
            $element->setValue($value);
            $inputArr[$element->m_Name] = $value;
        }
        return $inputArr;
    }
	
	/**
     * Validate input on EasyForm level
     * default form validation do nothing.
     * developers need to override this method to implement their logic
     *
     * @return boolean
     */
    protected function validateForm($cleanError = true)
    {
        if($cleanError == true)
        {
            $this->m_ValidateErrors = array();
        }
        $this->m_DataPanel->rewind();
        while($this->m_DataPanel->valid())
        {
            /* @var $element Element */
            $element = $this->m_DataPanel->current();
            if($element->m_Label)
            {
                $elementName = $element->m_Label;
            }
            else
            {
                $elementName = $element->m_Text;
            }
            if ($element->checkRequired() === true &&
                    ($element->m_Value==null || $element->m_Value == ""))
            {
                $errorMessage = $this->getMessage("FORM_ELEMENT_REQUIRED",array($elementName));
                $this->m_ValidateErrors[$element->m_Name] = $errorMessage;
                //return false;
            }
            elseif ($element->m_Value!==null && $element->Validate() == false)
            {
                $validateService = BizSystem::getService(VALIDATE_SERVICE);
                $errorMessage = $this->getMessage("FORM_ELEMENT_INVALID_INPUT",array($elementName,$value,$element->m_Validator));                
                if ($errorMessage == false)
                { //Couldn't get a clear error message so let's try this
                    $errorMessage = $validateService->getErrorMessage($element->m_Validator, $elementName);
                }
                $this->m_ValidateErrors[$element->m_Name] = $errorMessage;
                //return false;
            }
            $this->m_DataPanel->next() ;
        }
        if (count($this->m_ValidateErrors) > 0)
        {
            throw new ValidationException($this->m_ValidateErrors);
            return false;
        }
        return true;
    }
}
?>