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
	public $m_QueryString;

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
		$this->m_QueryString = $xmlArr["EASYFORM"]["ATTRIBUTES"]["QUERYSTRING"];
		$urlParts = parse_url($this->m_DataService);
		if (!$urlParts['host']) {
			$this->m_DataService = DEFAULT_DATASERVICE_PROVIDER.$this->m_DataService;
		}
		
		// set panel name to all elements
		foreach ($this->m_SearchPanel as $elem) $elem->setPanelName('searchPanel');
		foreach ($this->m_ActionPanel as $elem) $elem->setPanelName('actionPanel');
		foreach ($this->m_NavPanel as $elem) $elem->setPanelName('navPanel');
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
     * Get output attributs as array
     *
     * @return array array of attributs
     * @todo rename to getOutputAttribute or getAttribute (2.5?)
     */
    public function outputAttrs()
    {
        $output['name'] = $this->m_Name;
		$output['dataService'] = $this->m_DataService;
		$output['queryString'] = $this->m_QueryString;
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
     * Set request parameters
     *
     * @param array $paramFields
     * @return void
     */
    public function setRequestParams($paramFields)
    {
		if (empty($paramFields)) return;
		//print $this->m_Name; print_r($paramFields);
		$querylists = array();
		foreach ($paramFields as $k=>$v) {
			$querylist[] = "$k=$v";
		}
		$this->m_QueryString = implode('&',$querylist);
		
		// replace ':id' with $paramFields['Id'];
		if ($this->m_DataService && isset($paramFields['Id'])) {
			$this->m_DataService = str_replace(':id', $paramFields['Id'], $this->m_DataService);
		}
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
		/*
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
		*/
		if (!$this->allowAccess())
		{
           return "";
		}
        return $renderedHTML;
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
