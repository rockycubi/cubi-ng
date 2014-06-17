<?php
$XX = 0;
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
 * @version   $Id: I18n.php 5154 2013-01-16 09:48:03Z rockyswen@gmail.com $
 */

require_once 'Zend/Translate.php';
require_once 'Zend/Locale.php';

/**
 * I18n (Internationalization class) is singleton class that tranlates string
 * to different languages according to application translation files.
 *
 * @package   openbiz.bin
 * @author    Rocky Swen <rocky@phpopenbiz.org>
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class I18n
{
    const LANGUAGE_PATH_1 = "languages";
    const LANGUAGE_PATH_2 = "LC_MESSAGES";
    const DEFAULT_LANGUAGE = DEFAULT_LANGUAGE;
    
    protected static $_langData;
    protected static $_langCode;
    
    public static function t($text, $key=null, $module, $prefix=null)
    {
    	// TODO: use cache, apc cache? special handling for menu?
    	
    	//echo "to translate $text, $key, $module".nl;
    	if (!I18n::loadLangData($module))	// cannot load lang data, return orig text
			return $text;
					
		if ($key && isset(I18n::$_langData[$module][$prefix.$key]) && I18n::$_langData[$module][$prefix.$key]!=$text)
    		return I18n::$_langData[$module][$prefix.$key];	
			    	    	
    	$str_key = strtoupper('STRING_'.md5($text));
    	if ($key && isset(I18n::$_langData[$module][$str_key])  && I18n::$_langData[$module][$str_key]!=$text)
    		return I18n::$_langData[$module][$str_key];
    	
    	if ($key && isset(I18n::$_langData[$module][$key]))
    		return I18n::$_langData[$module][$key];	
    		
		// try to load theme.THEME_NAME.ini
		$module = '_theme';
		if (!I18n::loadLangData($module))
			return $text;
		
    	if ($key && isset(I18n::$_langData[$module][$key]))
    		return I18n::$_langData[$module][$key];
		
    	// try to load system.ini if previous steps can't find match
    	$module != '_system';
    	if (!I18n::loadLangData($module))
			return $text;
		
    	if ($key && isset(I18n::$_langData[$module][$key]))
    		return I18n::$_langData[$module][$key];

    	return $text;
    }
    
    protected static function loadLangData($module)
    {
    	if (isset(I18n::$_langData[$module])) {
    		return true;
    	}
    	
    	// get language code
    	$langCode = I18n::getCurrentLangCode();
    	
    	// load language file
    	if ($module == '_system') $filename = 'system.ini';
		else if ($module == '_theme') { $filename = 'theme.'.THEME_NAME.'.ini'; }
    	else $filename = "mod.$module.ini";
    	$langFile = LANGUAGE_PATH."/$langCode/$filename";
    	//echo "check ini file $langFile".nl;
    	if (!file_exists($langFile)) {
			I18n::$_langData[$module] = array();
			return false;
    	}
    	//echo "parse ini file $langFile".nl;
    	$inidata = parse_ini_file($langFile, false);
    	
    	I18n::$_langData[$module] = $inidata;
    	//print_r(I18n::$_langData[$module]);

    	return true;
    } 
    
	public static function AddLangData($from_module,$to_module=null)	
    {    	        	
    	if($to_module==null){
    		$to_module = $from_module;
    	}
    	$langCode = I18n::getCurrentLangCode();    	    	    
    	$filename = "mod.$from_module.ini";
    	$langFile = LANGUAGE_PATH."/$langCode/$filename";    	
    	if (!file_exists($langFile)) return false;    	    	
    	$inidata = parse_ini_file($langFile, false);
    	if(is_array(I18n::$_langData[$to_module])){
    		I18n::$_langData[$to_module] = array_merge(I18n::$_langData[$to_module],$inidata);
    	}else{
    		I18n::$_langData[$to_module] = $inidata;
    	}
    	return true;
    }     
    
	public static function getCurrentLangCode ()
    {
    	if (I18n::$_langCode != null)
            return I18n::$_langCode;
        $currentLanguage = BizSystem::sessionContext()->getVar("LANG");        
        // default language
        if ($currentLanguage == ""){
        	$currentLanguage = BizSystem::getUserPreference("language");
        	
        }
        if($currentLanguage == ""){
            $currentLanguage = I18n::DEFAULT_LANGUAGE;
        }
        // language from url
        if (isset($_GET['lang'])){
            $currentLanguage = $_GET['lang'];
            BizSystem::sessionContext()->setVar("LANG",$currentLanguage );
        }

        // TODO: user pereference has language setting
        
        BizSystem::sessionContext()->setVar("LANG", $currentLanguage);
        I18n::$_langCode = $currentLanguage;
        
        return $currentLanguage;
    }
}
?>