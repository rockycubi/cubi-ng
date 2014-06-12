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
 * @version   $Id: ViewRenderer.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

/**
 * ViewRenderer class is view helper for rendering form
 *
 * @package openbiz.bin.easy
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2011
 * @access public
 */
class ViewRenderer
{

    /**
     * Render view object
     *
     * @param EasyView $viewObj
     * @return string result of rendering process
     */
    static public function render ($viewObj)
    {    	
        $tplEngine = $viewObj->m_TemplateEngine;
        $tplAttributes = ViewRenderer::buildTemplateAttributes($viewObj);
        
        if(defined("PAGE_MINIFY") && PAGE_MINIFY==1)
        {
        	//if(!ob_start("ob_gzhandler")) ob_start();
        	ob_start();
        }
        
        if ($tplEngine == "Smarty" || $tplEngine == null)
            $result = ViewRenderer::renderSmarty($viewObj, $tplAttributes);
        else
            $result = ViewRenderer::renderPHP($viewObj, $tplAttributes);
       
        if(defined("PAGE_MINIFY") && PAGE_MINIFY==1)
        {        	
        	$html = ob_get_contents();
        	ob_end_clean();
        	$html = self::MinifyOutput($html);
        	echo $html;
        }    
        return $html;
    }
    
    /**
     * 
     * Minify the HTML code and rewrite the code for 
     * including CSS and JS files to make it redirect to /bin/min/index.php?f
     * @param string $html
     * @return string $html
     */
    static public function MinifyOutput($html)
    {    	
    	
    	$minifyURL = APP_URL."/bin/min/index.php";
    	$headEnd="</head>"; 

    	//fetch js requests
    	preg_match_all("/\<script.*?src\s?\=\s?[\"|\'](.*?\.js)[\"|\']/i",$html, $matches);    	
    	$jsListStr = implode(array_unique($matches[1]), ',');
    	$jsURL = $minifyURL . '?f='.$jsListStr;
    	$jsCode = "<script type=\"text/javascript\" src=\"$jsURL\"></script>";
     
    	//remove old js include
    	$html = preg_replace("/\<script.*?src\s?\=\s?[\"|\'].*?\.js[\"|\'].*?\<\/script\>/i","",$html);

    	//add new js include
    	$html = str_replace($headEnd,$jsCode."\n".$headEnd,$html);
    	
    	preg_match_all("/\<link.*?href\s?\=\s?\"(.*?\.css)\"/i",$html, $matches);
    	$cssListStr = implode(array_unique($matches[1]), ',');
    	$cssURL = $minifyURL . '?f='.$cssListStr;
    	$cssCode = "<link rel=\"stylesheet\" href=\"$cssURL\" type=\"text/css\">";
    	
    	$html = preg_replace("/\<link.*?href\s?\=\s?\"(.*?\.css)\".*?\>/i","",$html);
    	$html = str_replace($headEnd,$cssCode."\n".$headEnd,$html);  
		
    	require_once APP_HOME.'/bin/min/lib/Minify/HTML.php';
    	$html = Minify_HTML::minify($html);
    	return $html;
    } 

