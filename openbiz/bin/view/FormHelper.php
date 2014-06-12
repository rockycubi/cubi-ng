<?php
/**
 * FormHelper class
 *
 * @package 
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
 
 /*
  * Navigation methods: switchForm, showForm, getRedirectPage, processPostAction
  * Error Handling methods:
  * Validation methods
  */
class FormHelper
{
	protected $formObj;
	protected $m_InvokingElement;
	
	public function __construct($formObj)
	{
		$this->formObj = $formObj;
	}

// -------------------------- Navigation Methods ---------------------- //
	/**
     * Switch to other form
     *
     * @param string $formName to-be-swtiched form name. if empty, then switch to default form
     * @param string $id id value of the target form
     * @return void
     * @access remote
     */
    public function switchForm($formName=null, $id=null)
    {    	
		$formObj1 = BizSystem::getObject($formName);
		$formObj1->setRecordId($id);
		$output = $formObj1->render();
		if (!empty($output)) BizSystem::clientProxy()->redrawForm($this->formObj->m_Name, $output);
    }
	
	public function loadDialog($formName=null, $id=null)
    {    	
		$formObj1 = BizSystem::getObject($formName);
		$formObj1->setRecordId($id);
		//$formObj1->setParentForm($this->formObj->m_Name);
		$output = $formObj1->render();
		if (!empty($output)) BizSystem::clientProxy()->redrawForm("DIALOG", $output);
    }
	
    /**
     * Process Post Action
     *
     * @return void
     */
    public function processPostAction()
    {
        // get the $redirectPage from eventHandler
        list($redirectPage,$target) = $this->getRedirectPage();
        if ($redirectPage)
        {
			// if the redirectpage start with "form=", render the form to the target which is defined by FuntionType
			if (strpos($redirectPage,"form=") === 0)
			{
				parse_str($redirectPage, $output);
				$formName = $output['form'];
				
				$id = null;
				if (isset($output['fld:Id'])) {
					$id = $output['fld:Id'];
				}
				$this->switchForm($formName, $id);
			}
			else
			{
				// otherwise, do page redirection
				BizSystem::clientProxy()->ReDirectPage($redirectPage);
			}
        }
    }
	
    /**
     * return redirect page and target array
     *
     * @return array {redirectPage, $target}
     */
    public function getRedirectPage()
    {
        // get the control that issues the call
        // __this is elementName:eventHandlerName
        list($element, $eventHandler) = $this->getInvokingElement();
        $eventHandlerName = $eventHandler->m_Name;
        $redirectPage = $element->getRedirectPage($eventHandlerName); // need to get postaction of eventhandler
        $functionType = $element->getFunctionType($eventHandlerName);
        switch ($functionType)
        {
            case "Popup":
                $target = "Popup";
                break;
            default:
                $target = "";
        }
        return array($redirectPage, $target);
    }
	
	/**
     * Get the element that issues the call.
     *
     * @return array element object and event handler name
     */
    public function getInvokingElement()
    {
    	if ($this->m_InvokingElement)
        	return $this->m_InvokingElement;
    	// __this is elementName:eventHandlerName
        $elementAndEventName = BizSystem::clientProxy()->getFormInputs("__this");
        if (! $elementAndEventName)
        	return array(null,null);
        list ($elementName, $eventHandlerName) = explode(":", $elementAndEventName);
        $element = $this->formObj->getElement($elementName);
        $eventHandler = $element->m_EventHandlers->get($eventHandlerName);
        $this->m_InvokingElement = array($element, $eventHandler);
        return $this->m_InvokingElement;
    }
	
// -------------------------- Error Handling Methods ---------------------- //
    /**
     * Handle the error from {@link BizDataObj::getErrorMessage} method,
     * report the error as an alert window and log.
     *
     * @param int $errCode
     * @return void
     */
    public function processDataObjError($errCode = 0)
    {
        $errorMsg = $this->formObj->getDataObj()->getErrorMessage();
        BizSystem::log(LOG_ERR, "DATAOBJ", "DataObj error = ".$errorMsg);
        BizSystem::clientProxy()->showErrorMessage($errorMsg);
    }

    /**
     * Process error of form object
     *
     * @param array $errors
     * @return string - HTML text of this form's read mode
     */
    public function processFormObjError($errors)
    {
        $this->formObj->m_Errors = $errors;
		//print_r($this->m_Errors); exit;
        //$this->m_hasError = true;
        return $this->formObj->rerender();
    }

    /**
     * Handle the exception from DataObj method,
     *  report the error as an alert window
     *
     * @param int $errCode
     * @return string
     */
    public function processBDOException($e)
    {
        $errorMsg = $e->getMessage();
        BizSystem::log(LOG_ERR, "DATAOBJ", "DataObj error = ".$errorMsg);
        //BizSystem::clientProxy()->showClientAlert($errorMsg);   //showErrorMessage($errorMsg);
        //BizSystem::clientProxy()->showErrorMessage($errorMsg);	
        $e->no_exit=true;        
	    OB_ErrorHandler::ExceptionHandler($e);
    }

}
?>