<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.menu.widget
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: MenuWidget.php 5438 2014-05-06 07:47:10Z rockyswen@gmail.com $
 */

class MenuWidget extends MetaObject implements iUIControl {

    public $m_Title;
    public $m_Description;
	public $m_StartMenuItem;
    public $m_StartMenuID;
    public $m_SearchRule;
    public $m_GlobalSearchRule;
	public $m_MenuDeep;
	public $m_TemplateEngine;
	public $m_TemplateFile;
	public $m_DataObjName;
	public $m_CacheLifeTime;
	public $m_CssClass;
	
	public $m_DataService;
	public $m_QueryString;
	
	protected $m_DataObj;
    
    function __construct(&$xmlArr)
    {
    	$this->readMetadata($xmlArr);
        $this->translate();
    }
    
    protected function readMetaData($xmlArr)
    {
    	parent::readMetaData($xmlArr);
        $this->m_Name = $this->prefixPackage($this->m_Name);
        $this->m_Title = isset($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["TITLE"]) ? $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["TITLE"] : null;
        $this->m_Description = isset($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["DESCRIPTION"]) ? $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["DESCRIPTION"] : null;
        $this->m_CssClass = isset($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["CSSCLASS"]) ? $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["CSSCLASS"] : null;
        $this->m_TemplateEngine = isset($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["TEMPLATEENGINE"]) ? $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["TEMPLATEENGINE"] : null;
        $this->m_TemplateFile = isset($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["TEMPLATEFILE"]) ? $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["TEMPLATEFILE"] : null;
        $this->m_StartMenuItem = isset($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["STARTMENUITEM"]) ? $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["STARTMENUITEM"] : null;
        $this->m_StartMenuID = isset($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["STARTMENUID"]) ? $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["STARTMENUID"] : null;
        $this->m_SearchRule = isset($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["SEARCHRULE"]) ? $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["SEARCHRULE"] : null;
        $this->m_GlobalSearchRule = isset($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["GLOBALSEARCHRULE"]) ? $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["GLOBALSEARCHRULE"] : null;
        $this->m_MenuDeep = isset($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["MENUDEEP"]) ? $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["MENUDEEP"] : null;
        $this->m_DataObjName = $this->prefixPackage($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["BIZDATAOBJ"]);
        $this->m_CacheLifeTime = isset($xmlArr["MENUWIDGET"]["ATTRIBUTES"]["CACHELIFETIME"]) ? $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["CACHELIFETIME"] : "0";
        $this->translate();
		
		// read dataService metadata. if not a full url, add default global DATA_SERVICE_HOST as prefix
		$this->m_DataService = $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["DATASERVICE"];
		$this->m_QueryString = $xmlArr["MENUWIDGET"]["ATTRIBUTES"]["QUERYSTRING"];
		$urlParts = parse_url($this->m_DataService);
		if (!$urlParts['host']) {
			$this->m_DataService = DEFAULT_DATASERVICE_PROVIDER.$this->m_DataService;
		}
    }
    
    public function render()
    {
    	if (!$this->allowAccess())
            return "";
        if($this->m_CacheLifeTime>0)
        {
            $cache_id = md5($this->m_Name);
            //try to process cache service.
            $cacheSvc = BizSystem::getService(CACHE_SERVICE,1);
            $cacheSvc->init($this->m_Name,$this->m_CacheLifeTime);
            if($cacheSvc->test($cache_id))
            {
                BizSystem::log(LOG_DEBUG, "MENU", "Cache Hit. menu widget name = ".$this->m_Name);
                $output = $cacheSvc->load($cache_id);
            }
            else
            {
                BizSystem::log(LOG_DEBUG, "MENU", "Set cache. menu widget = ".$this->m_Name);
                $output = $this->renderHTML();
                $cacheSvc->save($output, $cache_id);
            }
            return $output;
        }
        $renderedHTML = $this->renderHTML();
        return $renderedHTML;
    }  

    protected function renderHTML()
    {
        include_once(dirname(__FILE__)."/MenuRenderer.php");   
        $sHTML = MenuRenderer::render($this);
        return $sHTML;
    }    

    public function fetchMenuTree(){
    	$dataObj = $this->getDataObj();
    	if ($this->m_SearchRule!="") {
    		$tree = $dataObj->fetchTreeBySearchRule($this->m_SearchRule, $this->m_MenuDeep, $this->m_GlobalSearchRule);
    	}else if($this->m_StartMenuID!=""){
    		$tree = $dataObj->fetchTree($this->m_StartMenuID, $this->m_MenuDeep);
    	}else{
    		$tree = $dataObj->fetchTreeByName($this->m_StartMenuItem, $this->m_MenuDeep);
    	}
    	return $tree; 
    }

    public function outputAttrs(){
    	$attrs = array();
    	$attrs['name'] = $this->m_Name;
    	$attrs['title'] = $this->m_Title;
    	$attrs['css'] = $this->m_CssClass;
    	$attrs['description'] = $this->m_Description;
		$attrs['dataService'] = $this->m_DataService;
		$attrs['queryString'] = $this->m_QueryString;
    	//$attrs['menu'] = $this->fetchMenuTree();
    	//$attrs['breadcrumb']= $this->getDataObj()->getBreadCrumb();
    	//if ($this->m_Name=="menu.widget.MainTabMenu") { print_r($attrs['menu']);   print_r($attrs['breadcrumb']);  }
    	return $attrs;
    }
    
    protected function prefixPackage($name)
    {
        if ($name && !strpos($name, ".") && ($this->m_Package)) // no package prefix as package.object, add it
            $name = $this->m_Package.".".$name;

        return $name;
    } 
    
    final public function getDataObj()
    {
        if (!$this->m_DataObj)
        {        	
            if ($this->m_DataObjName)
                $this->m_DataObj = BizSystem::getObject($this->m_DataObjName,1);
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

    public function setRequestParams(){
    	
    }
    
    protected function translate()
    {
    	$module = $this->getModuleName($this->m_Name);
    	$this->m_Title = I18n::t($this->m_Title, $this->getTransKey('Title'), $module);
        $this->m_Description = I18n::t($this->m_Description, $this->getTransKey('Description'), $module);
    }
    
    protected function getTransKey($name)
    {
    	$shortFormName = substr($this->m_Name,intval(strrpos($this->m_Name,'.'))+1);
    	return strtoupper($shortFormName.'_'.$name);
    }
    public function getModuleName($name)
    {
    	return substr($name,0,intval(strpos($name,'.')));
    }    
}
?>