    /**
     * Gather all template variables needed. Should play well with Smarty or Zend templates
     *
     * @param EasyView $viewObj
     * @return array associative array holding all needed VIEW based template variables
     */
    static public function buildTemplateAttributes ($viewObj)
    {
        // Assocative Array to hold all Template Values
        // Fill with default viewobj attributes
        //$tplAttributes = $viewObj->outputAttrs();
        
        //Not sure what this is doing...
        $newClntObjs = '';
        
        //Fill other direct view variables
        $tplAttributes["module"] = $viewObj->getModuleName($viewObj->m_Name);
        $tplAttributes["description"] = $viewObj->m_Description;
        $tplAttributes["keywords"] = $viewObj->m_Keywords;
        if ($viewObj->m_Tiles) {
            foreach ($viewObj->m_Tiles as $tname => $tile) {
                foreach ($tile as $formRef) {
                    if ($formRef->m_Display == false)
                        continue;
                    $tiles[$tname][$formRef->m_Name] = BizSystem::getObject($formRef->m_Name)->render();
                    $tiletabs[$tname][$formRef->m_Name] = $formRef->m_Description;
                }
            }
        } else {
            foreach ($viewObj->m_FormRefs as $formRef) {
                if ($formRef->m_Display == false)
                    continue;
                $forms[$formRef->m_Name] = BizSystem::getObject($formRef->m_Name)->render();
                $formtabs[$formRef->m_Name] = $formRef->m_Description;
            }
        }
        
        if(count($viewObj->m_Widgets)){
    		foreach ($viewObj->m_Widgets as $formRef) {
                if ($formRef->m_Display == false)
                    continue;
                $widgets[$formRef->m_Name] = BizSystem::getObject($formRef->m_Name)->render();
            }
        }
        
        //Fill Loop related data
        $tplAttributes["forms"] = $forms;
        $tplAttributes["widgets"] = $widgets;
        $tplAttributes["formtabs"] = $formtabs;
        $tplAttributes["tiles"] = $tiles;
        $tplAttributes["tiletabs"] = $tiletabs;
        
        // add clientProxy scripts
        $includedScripts = BizSystem::clientProxy()->getAppendedScripts();
        $tplAttributes["style_sheets"] = BizSystem::clientProxy()->getAppendedStyles();
        if ($viewObj->m_IsPopup && $bReRender == false) {
            $moveToCenter = "moveToCenter(self, " . $viewObj->m_Width . ", " . $viewObj->m_Height . ");";
            $tplAttributes["scripts"] = $includedScripts . "\n<script>\n" . $newClntObjs . $moveToCenter . "</script>\n";
        } else
            $tplAttributes["scripts"] = $includedScripts . "\n<script>\n" . $newClntObjs . "</script>\n";
        
        if ($viewObj->m_Title)
            $tplAttributes["title"] = Expression::evaluateExpression($viewObj->m_Title, $viewObj);
        else
            $tplAttributes["title"] = $viewObj->m_Description;
            
    	if(DEFAULT_SYSTEM_NAME){
        	$tplAttributes["title"] = $tplAttributes["title"].' - '.DEFAULT_SYSTEM_NAME;
        }
        return $tplAttributes;
    }

    /**
     * Render smarty template for view object
     *
     * @param EasyView $viewObj
     * @param string $tplFile
     * @return string result of rendering process
     */
    static protected function renderSmarty ($viewObj, $tplAttributes = Array())
    {
        $smarty = BizSystem::getSmartyTemplate();
		
		$viewOutput = $viewObj->outputAttrs();
        foreach ($viewOutput as $k=>$v) {
            $smarty->assign($k, $v);
        }
        // render the formobj attributes
        $smarty->assign("view", $viewOutput);
		
        //Translate Array of template variables to Zend template object
        foreach ($tplAttributes as $key => $value) {
            $smarty->assign($key, $value);
        }
		$tpl = $_REQUEST['partial'] ? $viewObj->m_TemplateFile : $viewObj->m_PageTemplate;
        if ($viewObj->m_ConsoleOutput)
            $smarty->display(BizSystem::getTplFileWithPath($tpl, $viewObj->m_Package));
        else
            return $smarty->fetch(BizSystem::getTplFileWithPath($tpl, $viewObj->m_Package));
    }

    /**
     * Render PHP template for view object
     *
     * @param EasyForm $formObj
     * @param string $tplFile
     * @return string result of rendering process
     */
    static protected function renderPHP ($viewObj, $tplAttributes = Array())
    {
        $view = BizSystem::getZendTemplate();
        $tplFile = BizSystem::getTplFileWithPath($viewObj->m_TemplateFile, $viewObj->m_Package);
        $view->addScriptPath(dirname($tplFile));
        
        //Translate Array of template variables to Zend template object
        foreach ($tplAttributes as $key => $value) {
            if ($value == NULL) {
                $view->$key = '';
            } else {
                $view->$key = $value;
            }
        }
		$tpl = $_REQUEST['partial'] ? $viewObj->m_TemplateFile : $viewObj->m_PageTemplate;
        if ($viewObj->m_ConsoleOutput)
            echo $view->render($tpl);
        else
            return $view->render($tpl);
    }

    /**
     * Set headers of view
     * 
     * @param EasyView $viewObj
     * @return void
     */
    static protected function setHeaders ($viewObj)
    {
        // get the cache attribute
        // if cache = browser, set the cache control in headers
        header('Pragma:', true);
        header('Cache-Control: max-age=3600', true);
        $offset = 60 * 60 * 24 * - 1;
        $ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
        header($ExpStr, true);
    }
}
?>