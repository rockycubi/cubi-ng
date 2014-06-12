<?PHP

/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: Resource.php 4179 2011-05-26 07:40:53Z rockys $
 */

/**
 * Resource class
 *
 * @package   openbiz.bin
 * @author    Rocky Swen <rocky@phpopenbiz.org>
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 * @todo loadMessage(), 
 *       getXmlFileWithPath(), 
 *       getTplFileWithPath, 
 *       getLibFileWithPath
 */
class Resource
{
    private static $_imageUrl;
    private static $_cssUrl;
    private static $_jsUrl;
    private static $_currentTheme;
	private static $_xmlFileList;
	private static $_xmlArrayList;

    const DEFAULT_THEME = THEME_NAME;
    /**
     * Load message from file
     *
     * @param string $messageFile
     * @return mixed
     */
    public static function loadMessage($messageFile, $packageName="")
    {
        if (isset($messageFile) && $messageFile != "")
        {

            // message file location order 
            // 1. MESSAGE_PATH."/".$messageFile
            // 2. MODULE_PATH . "/$moduleName/message/" . $messageFile;
            // 3. CORE_MODULE_PATH . "/$moduleName/message/" . $messageFile;
            // APP_HOME / MESSAGE_PATH : APP_HOME / messages
            if (is_file(MESSAGE_PATH . "/" . $messageFile)) {
                return parse_ini_file(MESSAGE_PATH . "/" . $messageFile);
            } else if (is_file(MODULE_PATH . "/" . $messageFile)) {
				return parse_ini_file(MODULE_PATH . "/" . $messageFile);
			} else {
                if (isset($packageName) && $packageName != "")
                {
                    $dirs = explode('.', $packageName);
                    $moduleName = $dirs[0];
                    $msgFile = MODULE_PATH . "/$moduleName/message/" . $messageFile;
                    if (is_file($msgFile))
                    {
                        return parse_ini_file($msgFile);
                    } else
                    {
                        $errmsg = self::getMessage("SYS_ERROR_INVALID_MSGFILE", array($msgFile));
                        trigger_error($errmsg, E_USER_ERROR);
                    }
                } else
                {
                    $errmsg = self::getMessage("SYS_ERROR_INVALID_MSGFILE", array(MESSAGE_PATH . "/" . $messageFile));
                    trigger_error($errmsg, E_USER_ERROR);
                }
            }
        }
        return null;
    }

    /**
     * Get message from CONSTANT, translate and format it
     * @param string $msgId ID if constant
     * @param array $params parameter for format (use vsprintf)
     * @return string
     */
    public static function getMessage($msgId, $params=array())
    {
        $message = constant($msgId);
        if (isset($message))
        {
            $message = I18n::t($message, $msgId, 'system');
            $result = vsprintf($message, $params);
        }
        return $result;
    }

    /**
     * Get image URL
     * @return string
     */
    public static function getImageUrl()
    {
        if (isset(self::$_imageUrl))
            return self::$_imageUrl;
        $useTheme = !defined('USE_THEME') ? 0 : USE_THEME;
        $themeUrl = !defined('THEME_URL') ? "../themes" : THEME_URL;
        $themeName = Resource::getCurrentTheme();
        if ($useTheme)
            self::$_imageUrl = "$themeUrl/$themeName/images";
        else
            self::$_imageUrl = "../images";

        return self::$_imageUrl;
    }

    /**
     * Get CSS URL
     * @return string
     */
    public static function getCssUrl()
    {
        if (isset(self::$_cssUrl))
            return self::$_cssUrl;
        $useTheme = !defined('USE_THEME') ? 0 : USE_THEME;
        $themeUrl = !defined('THEME_URL') ? APP_URL . "/themes" : THEME_URL;
		$themeName = Resource::getCurrentTheme();
        if ($useTheme)
            self::$_cssUrl = "$themeUrl/$themeName/css";
        else
            self::$_cssUrl = APP_URL . "/css";
        return self::$_cssUrl;
    }

    /**
     * Get JavaScript(JS) URL
     * @return string
     */
    public static function getJsUrl()
    {
        if (isset(self::$_jsUrl))
            return self::$_jsUrl;
        self::$_jsUrl = !defined('JS_URL') ? APP_URL . "/js" : JS_URL;
        return self::$_jsUrl;
    }

