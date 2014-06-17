<?php
/**
 * BaseForm class
 *
 * @package 
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */

include_once "FormHelper.php"; 

class BaseForm extends MetaObject implements iSessionObject
{
    // metadata vars are public, necessary for metadata inheritance
    public $m_Title;
    public $m_Icon;
    public $m_jsClass;
    public $m_DataObjName;
	
	// FormAction handles actions from client
	public $m_FormAction;
	// FormRenderer draws data to given format (html/xml) output
	public $m_FormRenderer;
	// FormEventManager triggers external event observers on certain events
	public $m_FormEventManager;

    /**
     * Name of inherited form (meta-form)
     * @var string
     */
    public $m_InheritFrom;
    
	public $m_Panels; 
    /**
     * Data Panel object
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

    public $m_Height;
    public $m_Width;
    public $m_TemplateEngine;
    public $m_TemplateFile;
    public $m_SubForms = null;
    public $m_CacheLifeTime = 0;

    // basic form vars
    protected $m_DataObj;
    public $m_MessageFile = null;
    protected $m_Messages;
	
	protected $m_DirectMethodList = array(); //list of method that can directly from browser
	
	protected $formHelper;
    
    /**
     * Initialize BizForm with xml array
     *
     * @param array $xmlArr
     * @return void
     */
    function __construct(&$xmlArr)
    {
		$this->readMetadata($xmlArr);
        $this->inheritParentObj();
		$this->formHelper = new FormHelper($this);
    }

