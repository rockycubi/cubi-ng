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
 * @version   $Id: EasyForm.php 4203 2011-06-01 07:33:23Z rockys $
 */

//include_once(OPENBIZ_BIN."/easy/Panel.php");
//include_once(OPENBIZ_BIN."/easy/FormRenderer.php");
//include_once(OPENBIZ_BIN."/util/QueryStringParam.php");

/**
 * EasyForm class - contains form object metadata functions
 *
 * @package openbiz.bin.easy
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class EasyForm extends MetaObject implements iSessionObject
{
    public $DATAFORMAT = 'RECORD';

    // metadata vars are public, necessary for metadata inheritance
    public $m_Title;
    public $m_Icon;
    public $m_Description;
    public $m_jsClass;
    public $m_DataObjName;
    public $m_Height;
    public $m_Width;
    public $m_DefaultForm;
  
    public $m_CanUpdateRecord;
    public $m_DirectMethodList = null; //list of method that can directly from browser

    public $m_Panels; 

    /**
     * Name of inherited form (meta-form)
     *
     * @var string
     */
    public $m_InheritFrom;
    
    /**
     * Data Panel object
     *
     * @var Panel
     */
    public $m_DataPanel;
    /**
     * Action Panel object
     * @var Panel
     */
    public $m_ActionPanel;
    /**
     * Navigation Panel object
     * @var Panel
     */
    public $m_NavPanel;
    /**
     * Search Panel object
     * @var Panel
     */
    public $m_SearchPanel;

    public $m_TemplateEngine;
    public $m_TemplateFile;
    public $m_FormType;
    public $m_SubForms = null;
    public $m_EventName;
    public $m_Range = 10;
    public $m_CacheLifeTime = 0;
    public $m_FormParams;

    // parent form is the form that trigger the popup. "this" form is a popup form
    public $m_ParentFormName;
    // the form that drives navigation - the 1st form deplayed in the view
    public $m_DefaultFormName = null;

    public $m_Errors;   // errors array (error_element, error_message)
    public $m_Notices;  // list of notice messages

    // basic form vars
    protected $m_DataObj;
    protected $m_RecordId = null;
    public $m_ActiveRecord = null;
    public $m_FormInputs = null;
    public $m_SearchRule = null;
    public $m_FixSearchRule = null; // FixSearchRule is the search rule always applying on the search
    
    public $m_SortRule = null;
    
    protected $m_DefaultFixSearchRule = null;
    protected $m_Referer = "";
    public $m_MessageFile = null;
    protected $m_hasError = false;
    protected $m_ValidateErrors = array();
	protected $queryParams = array();

    // vars for grid(list)
    protected $m_CurrentPage = 1;
    protected $m_StartItem = 1;
    public $m_TotalPages = 1;
    protected $m_TotalRecords = 0;
    protected $m_RecordSet = null;
    protected $m_RefreshData = false;
    protected $m_Resource = "";

    protected $m_Messages;
    protected $m_InvokingElement = null;
    
    public $m_AutoRefresh=0;
    
    public $m_ReferenceFormName; //switch from which form
    protected $m_RecordAllowAccess = true;
	
	public $m_DataService;

    /**
     * Initialize BizForm with xml array
     *
     * @param array $xmlArr
     * @return void
     */
    function __construct(&$xmlArr)
    {
        $this->readMetadata($xmlArr);
        //echo $_GET['referer'];
        $this->inheritParentObj();
    }

    public function allowAccess($access=null)
    {
    	if(!$this->m_RecordAllowAccess)
    	{
    		/**
    		 * if the record is now allowed to access, then deny form render
    		 * instead of display an empty form
    		 */
    		return false; 
    	}
    	$result = parent::allowAccess($access);    	
    	return $result ;
    }
    
    /**
     * Read array meta data, and store to meta object
     *
     * @param array $xmlArr
     * @return void
     */
    protected function readMetadata(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_InheritFrom = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["INHERITFROM"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["INHERITFROM"] : null;        
        $this->m_Title = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["TITLE"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["TITLE"] : null;
        $this->m_Icon = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["ICON"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["ICON"] : null;        
        $this->m_Description = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["DESCRIPTION"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["DESCRIPTION"] : null;
        $this->m_jsClass = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["JSCLASS"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["JSCLASS"] : null;
        $this->m_Height = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["HEIGHT"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["HEIGHT"] : null;
        $this->m_Width = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["WIDTH"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["WIDTH"] : null;
        $this->m_DefaultForm = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["DEFAULTFORM"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["DEFAULTFORM"] : null;
        $this->m_TemplateEngine = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["TEMPLATEENGINE"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["TEMPLATEENGINE"] : null;
        $this->m_TemplateFile = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["TEMPLATEFILE"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["TEMPLATEFILE"] : null;
        $this->m_FormType = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["FORMTYPE"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["FORMTYPE"] : null;
        $this->m_Range = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["PAGESIZE"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["PAGESIZE"] : $this->m_Range;
        $this->m_FixSearchRule = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["SEARCHRULE"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["SEARCHRULE"] : null;
        $this->m_SortRule = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["SORTRULE"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["SORTRULE"] : null;
		$this->m_DefaultFixSearchRule = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["SEARCHRULE"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["SEARCHRULE"] : null;
        
        $this->m_Name = $this->prefixPackage($this->m_Name);
        if ($this->m_InheritFrom == '@sourceMeta') $this->m_InheritFrom = '@'.$this->m_Name;
        else $this->m_InheritFrom = $this->prefixPackage($this->m_InheritFrom);
        $this->m_DataObjName = $this->prefixPackage($xmlArr["EASYFORM"]["ATTRIBUTES"]["BIZDATAOBJ"]);

        if (isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["DIRECTMETHOD"]))
            $this->m_DirectMethodList = explode(",", strtolower(str_replace(" ", "",$xmlArr["EASYFORM"]["ATTRIBUTES"]["DIRECTMETHOD"])));

        $this->m_DataPanel = new Panel($xmlArr["EASYFORM"]["DATAPANEL"]["ELEMENT"],"",$this);
        $this->m_ActionPanel = new Panel($xmlArr["EASYFORM"]["ACTIONPANEL"]["ELEMENT"],"",$this);
        $this->m_NavPanel = new Panel($xmlArr["EASYFORM"]["NAVPANEL"]["ELEMENT"],"",$this);
        $this->m_SearchPanel = new Panel($xmlArr["EASYFORM"]["SEARCHPANEL"]["ELEMENT"],"",$this);
        $this->m_Panels = array($this->m_DataPanel, $this->m_ActionPanel, $this->m_NavPanel, $this->m_SearchPanel);

        $this->m_FormType = strtoupper($this->m_FormType);

        $this->m_EventName = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["EVENTNAME"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["EVENTNAME"] : null;

        $this->m_MessageFile = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["MESSAGEFILE"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["MESSAGEFILE"] : null;
        $this->m_Messages = Resource::loadMessage($this->m_MessageFile , $this->m_Package);

        $this->m_CacheLifeTime = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["CACHELIFETIME"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["CACHELIFETIME"] : "0";

        $this->m_CurrentPage = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["STARTPAGE"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["STARTPAGE"] : 1;
        $this->m_StartItem = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["STARTITEM"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["STARTITEM"] : 1;

        $this->m_AutoRefresh = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["AUTOREFRESH"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["AUTOREFRESH"] : 0;
        
        // parse access
        if ($this->m_Access)
        {
            $arr = explode (".", $this->m_Access);
            $this->m_Resource = $arr[0];
        }
        if ($this->m_jsClass == "jbForm" && strtoupper($this->m_FormType) == "LIST") $this->m_jsClass = "Openbiz.TableForm";
        if ($this->m_jsClass == "jbForm") $this->m_jsClass = "Openbiz.Form";
        
		$this->translate();	// translate for multi-language support
		
		// read dataService metadata. if not a full url, add default global DATA_SERVICE_HOST as prefix
		$this->m_DataService = $xmlArr["EASYFORM"]["ATTRIBUTES"]["DATASERVICE"];
		$urlParts = parse_url($this->m_DataService);
		if (!$urlParts['host']) {
			$this->m_DataService = DEFAULT_DATASERVICE_PROVIDER.$this->m_DataService;
		}
    }

    /**
     * Inherit from parent object. Name, Package, Class cannot be inherited
     *
     * @return void
     */
    protected function inheritParentObj()
    {
        if (!$this->m_InheritFrom) return;
        $parentObj = BizSystem::getObject($this->m_InheritFrom);

        $this->m_Title = $this->m_Title ? $this->m_Title : $parentObj->m_Title;
        $this->m_Icon = $this->m_Icon ? $this->m_Icon : $parentObj->m_Icon;        
        $this->m_Description  = $this->m_Description ? $this->m_Description : $parentObj->m_Description;
        $this->m_jsClass   = $this->m_jsClass ? $this->m_jsClass : $parentObj->m_jsClass;
        $this->m_Height   = $this->m_Height ? $this->m_Height : $parentObj->m_Height;
        $this->m_Width   = $this->m_Width ? $this->m_Width : $parentObj->m_Width;
        $this->m_DefaultForm   = $this->m_DefaultForm ? $this->m_DefaultForm : $parentObj->m_DefaultForm;        
        $this->m_TemplateEngine   = $this->m_TemplateEngine ? $this->m_TemplateEngine : $parentObj->m_TemplateEngine;
        $this->m_TemplateFile   = $this->m_TemplateFile ? $this->m_TemplateFile : $parentObj->m_TemplateFile;        
        $this->m_FormType   = $this->m_FormType ? $this->m_FormType : $parentObj->m_FormType;
        $this->m_Range   = $this->m_Range ? $this->m_Range : $parentObj->m_Range;
        $this->m_FixSearchRule   = $this->m_FixSearchRule ? $this->m_FixSearchRule : $parentObj->m_FixSearchRule;
        $this->m_DefaultFixSearchRule   = $this->m_DefaultFixSearchRule ? $this->m_DefaultFixSearchRule : $parentObj->m_DefaultFixSearchRule;		        
        $this->m_DataObjName   = $this->m_DataObjName ? $this->m_DataObjName : $parentObj->m_DataObjName;
        $this->m_DirectMethodList   = $this->m_DirectMethodList ? $this->m_DirectMethodList : $parentObj->m_DirectMethodList;
        $this->m_EventName   = $this->m_EventName ? $this->m_EventName : $parentObj->m_EventName;
        $this->m_MessageFile   = $this->m_MessageFile ? $this->m_MessageFile : $parentObj->m_MessageFile;
        $this->m_Messages = Resource::loadMessage($this->m_MessageFile , $this->m_Package);
		$this->m_CacheLifeTime   = $this->m_CacheLifeTime ? $this->m_CacheLifeTime : $parentObj->m_CacheLifeTime;
		$this->m_CurrentPage   = $this->m_CurrentPage ? $this->m_CurrentPage : $parentObj->m_CurrentPage;
		$this->m_StartItem   = $this->m_StartItem ? $this->m_StartItem : $parentObj->m_StartItem;        
        
        $this->m_DataPanel->merge($parentObj->m_DataPanel);
        $this->m_ActionPanel->merge($parentObj->m_ActionPanel);
        $this->m_NavPanel->merge($parentObj->m_NavPanel);
        $this->m_SearchPanel->merge($parentObj->m_SearchPanel);        

        if($this->m_DataPanel->current()){
	        foreach ($this->m_DataPanel as $elem)
	            $elem->adjustFormName($this->m_Name);
        }
        if($this->m_ActionPanel->current()){
	        foreach ($this->m_ActionPanel as $elem)
	            $elem->adjustFormName($this->m_Name);
        }
        if($this->m_NavPanel->current()){                
	        foreach ($this->m_NavPanel as $elem)
	            $elem->adjustFormName($this->m_Name);
        }
        if($this->m_SearchPanel->current()){
	        foreach ($this->m_SearchPanel as $elem)
	            $elem->adjustFormName($this->m_Name);            
        }   
		$this->m_Panels = array($this->m_DataPanel, $this->m_ActionPanel, $this->m_NavPanel, $this->m_SearchPanel);            
    }    
    
    /**
     * Get message, and translate it
     *
     * @param string $messageId message Id
     * @param array $params
     * @return string message string
     */
    public function getMessage($messageId, $params=array())
    {
        $message = isset($this->m_Messages[$messageId]) ? $this->m_Messages[$messageId] : constant($messageId);
        //$message = I18n::getInstance()->translate($message);
        $message = I18n::t($message, $messageId, $this->getModuleName($this->m_Name));        
        $msg = @vsprintf($message,$params);
        if(!$msg){ //maybe in translation missing some %s can cause it returns null
        	$msg = $message;
        }
        return $msg;
    }

    public function canDisplayForm()
    {
    	
    	if($this->getDataObj()->m_DataPermControl=='Y')
        {
        	switch(strtolower($this->m_FormType))
        	{
        		default:
        		case 'list':
        			return true;
        			break;
        		case 'detail':
        			$permCode=1;
        			break;  
        			
        		case 'edit':
        			$permCode=2;
        			break;        		      		
        	}
	        $svcObj = BizSystem::GetService(DATAPERM_SERVICE);
	        $result = $svcObj->checkDataPerm($this->fetchData(),$permCode,$this->getDataObj());
	        if($result == false)
	        {
	        	return false;
	        }
        }    	
        return true;
    }

    public function canDeleteRecord($rec)
    {
    	
    	if($this->getDataObj()->m_DataPermControl=='Y')
        {        	        	
	        $svcObj = BizSystem::GetService(DATAPERM_SERVICE);
	        $result = $svcObj->checkDataPerm($rec,3,$this->getDataObj());
	        if($result == false)
	        {
	        	return false;
	        }
        }    	
        return true;
    }
    
    
    /**
     * Get/Retrieve Session data of this object
     *
     * @param SessionContext $sessionContext
     * @return void
     */
    public function getSessionVars($sessionContext)
    {
        $sessionContext->getObjVar($this->m_Name, "RecordId", $this->m_RecordId);
        $sessionContext->getObjVar($this->m_Name, "FixSearchRule", $this->m_FixSearchRule);
        $sessionContext->getObjVar($this->m_Name, "SearchRule", $this->m_SearchRule);
        $sessionContext->getObjVar($this->m_Name, "QueryParams", $this->queryParams);
        $sessionContext->getObjVar($this->m_Name, "SubForms", $this->m_SubForms);
        $sessionContext->getObjVar($this->m_Name, "ParentFormName", $this->m_ParentFormName);
        $sessionContext->getObjVar($this->m_Name, "DefaultFormName", $this->m_DefaultFormName);
        $sessionContext->getObjVar($this->m_Name, "CurrentPage", $this->m_CurrentPage);
        $sessionContext->getObjVar($this->m_Name, "PageSize", $this->m_Range);
        $sessionContext->getObjVar($this->m_Name, "ReferenceFormName", $this->m_ReferenceFormName);
        $sessionContext->getObjVar($this->m_Name, "SearchPanelValues", $this->m_SearchPanelValues);
    }

    /**
     * Save object variable to session context
     *
     * @param SessionContext $sessionContext
     * @return void
     */
    public function setSessionVars($sessionContext)
    {
        $sessionContext->setObjVar($this->m_Name, "RecordId", $this->m_RecordId);
        $sessionContext->setObjVar($this->m_Name, "FixSearchRule", $this->m_FixSearchRule);
        $sessionContext->setObjVar($this->m_Name, "SearchRule", $this->m_SearchRule);        
        $sessionContext->setObjVar($this->m_Name, "QueryParams", $this->queryParams);
        $sessionContext->setObjVar($this->m_Name, "SubForms", $this->m_SubForms);
        $sessionContext->setObjVar($this->m_Name, "ParentFormName", $this->m_ParentFormName);
        $sessionContext->setObjVar($this->m_Name, "DefaultFormName", $this->m_DefaultFormName);
        $sessionContext->setObjVar($this->m_Name, "CurrentPage", $this->m_CurrentPage);
        $sessionContext->setObjVar($this->m_Name, "PageSize", $this->m_Range);
        $sessionContext->setObjVar($this->m_Name, "ReferenceFormName", $this->m_ReferenceFormName);
        $sessionContext->setObjVar($this->m_Name, "SearchPanelValues", $this->m_SearchPanelValues);        
    }

    /**
     * Invoke the action passed from browser
     *
     * @return mixed the function result, or false on error.
     */
    public function invoke()
    {
        $argList = func_get_args();
        $param1 = array_shift($argList);
        // first one is element:eventhandler
        list ($elementName, $eventHandlerName) = explode(":", $param1);        
        $element = $this->getElement($elementName);
        $eventHandler = $element->m_EventHandlers->get($eventHandlerName);
        $this->m_InvokingElement = array($element, $eventHandler);
        // find the matching function
        list($funcName, $funcParams) = $eventHandler->parseFunction($eventHandler->m_OrigFunction);
        // call the function with rest parameters
        return call_user_func_array(array($this, $funcName), $argList);
    }
    
    /**
     * Validate request from client (browser)
     * 
     * @param string $methodName called from the client
     * @return boolean
     */
    public function validateRequest($methodName)
    {
        $methodName = strtolower($methodName);
        
        if ($methodName == "selectrecord" || $methodName == "invoke" || $methodName="sortrecord") 
            return true;
        // element, eventhandler
        list($element, $eventHandler) = $this->getInvokingElement();
        if ($element && $eventHandler)
        {
            if (stripos($eventHandler->m_OrigFunction, $methodName)===0)
                return true;
        }
        // scan elements to match method
        foreach ($this->m_Panels as $panel)
        {
            foreach ($panel as $elem) 
                if ($elem->matchRemoteMethod($methodName)) return true;
        }

        if (is_array($this->m_DirectMethodList))
        {
            foreach ($this->m_DirectMethodList as $value)
            {
                if ($methodName == $value) return true;
            }
        }

        return false;
    }

    /**
     * Get object property
     * This method get element object if propertyName is "Elements[elementName]" format.
     *
     * @param string $propertyName
     * @return <type>
     */
    public function getProperty($propertyName)
    {
        $ret = parent::getProperty($propertyName);
        if ($ret !== null) return $ret;

        $pos1 = strpos($propertyName, "[");
        $pos2 = strpos($propertyName, "]");
        if ($pos1>0 && $pos2>$pos1)
        {
            $propType = substr($propertyName, 0, $pos1);
            $elementName = substr($propertyName, $pos1+1,$pos2-$pos1-1);
            switch(strtolower($propType))
            {
				case 'param':            	
            	case 'params':
            		$result = $this->m_FormParams[$elementName];
            		break;
            	default:
            		
            		$result = $this->getElement($elementName);
            		break;
            }            
            return $result;
        }
    }

    /**
     * Get object instance of {@link BizDataObj} defined in it's metadata file
     *
     * @return BizDataObj
     */
    public function getDataObj()
    {
        if (!$this->m_DataObj)
        {
            if ($this->m_DataObjName)
                $this->m_DataObj = BizSystem::objectFactory()->getObject($this->m_DataObjName);
            if($this->m_DataObj)
                $this->m_DataObj->m_BizFormName = $this->m_Name;
            else
            {
                //BizSystem::clientProxy()->showErrorMessage("Cannot get DataObj of ".$this->m_DataObjName.", please check your metadata file.");
                return null;
            }
        }
        return $this->m_DataObj;
    }

    /**
     * Set data object {@link BizDataObj} with specified instant from parameter
     *
     * @param BizDataObj $dataObj
     * @return void
     */
    final public function setDataObj($dataObj)
    {
        $this->m_DataObj = $dataObj;
    }

    /**
     * Get output attributs as array
     *
     * @return array array of attributs
     * @todo rename to getOutputAttribute or getAttribute (2.5?)
     */
    public function outputAttrs()
    {
        $output['name'] = $this->m_Name;
		$output['dataService'] = $this->m_DataService;
        $output['title'] = Expression::evaluateExpression($this->m_Title, $this);
        $output['icon'] = $this->m_Icon;
        $output['hasSubform'] = $this->m_SubForms ? 1 : 0;
        $output['currentPage'] = $this->m_CurrentPage;
        $output['currentRecordId'] = $this->m_RecordId;
        $output['totalPages'] = $this->m_TotalPages;
        $output['totalRecords'] = $this->m_TotalRecords;
        $output['description'] = str_replace('\n', "<br />", Expression::evaluateExpression($this->m_Description,$this));
        $output['elementSets'] = $this->getElementSet();
        $output['tabSets'] = $this->getTabSet();
        $output['ActionElementSets'] = $this->getElementSet($this->m_ActionPanel);    
        if($output['icon'])
        {   
	        if(preg_match("/{.*}/si",$output['icon']))
	        {
	        	$output['icon'] = Expression::evaluateExpression($output['icon'], null);
	        }
	        else
	        {
	        	$output['icon'] = THEME_URL . "/" . Resource::getCurrentTheme() . "/images/".$output['icon'];
	        }
        }
        return $output;
    }

    /**
     * Handle the error from {@link BizDataObj::getErrorMessage} method,
     * report the error as an alert window and log.
     *
     * @param int $errCode
     * @return void
     */
    public function processDataObjError($errCode = 0)
    {
        $errorMsg = $this->getDataObj()->getErrorMessage();
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
        $this->m_Errors = $errors;
        $this->m_hasError = true;
        return $this->rerender();
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

    /**
     * Set the sub forms of this form. This form is parent of other forms
     *
     * @param string $subForms - sub controls string with format: ctrl1;ctrl2...
     * @return void
     */
    final public function setSubForms($subForms)
    {
        // sub controls string with format: ctrl1;ctrl2...
        if (!$subForms || strlen($subForms) < 1)
        {
            $this->m_SubForms = null;
            return;
        }
        $subFormArr = explode(";", $subForms);
        unset($this->m_SubForms);
        foreach ($subFormArr as $subForm)
        {
            $this->m_SubForms[] = $this->prefixPackage($subForm);
        }
    }

    /**
     * Get view object
     *
     * @global BizSystem $g_BizSystem
     * @return EasyView
     */
    public function getViewObject()
    {
        global $g_BizSystem;
        $viewName = $g_BizSystem->getCurrentViewName();
        if (!$viewName) return null;
        $viewObj = BizSystem::getObject($viewName);
        return $viewObj;
    }

    /**
     * Get sub form of this form
     *
     * @return EasyForm
     */
    public function getSubForms()
    {
        // ask view to give its subforms if not set yet
        return $this->m_SubForms;
    }

    /**
     * Get an element object
     *
     * @param string $elementName - name of the control
     * @return Element
     */
    public function getElement($elementName)
    {
        if ($this->m_DataPanel->get($elementName)) return $this->m_DataPanel->get($elementName);
        if ($this->m_ActionPanel->get($elementName)) return $this->m_ActionPanel->get($elementName);
        if ($this->m_NavPanel->get($elementName)) return $this->m_NavPanel->get($elementName);
        if ($this->m_SearchPanel->get($elementName)) return $this->m_SearchPanel->get($elementName);
        if($this->m_WizardPanel)
        {
        	if ($this->m_WizardPanel->get($elementName)) return $this->m_WizardPanel->get($elementName);
        }
    }
    
    public function getElementSet($panel = null)
    {
    	if(!$panel){
    		$panel = $this->m_DataPanel;
    	}
    	$setArr = array();
    	$panel->rewind();
        while($panel->valid())    	    	
        {      
        	$elem = $panel->current();
        	$panel->next();    
        	if($elem->m_ElementSet && $elem->canDisplayed()){
        		//is it in array
        		if(in_array($elem->m_ElementSet,$setArr)){
        			continue;
        		}else{
        			array_push($setArr,$elem->m_ElementSet);
        		}
        	}                          
        }
        return $setArr;
    }
    
	public function getTabSet($panel = null)
    {
    	if(!$panel){
    		$panel = $this->m_DataPanel;
    	}
    	$setArr = array();
    	$tabSetArr = array();
    	$panel->rewind();
        while($panel->valid())    	    	
        {      
        	$elem = $panel->current();
        	$panel->next();    
        	if($elem->m_TabSet && $elem->canDisplayed()){
        		//is it in array
        		if(in_array($elem->m_TabSet,$setArr)){
        			continue;
        		}else{
         			$setArr[$elem->m_TabSetCode]=$elem->m_TabSet;
        		}
        	}          	                                  
        }
        foreach($setArr as $tabsetCode=>$tabset)
        {
        	$elemSetArr = array();
        	$panel->rewind();
	        while($panel->valid())    	    	
	        {      
	        	$elem = $panel->current();
	        	$panel->next();    
	        	if($elem->m_ElementSet && $elem->canDisplayed()){
	        		//is it in array
	        		if( $elem->m_TabSetCode!= $tabsetCode || 
	        			in_array($elem->m_ElementSet,$elemSetArr)){
	        			continue;
	        		}else{
	        			array_push($elemSetArr,$elem->m_ElementSet);
	        		}
	        	}          	                                  
	        }
	        $tabSetArr[$tabsetCode]['SetName'] = $tabset;
	        $tabSetArr[$tabsetCode]['Elems'] = $elemSetArr;
        }
        return $tabSetArr;
    }    

    /**
     * Get error elements
     *
     * @param array $fields
     * @return array
     */
    public function getErrorElements($fields)
    {
        $errElements = array();
        foreach ($fields as $field=>$error)
        {
            $element = $this->m_DataPanel->getByField($field);
            $errElements[$element->m_Name]=$error;
        }
        return $errElements;
    }

    /**
     * Popup a selection EasyForm in a dynamically generated EasyView
     *
     * @param string $viewName
     * @param string $formName
     * @param string $elementName
     * @return void
     * @access remote
     */
    public function loadPicker($formName, $elementName="")
    {
        // set the ParentFormName and ParentCtrlName of the popup form
        /* @var $pickerForm EasyForm */
        $pickerForm = BizSystem::objectFactory()->getObject($formName);

        if ($elementName != "")
        {
            // set the picker map as well
            $element = $this->getElement($elementName);
            $pickerMap = $element->m_PickerMap;
        }
		$currentRecord = $this->readInputRecord();
		$pickerForm->setParentForm($this->m_Name);
        $pickerForm->setParentFormData($this->m_Name, $elementName, $pickerMap);
        $pickerForm->m_ParentFormRecord = $currentRecord;
        BizSystem::clientProxy()->redrawForm("DIALOG", $pickerForm->render());
    }
    
    public function loadDialog($formName, $id=null,$transId=false)
    {
    	$paramFields = array();
        if ($id!=null)
            $paramFields["Id"] = $id;
        if ($transId!=false)
            $paramFields["Id"] = $this->m_RecordId;
        $this->_showForm($formName, "Dialog", $paramFields);
    }
	
	public function setParentForm($parentFormName)
	{
		$this->m_ParentFormName = $parentFormName;
	}

    /**
     * Call/Invoke service method, this EasyForm name is passed to the method
     *
     * @param string $class
     * @param string $method
     * @param string $param
     * @return mixed - return value of the service method
     */
    public function callService($class, $method, $param = null)
    {
        $service = BizSystem::getService($class);
        if($param){
        	return $service->$method($param);
        }else{
        	return $service->$method($this->m_Name);
        }
    }

    /**
     * Set request parameters
     *
     * @param array $paramFields
     * @return void
     */
    public function setRequestParams($paramFields)
    {    	    	
        if ($paramFields)
        {
        	$this->m_FixSearchRule=null; // reset fixsearchrule to clean the previous one in session
            foreach($paramFields as $fieldName=>$val)
            {
                $element = $this->m_DataPanel->getByField($fieldName);
                if($element->m_AllowURLParam=='Y')
                {                	
                	if(!$this->getDataObj())return;
                	if($this->getDataObj()->getField($fieldName)){
	                    if($this->getDataObj()->getField($fieldName)->checkValueType($val))
	                    {
	                        $this->setFixSearchRule("[$fieldName]='$val'");
	                    }
                	}
                }
            }
        }
    }

    public function setCurrentPage($pageid)
    {
    	$this->m_CurrentPage = $pageid;
    }
    /**
     * Close the popup window
     *
     * @return void
     */
    public function close()
    {
        BizSystem::clientProxy()->closePopup();
    }

    /**
     * Render parent form
     *
     * @return void
     */
    public function renderParent()
    {
        /* @var $parentForm EasyForm */
        $parentForm = BizSystem::objectFactory()->getObject($this->m_ParentFormName);
        $parentForm->rerender();
    }

    /**
     * Set the dependent search rule of the bizform, this search rule will apply on its BizDataObj.
     * The dependent search rule (session var) will always be with bizform until it get set to other value
     *
     * @param string $rule - search rule has format "[fieldName1] opr1 Value1 AND/OR [fieldName2] opr2 Value2"
     * @param boolean $cleanActualRule
     * @return void
     */
    public function setFixSearchRule($rule = null, $cleanActualRule = true)
    {
        if ($cleanActualRule)
            $this->m_FixSearchRule = $this->m_DefaultFixSearchRule;
            
        if ($this->m_FixSearchRule && $rule)
        {        	
            if (strpos($this->m_FixSearchRule, $rule) === false)
            {
                $this->m_FixSearchRule = $this->m_FixSearchRule . " AND " . $rule;
            }
        }
        if (!$this->m_FixSearchRule && $rule){
            $this->m_FixSearchRule = $rule;
        }
    }

    /**
     * Fetch record set
     *
     * @return array array of record
     */
    public function fetchDataSet()
    {
        if (!empty($this->m_DataService)) {
			//print "do not fetch data from data object";
			return array();
		}
		$dataObj = $this->getDataObj();

        if (!$dataObj) return null;
        
        if ($this->m_RefreshData)
            $dataObj->resetRules();
        else
            $dataObj->clearSearchRule();

        if ($this->m_FixSearchRule)
        {
            if ($this->m_SearchRule)
                $searchRule = $this->m_SearchRule . " AND " . $this->m_FixSearchRule;
            else
                $searchRule = $this->m_FixSearchRule;
        }
        else
            $searchRule = $this->m_SearchRule;

		$dataObj->setQueryParameters($this->queryParams);
        $dataObj->setSearchRule($searchRule);        
        if($this->m_StartItem>1)
        {
            $dataObj->setLimit($this->m_Range, $this->m_StartItem);
        }
        else
        {
            $dataObj->setLimit($this->m_Range, ($this->m_CurrentPage-1)*$this->m_Range);
        }      
        if($this->m_SortRule && $this->m_SortRule != $this->getDataObj()->m_SortRule)
        {
        		$dataObj->setSortRule($this->m_SortRule);
        }          
        $resultRecords = $dataObj->fetch();
        $this->m_TotalRecords = $dataObj->count();
        if ($this->m_Range && $this->m_Range > 0)
            $this->m_TotalPages = ceil($this->m_TotalRecords/$this->m_Range);
        $selectedIndex = 0;
        
        //if current page is large than total pages ,then reset current page to last page
        if($this->m_CurrentPage>$this->m_TotalPages && $this->m_TotalPages>0)
        {
        	$this->m_CurrentPage = $this->m_TotalPages;
        	$dataObj->setLimit($this->m_Range, ($this->m_CurrentPage-1)*$this->m_Range);
        	$resultRecords = $dataObj->fetch();
        }
        
        $this->getDataObj()->setActiveRecord($resultRecords[$selectedIndex]);

		if(!$this->m_RecordId)
		{
			$this->m_RecordId = $resultRecords[0]["Id"];
		}else{
			$foundRecordId = false;
			foreach($resultRecords as $record)
			{
				if($this->m_RecordId==$record['Id'])
				{
					$foundRecordId = true;
				}
			}
			if($foundRecordId == false)
			{
				$this->m_RecordId=$result[0]['Id'];
			}			
		}
		
        return $resultRecords;
    }

    /**
     * Fetch single record
     *
     * @return array one record array
     */
    public function fetchData()
    {    	
        // if has valid active record, return it, otherwise do a query
        if ($this->m_ActiveRecord != null)
            return $this->m_ActiveRecord;
        
        $dataObj = $this->getDataObj();
        if ($dataObj == null) return;

        if (strtoupper($this->m_FormType) == "NEW")
            return $this->getNewRecord();
		
        if (!$this->m_FixSearchRule && !$this->m_SearchRule){ 
        	//if its a default sub form,even no search rule, but can still fetch a default record 
        	if(!is_array($this->getDataObj()->m_Association)){
        		//only if its a default sub form and without any association then return emply array
        		return array();
        	}
        }else{	
	        if ($this->m_RefreshData)   $dataObj->resetRules();
	        else $dataObj->clearSearchRule();
	
	        if ($this->m_FixSearchRule)
	        {
	            if ($this->m_SearchRule)
	                $searchRule = $this->m_SearchRule . " AND " . $this->m_FixSearchRule;
	            else
	                $searchRule = $this->m_FixSearchRule;
	        }
	        
	        $dataObj->setSearchRule($searchRule);
	        $dataObj->setLimit(1);
        }
        $resultRecords = $dataObj->fetch();
		if(!count($resultRecords))
		{
			$this->m_RecordAllowAccess=false;
		}

        $this->m_RecordId = $resultRecords[0]['Id'];
        $this->setActiveRecord($resultRecords[0]);    	
        
    	if($this->getDataObj()){
			$this->m_CanUpdateRecord = (int)$this->getDataObj()->canUpdateRecord();
    	}        
        return $resultRecords[0];
    }

    /**
     * Goto page specified by $page parameter, and ReRender
     * If page not specified, goto page 1
     *
     * @param number $page
     */
    public function gotoPage($page=1)
    {
        $tgtPage = intval($page);
        if ($tgtPage == 0) $tgtPage = 1;
        $this->m_CurrentPage = $tgtPage;
        $this->rerender();
    }
    public function gotoSelectedPage($elemName)
    {
        $page = BizSystem::clientProxy()->getFormInputs(str_replace(".","_", $this->m_Name).'_'.$elemName);
    	$this->gotoPage($page);
    }
    public function setPageSize($elemName)
    {
        $pagesize = BizSystem::clientProxy()->getFormInputs(str_replace(".","_", $this->m_Name).'_'.$elemName);
    	$this->m_Range=$pagesize;
    	$this->UpdateForm();
    }    
    /**
     * Sort Record, for list form
     *
     * @param string $sortCol column name to sort
     * @param string $order 'dec' (decending) or 'asc' (ascending)
     * @access remote
     * @return void
     */
    public function sortRecord($sortCol, $order='ASC')
    {
        $element = $this->getElement($sortCol);
        // turn off the OnSort flag of the old onsort field
        $element->setSortFlag(null);
        // turn on the OnSort flag of the new onsort field
        if ($order == "ASC")
            $order = "DESC";
        else
            $order = "ASC";
        $element->setSortFlag($order);

        // change the sort rule and issue the query
        $this->getDataObj()->setSortRule("[" . $element->m_FieldName . "] " . $order);

        // move to 1st page
        $this->m_CurrentPage = 1;
        $this->m_SortRule = "";

        $this->rerender();
    }

    /**
     * Run Search
     *
     * @return void
     */
    public function runSearch()
    {
        static $isSearchHelperLoaded = false;
        
        if (!$isSearchHelperLoaded) {
            include_once(OPENBIZ_BIN."/easy/SearchHelper.php");
            $isSearchHelperLoaded = true;
        }
        $searchRule = "";
        $this->queryParams = array();
        foreach ($this->m_SearchPanel as $element)
        {
            $searchStr = '';
        	if(method_exists($element,"getSearchRule")){
        		$searchStr = $element->getSearchRule();        		
        	}else{        	
	            if (!$element->m_FieldName)
	                continue;
	
	            $value = BizSystem::clientProxy()->getFormInputs($element->m_Name);            
	            if($element->m_FuzzySearch=="Y")
	            {
	                $value="*$value*";
	            }
	            if ($value!='')
	            {
	                //$searchStr = inputValToRule($element->m_FieldName, $value, $this);
	                $this->queryParams[$element->m_FieldName] = $value;
	            }
        	}

            //BizSystem::clientProxy()->showClientAlert($searchStr);
        	if($searchStr){
        		if ($searchRule == "")
                    $searchRule .= $searchStr;
                else
                    $searchRule .= " AND " . $searchStr;
        	}
        }


        $this->m_SearchRule = $searchRule;

        $this->m_RefreshData = true;

        $this->m_CurrentPage = 1;

        BizSystem::log(LOG_DEBUG,"FORMOBJ",$this->m_Name."::runSearch(), SearchRule=".$this->m_SearchRule);

		$recArr = $this->readInputRecord();		
		
		$this->m_SearchPanelValues = $recArr;
		        
        $this->runEventLog();
        $this->rerender();
    }

    /**
     * Reset search
     * 
     * @return void
     */
    public function resetSearch()
    {
        $this->m_SearchRule = "";
        $this->m_RefreshData = true;
        $this->m_CurrentPage = 1;
        $this->runEventLog();
        $this->rerender();
    }
    
    public function setSearchRule($searchRule, $queryParams=null)
    {
    	$this->m_SearchRule = $searchRule;
    	$this->queryParams = $queryParams;
    	$this->m_RefreshData = true;
        $this->m_CurrentPage = 1;
    }
    
    /**
     * New record, be default, just redirect to the new record page
     *
     * @return void
     */
    public function newRecord()
    {
        $this->processPostAction();
    }

    /**
     * Copy record to new record     *
     *
     * @param mixed $id id of record that want to copy,
     * it parameter not passed, id is '_selectedId'
     * @return void
     */
    public function copyRecord($id=null)
    {
        if ($id==null || $id=='')
            $id = BizSystem::clientProxy()->getFormInputs('_selectedId');

        if (!$id)
        {
            BizSystem::clientProxy()->showClientAlert($this->getMessage("PLEASE_EDIT_A_RECORD"));
            return;
        }
        $this->getActiveRecord($id);
        $this->processPostAction();
    }

    /**
     * Edit Record
     * NOTE: append fld:Id=$id to the redirect page url
     *
     * @param mixed $id
     * @return void
     */
    public function editRecord($id=null)
    {
        if ($id==null || $id=='')
            $id = BizSystem::clientProxy()->getFormInputs('_selectedId');
		
        if (!isset($id))
        {
            BizSystem::clientProxy()->showClientAlert($this->getMessage("PLEASE_EDIT_A_RECORD"));
            return;
        }

        // update the active record with new update record
        $this->getActiveRecord($id);

        $this->processPostAction();
    }

    /**
     * Show form
     *
     * @param string $formName
     * @param string $target target type: Popup or other
     * @param array $paramFields
     * @return void
     */
    protected function _showForm($formName, $target, $paramFields)
    {
    	
    	$formName_org = $formName;
        if (!$this->m_DefaultFormName)
    		$this->m_DefaultFormName = $this->m_Name;
    	if ($formName == null)
    	{
    		if($this->m_ReferenceFormName == null)
    		{
    			$formName = $this->m_DefaultFormName;
    		}else{
    			if($formName = $this->m_ReferenceFormName){
    				//this judgement is for anti endless loop between swtich forms
    				$formObj = BizSystem::objectFactory()->getObject($this->m_ReferenceFormName);
    				if($formObj->m_ReferenceFormName == $this->m_Name){    					
    					$formName = $this->m_DefaultFormName;
    				}else{    				
    					$formName = $this->m_ReferenceFormName;
    				}
    			}
    		}
    	}
    	//if($this->getViewObject()->isInFormRefLibs($formName))
        {
            // get the form object
            /* @var $formObj EasyForm */
            $formObj = BizSystem::objectFactory()->getObject($formName);
            $formObj->m_DefaultFormName = $this->m_DefaultFormName;
            if($formName_org){
            	//RefenerenceForm records where the from switch from
     			if( $this->m_FormType!='EDIT' &&
     				$this->m_FormType!='NEW' &&
     				$this->m_FormType!='COPY' ){
 	      	     	$formObj->m_ReferenceFormName = $this->m_Name;
    			} 
            }
            
            //if has more than Id field as params then $clearFixSearchRule is false, means join all where rules
            $paramTemp = $paramFields;           
            unset($paramTemp['Id']);
            if(count($paramTemp)){
            	$clearFixSearchRule = false;
            }else{
            	$clearFixSearchRule = true;
            }
            foreach($paramFields as $fieldName=>$val){            	
            	$formObj->m_FormParams[$fieldName] = $val;
                $formObj->setFixSearchRule("[$fieldName]='$val'",$clearFixSearchRule);                
                if($fieldName=="Id"){
                	$formObj->setRecordId($val);
                }
            }

            if(!$formObj->canDisplayForm())
            {
            	$formObj->m_ErrorMessage = $this->getMessage("FORM_OPERATION_NOT_PERMITTED",$formObj->m_Name);
         
        		if (strtoupper($this->m_FormType) == "LIST"){
        			BizSystem::log(LOG_ERR, "DATAOBJ", "DataObj error = ".$errorMsg);
        			BizSystem::clientProxy()->showClientAlert($formObj->m_ErrorMessage);
        		}else{
        			$this->processFormObjError(array($formObj->m_ErrorMessage));	
        		}
	            
	            return false;
            }           
             
            switch ($target)
            {
                case "Popup":
                    $formObj->setParentForm($this->m_Name);
                    echo $formObj->render();
                    break;
                case "Dialog":
                    $formObj->setParentForm($this->m_Name);
                    BizSystem::clientProxy()->redrawForm("DIALOG", $formObj->render());
                    break;
                default:
                    BizSystem::clientProxy()->redrawForm($this->m_Name, $formObj->render());
            }
        }
    }

    /**
     * Delete Record
     * NOTE: use redirectpage attr of eventhandler to redirect or redirect to previous page by default
     *
     * @param string $id
     * @return void
     */
    public function deleteRecord($id=null)
    {
        if ($id==null || $id=='')
            $id = BizSystem::clientProxy()->getFormInputs('_selectedId');

        $selIds = BizSystem::clientProxy()->getFormInputs('row_selections', false);
        if ($selIds == null)
            $selIds[] = $id;
        foreach ($selIds as $id)
        {        	
            $dataRec = $this->getDataObj()->fetchById($id);
            $this->getDataObj()->setActiveRecord($dataRec);
            
            if(!$this->canDeleteRecord($dataRec))
            {
            	$this->m_ErrorMessage = $this->getMessage("FORM_OPERATION_NOT_PERMITTED",$this->m_Name);         
        		if (strtoupper($this->m_FormType) == "LIST"){
        			BizSystem::log(LOG_ERR, "DATAOBJ", "DataObj error = ".$errorMsg);
        			BizSystem::clientProxy()->showClientAlert($this->m_ErrorMessage);
        		}else{
        			$this->processFormObjError(array($this->m_ErrorMessage));	
        		}	
        		return;
            }
            
            // take care of exception
            try
            {
                $dataRec->delete();
            } catch (BDOException $e)
            {
                // call $this->processBDOException($e);
                $this->processBDOException($e);
                return;
            }
        }
        if (strtoupper($this->m_FormType) == "LIST")
            $this->rerender();

        $this->runEventLog();
        $this->processPostAction();
    }

    /**
     * Remove the record out of the associate relationship
     *
     * @return void
     */
    public function removeRecord ($id=null)
    {
    	if ($id==null || $id=='')
            $id = BizSystem::clientProxy()->getFormInputs('_selectedId');

        $selIds = BizSystem::clientProxy()->getFormInputs('row_selections', false);
        if ($selIds == null)
            $selIds[] = $id;
        foreach ($selIds as $id)
        { 
        	
        	$rec = $this->getDataObj()->fetchById($id);
	        $ok = $this->getDataObj()->removeRecord($rec, $bPrtObjUpdated);
	        if (! $ok)
	            return $this->processDataObjError($ok);
        }        

        $this->runEventLog();
        $this->rerender();
		if($this->m_ParentFormName)
		{
			$this->renderParent();
		}
     }

    /**
     * Select Record
     *
     * @param string $recId
     * @access remote
     * @return void
     */
    public function selectRecord($recId)
    {
        if ($recId==null || $recId=='')
            $recId = BizSystem::clientProxy()->getFormInputs('_selectedId');
        $this->m_RecordId = $recId;
        if($this->getDataObj()){
        	$this->getDataObj()->setActiveRecordId($this->m_RecordId);
        }
        $this->rerender(false); // not redraw the this form, but draw the subforms
        //$this->rerender(); 
    }

    /**
     * Get element Id
     *
     * @return mixed
     */
    public function getElementID()
    {
        $id = $this->m_DataPanel->getByField('Id')->getValue();
        if($id)
        {
            return (int)$id;
        }
        else
        {
            return (int)$this->m_RecordId;
        }
    }

    /**
     * Save input and redirect page to a new view
     * use redirectpage attr of eventhandler to redirect or redirect to previous page by default
     * NOTE: For Edit/New form type
     * 
     * @return void
     */
    public function saveRecord()
    {
        if (strtoupper($this->m_FormType) == "NEW")
        {
            $this->insertRecord();
        }
        else
        {
            $this->updateRecord();
        }
    }

    /**
     * Update record
     *
     * @return mixed
     */
    public function updateRecord()
    {
        $currentRec = $this->fetchData();
        $recArr = $this->readInputRecord();
        $this->setActiveRecord($recArr);
        if (count($recArr) != 0){
            	
	        try
	        {
	            $this->ValidateForm();
	        }
	        catch (ValidationException $e)
	        {
	            $this->processFormObjError($e->m_Errors);
	            return;
	        }
	
	        if ($this->_doUpdate($recArr, $currentRec) == false)
	            return;
			
			$this->commitFormElements(); // commit change in FormElement
        }
		
        // in case of popup form, close it, then rerender the parent form
        if ($this->m_ParentFormName)
        {
            $this->close();

            $this->renderParent();
        }
        
        $this->processPostAction();
    }

    
	public function updateFieldValueAdd($id,$fld_name,$value,$min,$max)
    {    	
    	if($value>=$max){
    		$value = $min;
    	}else{
    		$value++;
    	}
		return $this->updateFieldValue($id,$fld_name,$value);
    }        
    
	public function updateFieldValueXor($id,$fld_name,$value)
    {    	
    	if($value>0){
    		$value_xor = 0;
    	}else{
    		$value_xor = 1;
    	}
		return $this->updateFieldValue($id,$fld_name,$value_xor);
    }    
    
	/**
     * Update record
     *
     * @return mixed
     */
    public function updateFieldValue($Id,$fld_name,$value)
    {
    	
		$element = $this->m_DataPanel->get($fld_name);
		$fieldname = $element->m_FieldName;
        $currentRec = $this->getActiveRecord($Id);
        $recArr = $this->getActiveRecord($Id);
		$recArr[$fieldname]=$value;
        if ($this->_doUpdate($recArr, $currentRec) == false)
            return;
		$this->UpdateForm();
    }
    /**
     * Do update record
     *
     * @param array $inputRecord
     * @param array $currentRecord
     * @return void
     */
    protected function _doUpdate($inputRecord, $currentRecord)
    {
        $dataRec = new DataRecord($currentRecord, $this->getDataObj());

        foreach ($inputRecord as $k => $v){
           	$dataRec[$k] = $v; // or $dataRec->$k = $v;
        }

        try
        {
            $dataRec->save();
        }
        catch (ValidationException $e)
        {
            $errElements = $this->getErrorElements($e->m_Errors);           
        	if(count($e->m_Errors)==count($errElements)){
            	$this->processFormObjError($errElements);
            }else{            	
            	$errmsg = implode("<br />",$e->m_Errors);
		        BizSystem::clientProxy()->showErrorMessage($errmsg);
            }
            return false;
        }
        catch (BDOException $e)
        {
            $this->processBDOException($e);
            return false;
        }
		$this->m_ActiveRecord = null;
        $this->getActiveRecord($dataRec["Id"]);

        $this->runEventLog();
        return true;
    }
	
	protected function commitFormElements()
	{
		foreach ($this->m_DataPanel as $element)
		{
			if (is_a($element, "FormElement")) {
				$element->setValue('');
			}
		}
	}
	
    /**
     * Insert new record
     *
     * @return mixed
     */
    public function insertRecord()
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

        $this->_doInsert($recArr);
        
        $this->commitFormElements(); // commit change in FormElement

        // in case of popup form, close it, then rerender the parent form
        if ($this->m_ParentFormName)
        {
            $this->close();

            $this->renderParent();
        }

        $this->processPostAction();
    }

    /**
     * Do insert record
     *
     * @param array $inputRecord
     * @return void
     */
    protected function _doInsert($inputRecord)
    {
       
        $dataRec = new DataRecord(null, $this->getDataObj());

        // $inputRecord['Id'] = null; // comment it out for name PK case 
        foreach ($inputRecord as $k => $v)
            $dataRec[$k] = $v; // or $dataRec->$k = $v;

        try
        {
           $dataRec->save();
        }
        catch (ValidationException $e)
        {
            $errElements = $this->getErrorElements($e->m_Errors);
            if(count($e->m_Errors)==count($errElements)){
            	$this->processFormObjError($errElements);
            }else{            	
            	$errmsg = implode("<br />",$e->m_Errors);
		        BizSystem::clientProxy()->showErrorMessage($errmsg);
            }
            return;
        }
        catch (BDOException $e)
        {
            $this->processBDOException($e);
            return;
        }
		$this->m_ActiveRecord = null;
        $this->getActiveRecord($dataRec["Id"]);

        $this->runEventLog();
        return $dataRec["Id"];
    }

    /**
     * Cancel input and do page redirection
     *
     * @return void
     */
    public function cancel()
    {
        $this->processPostAction();
    }

    /**
     * Update form controls
     *
     * @return void
     * @access remote
     */
    public function updateForm()
    {
        // read the input to form controls
        //@todo: read inputs but should be skipp uploaders elements
        $recArr = $this->readInputRecord();        
        $this->setActiveRecord($recArr);        
        $this->rerender();
    }

    /**
     * Generate list for AutoSuggest element
     * Also supports elements that have hidden values
     *
     * @param string $input - the search string used to filter the list
     * @todo rename to createAutoSuggestList or createAutoSuggest(v2.5?)
     * @return void
     */
    public function autoSuggest($input)
    {
		if (defined('JSLIB_BASE') && JSLIB_BASE == 'JQUERY') {
			$value = $_GET["term"];
			// get the select from list of the element
			$element = $this->getElement($input);
			$element->setValue($value);
			$fromlist = array();
			$element->getFromList($fromlist);
			$arr = array();
			$i = 0;
			foreach ($fromlist as $item) {
				$arr[$i++] = array('label'=>$item['txt'], 'value'=>$item['val']);
			}
			echo json_encode($arr);
			return;
		}
		$foo = $_POST;
    	$hidden_flag = FALSE;
        if (strpos($input, '_hidden'))
        {
            $realInput = str_replace('_hidden', '', $input);
			$hidden_flag = TRUE;            
        } else
        {
            $realInput = $input;
        }

        $value = BizSystem::clientProxy()->getFormInputs($input);

        // get the select from list of the element
        $element = $this->getElement($realInput);
        $element->setValue($value);
        $fromlist = array();
        $element->getFromList($fromlist);
        echo "<ul>";
        if ($fromlist)
        {
            if ($hidden_flag = TRUE)
            {
            	$count=0;
                foreach ($fromlist as $item)
                {                	
                    echo "<li id=" . $item['txt'] . ">" . $item['val'] . "</li>";
                    $count++;
                    if($count>=5) break;
                }
            }
            else
            {
            	$count=0;
                foreach ($fromlist as $item)
                {
                    echo "<li>" . $item['txt'] . "</li>";
                    $count++;
                    if($count>=5) break;
                }
            }
        }
        echo "</ul>";
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

        foreach ($this->m_SearchPanel as $element)
        {
            $value = BizSystem::clientProxy()->getFormInputs($element->m_Name);
            $element->setValue($value);
            $this->m_FormInputs[$element->m_Name] = $value;
            $value = $element->getValue();
            if ($value !== null && $element->m_FieldName)
                $recArr[$element->m_FieldName] = $value;
        }
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


    public function setFormInputs($inputArr=null)
    {
        if(!$inputArr){
    		$inputArr = $this->m_FormInputs;
        } 
    	if(!is_array($inputArr)){
    		$inputArr = array();
        }        
        foreach ($this->m_DataPanel as $element)
        {
            if (isset($inputArr[$element->m_Name]))
            {             
            	$element->setValue($inputArr[$element->m_Name]);             	           
            }
        }

        foreach ($this->m_SearchPanel as $element)
        {
            if (isset($inputArr[$element->m_Name]))
            {
            	$element->setValue($inputArr[$element->m_Name]);
            }
        }
        return $inputArr;
    }    

    /**
     * Get new record
     *
     * @return array
     */
    protected function getNewRecord()
    {
    	if($this->getDataObj())
    	{
        	$recArr = $this->getDataObj()->newRecord();
    	}
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
        foreach ($recArr as $field => $val)
        {
            if ($val == "" && $defaultRecArr[$field] != "")
            {
                $recArr[$field] = $defaultRecArr[$field];
            }
        }
        return $recArr;
    }

    /**
     * Render this form (return html content),
     * called by EasyView's render method (called when form is loaded).
     * Query is issued before returning the html content.
     *
     * @return string - HTML text of this form's read mode
     * @example ../../../example/FormObject.php
     */
    public function render()
    {
        if (!$this->allowAccess())
            return "";
        $this->setClientScripts();

        if($this->m_CacheLifeTime>0 && $this->m_SubForms == null)
        {
            $cache_id = md5($this->m_Name);
            //try to process cache service.
            $cacheSvc = BizSystem::getService(CACHE_SERVICE,1);
            $cacheSvc->init($this->m_Name,$this->m_CacheLifeTime);
            if($cacheSvc->test($cache_id))
            {
                BizSystem::log(LOG_DEBUG, "FORM", "Cache Hit. form name = ".$this->m_Name);
                $output = $cacheSvc->load($cache_id);
            }
            else
            {
                BizSystem::log(LOG_DEBUG, "FORM", "Set cache. form name = ".$this->m_Name);
                $output = $this->renderHTML();
                $cacheSvc->save($output, $cache_id);
            }
            return $output;
        }

        //Moved the renderHTML function infront of declaring subforms
        $renderedHTML = $this->renderHTML();

        // prepare the subforms' dataobjs, since the subform relates to parent form by dataobj association
        if ($this->m_SubForms && $this->getDataObj())
        {
            foreach ($this->m_SubForms as $subForm)
            {
                $formObj = BizSystem::objectFactory()->getObject($subForm);
                $dataObj = $this->getDataObj()->getRefObject($formObj->m_DataObjName);
                if ($dataObj)
                    $formObj->setDataObj($dataObj);
            }
        }
		if (!$this->allowAccess())
		{
           return "";
		}
        return $renderedHTML;
    }

    /**
     * Render context menu code
     *
     * @return string html code for context menu
     */
    protected function renderContextMenu ()
    {
        $menuList = array();
        foreach ($this->m_Panels as $panel)
        {
            $panel->rewind();
            while ($element = $panel->current())
            {
                $panel->next();
                if (method_exists($element,'getContextMenu') && $menus = $element->getContextMenu())
                {
                    foreach ($menus as $m)
                        $menuList[] = $m;
                }
            }
        }
        if (count($menuList) == 0)
            return "";
        $str = "<div  class='contextMenu' id='" . $this->m_Name . "_contextmenu'>\n";
        $str .= "<div class=\"contextMenu_header\" ></div>\n";
        $str .= "<ul>\n";
        foreach ($menuList as $m)
        {
            $func = $m['func'];
            $shortcutKey = isset($m['key']) ? " (".$m['key'].")" : "";
            $str .= "<li><a href=\"javascript:void(0)\" onclick=\"$func\">".$m['text'].$shortcutKey."</a></li>\n";
        }
        $str .= "</ul>\n";
        $str .= "<div class=\"contextMenu_footer\" ></div>\n";
        $str .= "</div>\n";
		if (defined('JSLIB_BASE') && JSLIB_BASE == 'JQUERY') {
			$str .= "
<script>
$(jq('".$this->m_Name."')).removeAttr('onContextMenu');
$(jq('".$this->m_Name."'))[0].oncontextmenu=function(event){return Openbiz.Menu.show(event, '".$this->m_Name."_contextmenu');};
$(jq('".$this->m_Name."')).bind('click',Openbiz.Menu.hide);
</script>";
		}
		else {
			$str .= "
<script>
$('".$this->m_Name."').removeAttribute('onContextMenu');
$('".$this->m_Name."').oncontextmenu=function(event){return Openbiz.Menu.show(event, '".$this->m_Name."_contextmenu');};
$('".$this->m_Name."').observe('click',Openbiz.Menu.hide);
</script>";
		}
        return $str;
    }

    /**
     * Rerender this form (form is rendered already) .
     *
     * @param boolean $redrawForm - whether render this form again or not, optional default true
     * @param boolean $hasRecordChange - if record change, need to render subforms, optional default true
     * @return string - HTML text of this form's read mode
     */
    public function rerender($redrawForm=true, $hasRecordChange=true)
    {
        if ($redrawForm)
        {
            BizSystem::clientProxy()->redrawForm($this->m_Name, $this->renderHTML());
        }

        if ($hasRecordChange)
        {
            $this->rerenderSubForms();
        }
    }

    /**
     * Rerender sub forms who has dependecy on this form.
     * This method is called when parent form's change affect the sub forms
     *
     * @return void
     */
    protected function rerenderSubForms()
    {
        if (! $this->m_SubForms)
            return;
        foreach ($this->m_SubForms as $subForm)
        {
            $formObj = BizSystem::objectFactory()->getObject($subForm);
            if($this->getDataObj() && $formObj->m_DataObjName)
            {
	            $dataObj = $this->getDataObj()->getRefObject($formObj->m_DataObjName);
	            if ($dataObj)
	                $formObj->setDataObj($dataObj);
            }
            $formObj->rerender();
        }
        return;
    }

    /**
     * Render html content of this form
     *
     * @return string - HTML text of this form's read mode
     */
    protected function renderHTML()
    {    	    	    	
        $formHTML = FormRenderer::render($this);
        $otherHTML = $this->rendercontextmenu();
        
        
        if(preg_match('/iPad/si',$_SERVER['HTTP_USER_AGENT']) || 
        	preg_match('/iPhone/si',$_SERVER['HTTP_USER_AGENT'])){
        		$otherHTML.="
        		<script>
				var a=document.getElementsByTagName('a');
				for(var i=0;i<a.length;i++)
				{
					if(a[i].getAttribute('href').indexOf('javascript:')==-1
					&& a[i].getAttribute('href').indexOf('#')==-1)
						{
						    a[i].onclick=function()
						    {
							    try{
						    		show_loader();
						    	}catch(e){
						    		
						    	}
						        window.location=this.getAttribute('href');
						        return false
						    }
						}else{
						}
				} 
				</script>       		
        		";
        	} 
        if(!$this->m_ParentFormName)
        {
        	if (($viewObj = $this->getViewObject())!=null)
                $viewObj->m_LastRenderedForm = $this->m_Name;
        }
        return $formHTML ."\n". $otherHTML;
    }

    /**
     * Get event log message
     *
     * @return mixed string or null
     */
    protected function getEventLogMsg()
    {
        list($element, $eventHandler) = $this->getInvokingElement();
        $eventLogMsg = $eventHandler->m_EventLogMsg;
        if($eventLogMsg)
        {
            return $eventLogMsg;
        }
        else
        {
            return null;
        }
    }

    /**
     * Get on event elements
     *
     * @return array element list
     */
    protected function getOnEventElements()
    {
        $elementList = array();
        foreach ($this->m_DataPanel as $element)
        {
            if ($element->m_OnEventLog=="Y")
                $elementList[] = $element->m_Value;
        }
        return $elementList;
    }

    /**
     * Run event log
     *
     * @return void
     */
    protected function runEventLog()
    {
        $logMessage = $this->getEventLogMsg();
        $eventName = $this->m_EventName;
        if($logMessage && $eventName)
        {
            $logElements = $this->getOnEventElements();
            $eventlog 	= BizSystem::getService(EVENTLOG_SERVICE);
            $eventlog->log($eventName, $logMessage, $logElements);
        }
    }

    /**
     * return redirect page and target array
     *
     * @return array {redirectPage, $target}
     */
    protected function getRedirectPage()
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
            case "Prop_Window":
            case "Prop_Dialog":
                $target = "Popup";
                break;
            default:
                $target = "";
        }
        return array($redirectPage, $target);
    }

    /**
     * Switch to other form
     *
     * @param string $formName to-be-swtiched form name. if empty, then switch to default form
     * @param string $id id value of the target form
     * @return void
     * @access remote
     */
    public function switchForm($formName=null, $id=null, $params=null, $target=null)
    {    	
    	$paramFields = array();
    	if($params){
    		parse_str(urldecode($params),$paramFields);
    	}
        if ($id!=null)
            $paramFields["Id"] = $id;
        $this->_showForm($formName, $target, $paramFields);
    }
    
    public function parentSwitchForm($formName=null, $id=null, $params=null, $target=null)
    {
    	if($this->m_ParentFormName){
    		$formObj = BizSystem::getObject($this->m_ParentFormName);
    		return $formObj->switchForm($formName, $id, $params, $target);
    	}
    }

	public function targetSwitchForm($targetForm, $formName=null, $id=null, $params=null, $target=null)
    {
    	if($targetForm){
    		$formObj = BizSystem::getObject($targetForm);
    		if($formObj){
    			return $formObj->switchForm($formName, $id, $params, $target);
    		}
    	}
    }
    /**
     * Get the element that issues the call.
     *
     * @return array element object and event handler name
     */
    protected function getInvokingElement()
    {
    	if ($this->m_InvokingElement)
        	return $this->m_InvokingElement;
    	// __this is elementName:eventHandlerName
        $elementAndEventName = BizSystem::clientProxy()->getFormInputs("__this");
        if (! $elementAndEventName)
        	return array(null,null);
        list ($elementName, $eventHandlerName) = explode(":", $elementAndEventName);
        $element = $this->getElement($elementName);
        $eventHandler = $element->m_EventHandlers->get($eventHandlerName);
        $this->m_InvokingElement = array($element, $eventHandler);
        return $this->m_InvokingElement;
    }

    /**
     * Process Post Action
     *
     * @return void
     */
    protected function processPostAction()
    {
        // get the $redirectPage from eventHandler
        list($redirectPage,$target) = $this->getRedirectPage();
        if ($redirectPage)
        {
            if($this->m_hasError==false)
            {
                // if the redirectpage start with "form=", render the form to the target which is defined by FuntionType
                if (strpos($redirectPage,"form=") === 0)
                {
                    parse_str($redirectPage, $output);
                    $formName = $output['form'];

                    // parse query string. e.g. fld:Id=val&fld:name=val
                    $paramFields = array();
                    foreach ($output as $key=>$value)
                    {
                        if (substr($key, 0, 4) == "fld:")
                        {
                            $fieldName = substr($key, 4);
                            $paramFields[$fieldName] = $value;
                        }
                    }

                    $this->_showForm($formName, $target, $paramFields);
                }
                else
                {
                    // otherwise, do page redirection
                    BizSystem::clientProxy()->ReDirectPage($redirectPage);
                }
            }
        }
    }

    /**
     * Get activeRecord
     *
     * @param mixed $recId
     * @return array - record array
     */
    public function getActiveRecord($recId=null)
    {
        if ($this->m_ActiveRecord != null)
        {
            if($this->m_ActiveRecord['Id'] != null)
            {
                return $this->m_ActiveRecord;
            }
        }

        if ($recId==null || $recId=='')
            $recId = BizSystem::clientProxy()->getFormInputs('_selectedId');
        if ($recId==null || $recId=='')
            return null;
        $this->m_RecordId = $recId;

        // TODO: may consider cache the current record in session
        if($this->getDataObj()){
	        $this->getDataObj()->setActiveRecordId($this->m_RecordId);
	        $rec = $this->getDataObj()->getActiveRecord();
	
	        // update the record row
	        $this->m_DataPanel->setRecordArr($rec);
	
	        $this->m_ActiveRecord = $rec;
        }
        return $rec;
    }

	public function getRecordId(){
    	return $this->m_RecordId;
    }
    public function setRecordId($val){
    	$this->m_RecordId = $val;
    	return $val; 
    }
    
    /**
     * Set active record
     *
     * @param array $record
     * @return void
     */
    protected function setActiveRecord($record)
    {
        // update the record row
        $this->m_DataPanel->setRecordArr($record);           
        if(!isset($this->m_ActiveRecord["Id"]) && 
        	$this->m_RecordId!=null && 
        	(strtoupper($this->m_FormType) == 'EDIT' || $this->m_FormType==null )){     
        		if($this->getDataObj()){   	
        			$this->m_ActiveRecord = $this->getDataObj()->fetchById($this->m_RecordId)->toArray();
        		}        		
        }
		if(is_array($record)){    	        
	        foreach($record as $key=>$value){
	        	if($key=='extend')continue;
	        	$this->m_ActiveRecord[$key] = $record[$key];
	        }        
		}
		
    }

    /**
     * Set client scripts, auto add javascripts code to the page
     *
     * @return void
     */
    protected function setClientScripts()
    {
        // load custom js class
        if ($this->m_jsClass != "Openbiz.Form" && $this->m_jsClass != "Openbiz.TableForm" && $this->m_jsClass != "" )
            BizSystem::clientProxy()->appendScripts($this->m_jsClass, $this->m_jsClass . ".js");
        /*
        if ($this->m_FormType == 'LIST')
        {
            BizSystem::clientProxy()->appendScripts("tablekit", "tablekit.js");
        }*/
    }
    
    protected function translate()
    {
    	$module = $this->getModuleName($this->m_Name);
    	if (!empty($this->m_Title))
    	{
    		$trans_string = I18n::t($this->m_Title, $this->getTransKey('Title'), $module, $this->getTransPrefix());
    		if($trans_string){
    			$this->m_Title = $trans_string;
    		}
    	}
    	if (!empty($this->m_Icon))
    	{
    		$trans_string = I18n::t($this->m_Icon, $this->getTransKey('Icon'), $module, $this->getTransPrefix());
    		if($trans_string){
    			$this->m_Icon = $trans_string;
    		}
    	}
    	if (!empty($this->m_Description))
    	{
    		$trans_string = I18n::t($this->m_Description, $this->getTransKey('Description'), $module, $this->getTransPrefix());
    		if($trans_string){
    			$this->m_Description = $trans_string;
    		}
    	}
    }
    
	protected function getTransPrefix()
    {    	
    	$nameArr = explode(".",$this->m_Name);
    	for($i=1;$i<count($nameArr)-1;$i++)
    	{
    		$prefix .= strtoupper($nameArr[$i])."_";
    	}
    	return $prefix;
    }
    
    protected function getTransKey($name)
    {
    	$shortFormName = substr($this->m_Name,intval(strrpos($this->m_Name,'.'))+1);
    	return strtoupper($shortFormName.'_'.$name);
    }
}
?>