    /**
     * Get smarty template
     * @return Smarty smarty object
     */
    public static function getSmartyTemplate()
    {
    	if(extension_loaded('ionCube Loader')){
        	include_once(SMARTY_DIR . "Smarty.class.php");
    	}else{
    		include_once(SMARTY_DIR . "Smarty.class.src.php");
    	}
        $smarty = new Smarty;

        $useTheme = !defined('USE_THEME') ? 0 : USE_THEME;
        if ($useTheme)
        {
            $theme = Resource::getCurrentTheme();
            $themePath = $theme;    // BizSystem::configuration()->GetThemePath($theme);
            if (is_dir(THEME_PATH . "/" . $themePath . "/template"))
            {
                $templateRoot = THEME_PATH . "/" . $themePath . "/template";
            } else
            {
                $templateRoot = THEME_PATH . "/" . $themePath . "/templates";
            }
            $smarty->template_dir = $templateRoot;
            $smarty->compile_dir = defined('SMARTY_CPL_PATH') ? SMARTY_CPL_PATH."/".$themePath : $templateRoot . "/cpl";
            $smarty->config_dir = $templateRoot . "/cfg";
			if (!file_exists($smarty->compile_dir)) {
                @mkdir($smarty->compile_dir, 0777);
            }
            // load the config file which has the images and css url defined
            $smarty->config_load('tpl.conf');
        } else
        {
            if (defined('SMARTY_TPL_PATH'))
                $smarty->template_dir = SMARTY_TPL_PATH;
            if (defined('SMARTY_CPL_PATH'))
                $smarty->compile_dir = SMARTY_CPL_PATH."/".$themePath;
            if (defined('SMARTY_CFG_PATH'))
                $smarty->config_dir = SMARTY_CFG_PATH;
        }
        if(!is_dir($smarty->compile_dir)){
        	mkdir($smarty->compile_dir,0777);
        }
        // load the config file which has the images and css url defined
        $smarty->assign('app_url', APP_URL);
        $smarty->assign('app_index', APP_INDEX);
        $smarty->assign('js_url', JS_URL);
        $smarty->assign('css_url', THEME_URL . "/" . $theme . "/css");
        $smarty->assign('resource_url', RESOURCE_URL );
        $smarty->assign('resource_php', RESOURCE_PHP );
        $smarty->assign('theme_js_url', THEME_URL . "/" . $theme . "/js");
        $smarty->assign('theme_url', THEME_URL . "/" . $theme);
        $smarty->assign('image_url', THEME_URL . "/" . $theme . "/images");
        $smarty->assign('lang', strtolower(I18n::getCurrentLangCode()));
        $smarty->assign('lang_name', I18n::getCurrentLangCode());

        return $smarty;
    }

    /**
     * Get Zend Template
     * @return Zend_View zend view template object
     */
    public static function getZendTemplate()
    {
        // now assign the book data to a Zend_View instance
        //Zend_Loader::loadClass('Zend_View');
        require_once 'Zend/View.php';
        $view = new Zend_View();
        if (defined('SMARTY_TPL_PATH'))
            $view->setScriptPath(SMARTY_TPL_PATH);

        $theme = Resource::getCurrentTheme();            
            
        // load the config file which has the images and css url defined
        $view->app_url = APP_URL;
        $view->app_index = APP_INDEX;
        $view->js_url = JS_URL;
        $view->css_url = THEME_URL . "/" . $theme . "/css";
        $view->resource_url = RESOURCE_URL;    
        $view->theme_js_url = THEME_URL . "/" . $theme . "/js";
        $view->theme_url = THEME_URL . "/" . $theme;
        $view->image_url = THEME_URL . "/" . $theme . "/images";
        $view->lang = strtolower(I18n::getCurrentLangCode());            
            
        return $view;
    }