    public function allowAccess($access=null)
    {
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
        $this->m_TemplateEngine = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["TEMPLATEENGINE"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["TEMPLATEENGINE"] : null;
        $this->m_TemplateFile = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["TEMPLATEFILE"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["TEMPLATEFILE"] : null;
		$this->m_FormType = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["FORMTYPE"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["FORMTYPE"] : null;
        
        $this->m_Name = $this->prefixPackage($this->m_Name);
        if ($this->m_InheritFrom == '@sourceMeta') $this->m_InheritFrom = '@'.$this->m_Name;
        else $this->m_InheritFrom = $this->prefixPackage($this->m_InheritFrom);
        $this->m_DataObjName = $this->prefixPackage($xmlArr["EASYFORM"]["ATTRIBUTES"]["BIZDATAOBJ"]);

        $this->m_DataPanel = new Panel($xmlArr["EASYFORM"]["DATAPANEL"]["ELEMENT"],"",$this);
        $this->m_ActionPanel = new Panel($xmlArr["EASYFORM"]["ACTIONPANEL"]["ELEMENT"],"",$this);
        $this->m_NavPanel = new Panel($xmlArr["EASYFORM"]["NAVPANEL"]["ELEMENT"],"",$this);
        $this->m_SearchPanel = new Panel($xmlArr["EASYFORM"]["SEARCHPANEL"]["ELEMENT"],"",$this);
        $this->m_Panels = array($this->m_DataPanel, $this->m_ActionPanel, $this->m_NavPanel, $this->m_SearchPanel);

        $this->m_EventName = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["EVENTNAME"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["EVENTNAME"] : null;

        $this->m_MessageFile = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["MESSAGEFILE"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["MESSAGEFILE"] : null;
        $this->m_Messages = Resource::loadMessage($this->m_MessageFile , $this->m_Package);

        $this->m_CacheLifeTime = isset($xmlArr["EASYFORM"]["ATTRIBUTES"]["CACHELIFETIME"]) ? $xmlArr["EASYFORM"]["ATTRIBUTES"]["CACHELIFETIME"] : "0";

        // parse access
        if ($this->m_Access)
        {
            $arr = explode (".", $this->m_Access);
            $this->m_Resource = $arr[0];
        }
		if ($this->m_jsClass == "jbForm" && strtoupper($this->m_FormType) == "LIST") $this->m_jsClass = "Openbiz.TableForm";
        if ($this->m_jsClass == "jbForm") $this->m_jsClass = "Openbiz.Form";
        
		$this->translate();	// translate for multi-language support
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
        $this->m_TemplateEngine   = $this->m_TemplateEngine ? $this->m_TemplateEngine : $parentObj->m_TemplateEngine;
        $this->m_TemplateFile   = $this->m_TemplateFile ? $this->m_TemplateFile : $parentObj->m_TemplateFile;        
        $this->m_FormType   = $this->m_FormType ? $this->m_FormType : $parentObj->m_FormType;
        $this->m_Range   = $this->m_Range ? $this->m_Range : $parentObj->m_Range;
        $this->m_FixSearchRule   = $this->m_FixSearchRule ? $this->m_FixSearchRule : $parentObj->m_FixSearchRule;
        $this->m_DefaultFixSearchRule   = $this->m_DefaultFixSearchRule ? $this->m_DefaultFixSearchRule : $parentObj->m_DefaultFixSearchRule;		        
        $this->m_DataObjName   = $this->m_DataObjName ? $this->m_DataObjName : $parentObj->m_DataObjName;
        $this->m_EventName   = $this->m_EventName ? $this->m_EventName : $parentObj->m_EventName;
        $this->m_MessageFile   = $this->m_MessageFile ? $this->m_MessageFile : $parentObj->m_MessageFile;
        $this->m_Messages = Resource::loadMessage($this->m_MessageFile , $this->m_Package);
		$this->m_CacheLifeTime   = $this->m_CacheLifeTime ? $this->m_CacheLifeTime : $parentObj->m_CacheLifeTime;
        
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
	
// -------------------------- Session Methods ---------------------- //
	
	public function getSessionVars($sessionContext) {}
	
	public function setSessionVars($sessionContext) {}
    
// -------------------------- Attribute Methods ---------------------- //
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
			$result = $this->getElement($elementName);
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
        if ($this->m_WizardPanel)
        {
        	if ($this->m_WizardPanel->get($elementName)) return $this->m_WizardPanel->get($elementName);
        }
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
	
	public function setRecordId($val){
    	$this->m_RecordId = $val;
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

// -------------------------- Render Methods ---------------------- //
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
        //$this->setClientScripts();

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
                $output = FormRenderer::render($this);
                $cacheSvc->save($output, $cache_id);
            }
            return $output;
        }

        //Moved the renderHTML function infront of declaring subforms
        $output = FormRenderer::render($this);

        // lazy subform loading - prepare the subforms' dataobjs, since the subform relates to parent form by dataobj association
        $this->prepareSubFormsDataObj();
		
        return $output;
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
            BizSystem::clientProxy()->redrawForm($this->m_Name, FormRenderer::render($this));
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
		$this->prepareSubFormsDataObj();
        foreach ($this->m_SubForms as $subForm)
        {
            $formObj = BizSystem::objectFactory()->getObject($subForm);
            $formObj->rerender();
        }
        return;
    }
	
	protected function prepareSubFormsDataObj()
	{
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
        $output['title'] = Expression::evaluateExpression($this->m_Title, $this);
        $output['icon'] = $this->m_Icon; 
        return $output;
    }
	
// -------------------------- Misc Methods ---------------------- //

	public function setRequestParams($paramFields) {}
	
	/**
     * Validate request from client (browser)
     * 
     * @param string $methodName called from the client
     * @return boolean
     */
    public function validateRequest($methodName)
    {
        $methodName = strtolower($methodName);
		foreach ($this->m_DirectMethodList as $value)
		{
			if ($methodName == $value) return true;
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

        // TODO: may consider cache the current record in session or pass the record from client
        if($this->getDataObj()){
	        $this->getDataObj()->setActiveRecordId($this->m_RecordId);
	        $rec = $this->getDataObj()->getActiveRecord();
	
	        // update the record row
	        $this->m_DataPanel->setRecordArr($rec);
	
	        $this->m_ActiveRecord = $rec;
        }
        return $rec;
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
  	        
		foreach($record as $key=>$value){
			//if($key=='extend')continue;
			$this->m_ActiveRecord[$key] = $record[$key];
		}
	}

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
		$this->formHelper->switchForm($formName, $id);
    }
	
	public function loadDialog($formName=null, $id=null)
    {    	
		$this->formHelper->loadDialog($formName, $id);
    }
    
// -------------------------- Tranlation Methods ---------------------- //

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