    /**
     * Get Xml file with path
     *
     * Search the object metedata file as objname+.xml in metedata directories
     * name convension: demo.BOEvent points to metadata/demo/BOEvent.xml
     * new in 2.2.3, demo.BOEvent can point to modules/demo/BOEvent.xml
     *
     * @param string $xmlObj xml object
     * @return string xml config file path
     * */
    public static function getXmlFileWithPath($xmlObj)
    {
        if (isset(self::$_xmlFileList[$xmlObj])) {
			return self::$_xmlFileList[$xmlObj];
		}
		$xmlFile = $xmlObj;
        if (strpos($xmlObj, ".xml") > 0)  // remove .xml suffix if any
            $xmlFile = substr($xmlObj, 0, strlen($xmlObj) - 4);

        // replace "." with "/"
        $xmlFile = str_replace(".", "/", $xmlFile);
        // check the leading char '@'
        $checkExtModule = true;
        if (strpos($xmlFile, '@') === 0) {
            $xmlFile = substr($xmlFile, 1);
            $checkExtModule = false;
        }
        $xmlFile .= ".xml";
        $xmlFile = "/" . $xmlFile;
		
		// find device path first
        if (defined('CLIENT_DEVICE')) {
            $path = dirname($xmlFile);
            if (strpos($path, 'view')>0 || strpos($path, 'form')>0 || strpos($path, 'widget')>0) {
                $fname = basename($xmlFile);
                $xmlFileList[] = MODULE_PATH."/$path/".CLIENT_DEVICE."/$fname";
            }
        }
        
        // search in modules directory first
        $xmlFileList[] = MODULE_PATH . $xmlFile;
        $xmlFileList[] = APP_HOME . $xmlFile;
        $xmlFileList[] = OPENBIZ_META . $xmlFile;
        if ($checkExtModule && defined('MODULE_EX_PATH')) array_unshift($xmlFileList, MODULE_EX_PATH . $xmlFile);

        foreach ($xmlFileList as $xmlFileItem)
        {
            //echo "file_exists($xmlFileItem)\n";
			if (file_exists($xmlFileItem)) {
				self::$_xmlFileList[$xmlObj] = $xmlFileItem;
                return $xmlFileItem;
			}
        }
		self::$_xmlFileList[$xmlObj] = null;
        return null;
    }

    /**
     * Get openbiz template file path by searching modules/package, /templates
     *
     * @param string $className
     * @return string php library file path
     * */
    public static function getTplFileWithPath($templateFile, $packageName)
    {
        //for not changing a lot things, the best injection point is added theme support here.
		$theme = Resource::getCurrentTheme();
        $themePath = $theme;    // BizSystem::configuration()->GetThemePath($theme);
        if ($themePath)
            $templateRoot = THEME_PATH . "/" . $themePath . "/template";
        else
            $templateRoot = SMARTY_TPL_PATH;

        $names = explode(".", $packageName);
        if (count($names) > 0)
            $moduleName = $names[0];
        $packagePath = str_replace('.', '/', $packageName);
        // check the leading char '@'
        $checkExtModule = true;
        if (strpos($packagePath, '@') === 0) {
            $packagePath = substr($packagePath, 1);
            $checkExtModule = false;
        }
        
        $searchTpls = array(
            MODULE_PATH . "/$packagePath/template/$templateFile",
            dirname(MODULE_PATH . "/$packagePath") . "/template/$templateFile",
            MODULE_PATH . "/$moduleName/template/$templateFile",
            //MODULE_PATH."/common/template/$templateFile",
            $templateRoot . "/$templateFile"
        );
        if ($checkExtModule && defined('MODULE_EX_PATH')) array_unshift($searchTpls, MODULE_EX_PATH . "/$packagePath/template/$templateFile");
		
		// device
		if (defined('CLIENT_DEVICE')) array_unshift($searchTpls, MODULE_PATH."/$moduleName/template/".CLIENT_DEVICE."/$templateFile");
		
        foreach ($searchTpls as $tplFile)
        {
            if (file_exists($tplFile))
            {
                return $tplFile;
            }
        }
        $errmsg = BizSystem::getMessage("UNABLE_TO_LOCATE_TEMPLATE_FILE", array($templateFile));
        trigger_error($errmsg, E_USER_ERROR);
        return null;
    }

    /**
     * Get openbiz library php file path by searching modules/package, /bin/package and /bin
     *
     * @param string $className
     * @return string php library file path
     * */
    public static function getLibFileWithPath($className, $packageName="")
    {
        return BizClassLoader::getLibFileWithPath($className, $packageName);
    }

    
    /**
     * Get core path of class
     *
     * @param string $className class name
     * @return string full file name of class
     */    
    public static function getCoreLibFilePath($className) {
        return BizClassLoader::getCoreLibFilePath($className);
    }
    

    /**
     * Get Xml Array.
     * If xml file has been compiled (has .cmp), load the cmp file as array;
     * otherwise, compile the .xml to .cmp first new 2.2.3, .cmp files
     * will be created in app/cache/metadata_cmp directory. replace '/' with '_'
     * for example, /module/demo/BOEvent.xml has cmp file as _module_demo_BOEvent.xml
     *
     * @param string $xmlFile
     * @return array
     * */
    public static function &getXmlArray($xmlFile)
    {
		if (isset(self::$_xmlArrayList[$xmlFile])) {
			return self::$_xmlArrayList[$xmlFile];
		}
        $objXmlFileName = $xmlFile;
		//echo "getXmlArray($xmlFile)\n";
        //$objCmpFileName = dirname($objXmlFileName) . "/__cmp/" . basename($objXmlFileName, "xml") . ".cmp";
        //$_crc32 = sprintf('%08X', crc32(dirname($objXmlFileName)));
        $_crc32 = strtoupper(md5(dirname($objXmlFileName)));
        $objCmpFileName = CACHE_METADATA_PATH . '/' . $_crc32 . '_'
                . basename($objXmlFileName, "xml") . "cmp";

        $xmlArr = null;
        //$cacheKey = substr($objXmlFileName, strlen(META_PATH)+1);
        $cacheKey = $objXmlFileName;
        $findInCache = false;
        if (file_exists($objCmpFileName)
                && (filemtime($objCmpFileName) > filemtime($objXmlFileName)))
        {
            // search in cache first
            if (!$xmlArr && extension_loaded('apc'))
            {
                if (($xmlArr = apc_fetch($cacheKey)) != null)
                {
                    $findInCache = true;
                }
            }
            if (!$xmlArr)
            {
                $content_array = file($objCmpFileName);
                $xmlArr = unserialize(implode("", $content_array));
            }
        } else
        {
        	if(extension_loaded('ionCube Loader')){
            	include_once(OPENBIZ_BIN . "util/xmltoarray.php");
        	}else{
        		include_once(OPENBIZ_BIN . "util/xmltoarray.src.php");
        	}
            $parser = new XMLParser($objXmlFileName, 'file', 1);
            $xmlArr = $parser->getTree();
            // simple validate the xml array
            $root_keys = array_keys($xmlArr);
            $root_key = $root_keys[0];
            if (!$root_key || $root_key == "")
            {
                trigger_error("Metadata file parsing error for file $objXmlFileName. Please double check your metadata xml file again.", E_USER_ERROR);
            }
            $xmlArrStr = serialize($xmlArr);
            if (!file_exists(dirname($objCmpFileName)))
                mkdir(dirname($objCmpFileName));
            $cmp_file = fopen($objCmpFileName, 'w') or die("can't open cmp file to write");
            fwrite($cmp_file, $xmlArrStr) or die("can't write to the cmp file");
            fclose($cmp_file);
        }
        // save to cache to avoid file processing overhead
        if (!$findInCache && extension_loaded('apc'))
        {
            apc_store($cacheKey, $xmlArr);
        }
		self::$_xmlArrayList[$xmlFile] = $xmlArr;
        return $xmlArr;
    }
	
	// theme selection priority: url, session, userpref, system(constant)
	public static function getCurrentTheme ()
    {
    	if (Resource::$_currentTheme != null)
            return Resource::$_currentTheme;
        
		$currentTheme = "";
		if (isset($_GET['theme'])){
            $currentTheme = $_GET['theme'];
        }
		if ($currentTheme == ""){
        	$currentTheme = BizSystem::sessionContext()->getVar("THEME");
        }
		if ($currentTheme == ""){
        	$currentTheme = BizSystem::getUserPreference("theme");
        }
        if ($currentTheme == "" && defined('THEME_NAME')) {
			$currentTheme = THEME_NAME;
		}
        if($currentTheme == ""){
            $currentTheme = Resource::DEFAULT_THEME;
        }
        // TODO: user pereference has language setting
        
        BizSystem::sessionContext()->setVar("THEME", $currentTheme);
        Resource::$_currentTheme = $currentTheme;
        
        return $currentTheme;
    }
    
    /**
     * class map for openbiz core class
     * @author agus suhartono
     * @var array
     */
    /*
    public static $coreClassMap = array(
            "BizController" => "/bin/BizController.php",
            "BizSystem" => "/bin/BizSystem.php",
            "ClientProxy" => "/bin/ClientProxy.php",
            "Configuration" => "/bin/Configuration.php",
            "OB_ErrorHandler" => "/bin/ErrorHandler",
            "Expression" => "/bin/Expression.php",
            "I18n" => "/bin/I18n.php",
            "ObjectFactory" => "/bin/ObjectFactory.php",
            "Resource" => "/bin/Resource.php",
            "SessionContext" => "/bin/SessionContext.php",
            "TypeManager" => "/bin/TypeManager.php",
            "UserSetting" => "/bin/UserSetting.php",
            "BDOException" => "/bin/sysclass_inc.php",
            "BFMException" => "/bin/sysclass_inc.php",
            "BSVCException" => "/bin/sysclass_inc.php",
            "MetaIterator" => "/bin/sysclass_inc.php",
            "MetaObject" => "/bin/sysclass_inc.php",
            "Parameter" => "/bin/sysclass_inc.php",
            "ValidationException" => "/bin/sysclass_inc.php",
            "iSessionObject" => "/bin/sysclass_inc.php",
            "iUIControl" => "/bin/sysclass_inc.php",
            
            "BizDataObj" => "/bin/data/BizDataObj.php",
            "BizDataObj_Abstract" => "/bin/data/BizDataObj_Abstract.php",
            "BizDataObj_Lite" => "/bin/data/BizDataObj_Lite.php",
            "BizDataSql" => "/bin/data/BizDataSql.php",
            "BizDataTree" => "/bin/data/BizDataTree.php",
            "NodeRecord" => "/bin/data/BizDataTree.php",            
            "BizField" => "/bin/data/BizField.php",
            "DataRecord" => "/bin/data/DataRecord.php",
            "DataSet" => "/bin/data/DataSet.php",
            "BizDataObj_Assoc" => "/bin/data/private/BizDataObj_Assoc.php",
            "BizDataObj_SQLHelper" => "/bin/data/private/BizDataObj_SQLHelper.php",
            "BizRecord" => "/bin/data/private/BizRecord.php",
            "ObjReference" => "/bin/data/private/ObjReference.php",
            "TableJoin" => "/bin/data/private/TableJoin.php",            
            
            "DynaView" => "/bin/easy/DynaView.php",
            "EasyForm" => "/bin/easy/EasyForm.php",
            "EasyFormGrouping" => "/bin/easy/EasyFormGrouping.php",
            "EasyFormTree" => "/bin/easy/EasyFormTree.php",
            "EasyFormWizard" => "/bin/easy/EasyFormWizard.php",
            "EasyView" => "/bin/easy/EasyView.php",
            "EasyViewWizard" => "/bin/easy/EasyViewWizard.php",
            "FormRenderer" => "/bin/easy/FormRenderer.php",
            "HTMLMenus" => "/bin/easy/HTMLMenus.php",
            "HTMLTabs" => "/bin/easy/HTMLTabs.php",
            "TabView" => "/bin/easy/HTMLTabs.php",            
            "HTMLTree" => "/bin/easy/HTMLTree.php",
            "Panel" => "/bin/easy/Panel.php",
            "PickerForm" => "/bin/easy/PickerForm.php",
            "ViewRenderer" => "/bin/easy/ViewRenderer.php",
            
            "AutoSuggest" => "/bin/easy/element/AutoSuggest.php",
            "Button" => "/bin/easy/element/Button.php",
            "CKEditor" => "/bin/easy/element/CKEditor.php",
            "CheckListbox" => "/bin/easy/element/CheckListbox.php",
            "Checkbox" => "/bin/easy/element/Checkbox.php",
            "ColorPicker" => "/bin/easy/element/ColorPicker.php",
            "ColumnBar" => "/bin/easy/element/ColumnBar.php",
            "ColumnBool" => "/bin/easy/element/ColumnBool.php",
            "ColumnHidden" => "/bin/easy/element/ColumnHidden.php",
            "ColumnImage" => "/bin/easy/element/ColumnImage.php",
            "ColumnList" => "/bin/easy/element/ColumnList.php",
            "ColumnPassword" => "/bin/easy/element/ColumnPassword.php",
            "ColumnShare" => "/bin/easy/element/ColumnShare.php",
            "ColumnSorting" => "/bin/easy/element/ColumnSorting.php",
            "ColumnStyle" => "/bin/easy/element/ColumnStyle.php",
            "ColumnText" => "/bin/easy/element/ColumnText.php",
            "ColumnValue" => "/bin/easy/element/ColumnValue.php",
            "DropDownList" => "/bin/easy/element/DropDownList.php",
            "EditCombobox" => "/bin/easy/element/EditCombobox.php",
            "Element" => "/bin/easy/element/Element.php",
            "File" => "/bin/easy/element/File.php",
            "FileUploader" => "/bin/easy/element/FileUploader.php",
            "FormElement" => "/bin/easy/element/FormElement.php",
            "HTMLBlock" => "/bin/easy/element/HTMLBlock.php",
            "HTMLButton" => "/bin/easy/element/HTMLButton.php",
            "Hidden" => "/bin/easy/element/Hidden.php",
            "IDCardReader" => "/bin/easy/element/IDCardReader.php",
            "IFrameBox" => "/bin/easy/element/IFrameBox.php",
            "ImageSelector" => "/bin/easy/element/ImageSelector.php",
            "ImageUploader" => "/bin/easy/element/ImageUploader.php",
            "InputDate" => "/bin/easy/element/InputDate.php",
            "InputDateRangePicker" => "/bin/easy/element/InputDateRangePicker.php",
            "InputDatetime" => "/bin/easy/element/InputDatetime.php",
            "InputElement" => "/bin/easy/element/InputElement.php",
            "InputPassword" => "/bin/easy/element/InputPassword.php",
            "InputPicker" => "/bin/easy/element/InputPicker.php",
            "InputText" => "/bin/easy/element/InputText.php",
            "LabelBar" => "/bin/easy/element/LabelBar.php",
            "LabelBool" => "/bin/easy/element/LabelBool.php",
            "LabelImage" => "/bin/easy/element/LabelImage.php",
            "LabelList" => "/bin/easy/element/LabelList.php",
            "LabelPassword" => "/bin/easy/element/LabelPassword.php",
            "LabelText" => "/bin/easy/element/LabelText.php",
            "LabelTextPaging" => "/bin/easy/element/LabelTextPaging.php",
            "LabelTextarea" => "/bin/easy/element/LabelTextarea.php",
            "Listbox" => "/bin/easy/element/Listbox.php",
            "OptionElement" => "/bin/easy/element/OptionElement.php",
            "PageSelector" => "/bin/easy/element/PageSelector.php",
            "PagesizeSelector" => "/bin/easy/element/PagesizeSelector.php",
            "Password" => "/bin/easy/element/Password.php",
            "Radio" => "/bin/easy/element/Radio.php",
            "RawData" => "/bin/easy/element/RawData.php",
            "ResetButton" => "/bin/easy/element/ResetButton.php",
            "RichText" => "/bin/easy/element/RichText.php",
            "RowCheckbox" => "/bin/easy/element/RowCheckbox.php",
            "Spacer" => "/bin/easy/element/Spacer.php",
            "SubmitButton" => "/bin/easy/element/SubmitButton.php",
            "Textarea" => "/bin/easy/element/Textarea.php",
            "TreeLabelText" => "/bin/easy/element/TreeLabelText.php",
            "TreeListbox" => "/bin/easy/element/TreeListbox.php",
   
            "accessService" => "/bin/service/accessService.php",
            "aclService" => "/bin/service/aclService.php",
            "auditService" => "/bin/service/auditService.php",
            "authService" => "/bin/service/authService.php",
            "cacheService" => "/bin/service/cacheService.php",
            "chartService" => "/bin/service/chartService.php",
            "compileService" => "/bin/service/compileService.php",
            "cryptService" => "/bin/service/cryptService.php",
            "doTriggerService" => "/bin/service/doTriggerService.php",
            "emailService" => "/bin/service/emailService.php",
            "excelService" => "/bin/service/excelService.php",
            "genIdService" => "/bin/service/genIdService.php",
            "ioService" => "/bin/service/ioService.php",
            "localeInfoService" => "/bin/service/localeInfoService.php",
            "logService" => "/bin/service/logService.php",
            "pdfService" => "/bin/service/pdfService.php",
            "profileService" => "/bin/service/profileService.php",
            "queryService" => "/bin/service/queryService.php",
            "reportService" => "/bin/service/reportService.php",
            "securityService" => "/bin/service/securityService.php",
            "validateService" => "/bin/service/validateService.php",

            "QueryStringParam" => "/bin/util/QueryStringParam.php",
            "XMLParser" => "/bin/util/xmltoarray.php",
            
            "Smarty" => "/others/Smarty/libs/Smarty.class.php",
        
        );
     * 
     */
    
}
