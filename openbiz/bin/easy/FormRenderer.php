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
 * @version   $Id: FormRenderer.php 4075 2011-05-02 13:43:39Z jixian2003 $
 */

/**
 * FormRenderer class is form helper for rendering form
 *
 * @package openbiz.bin.easy
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2011
 * @access public
 */
class FormRenderer
{

    /**
     * Render form object
     *
     * @param EasyForm $formObj
     * @return string result of rendering process
     */
    static public function render ($formObj)
    {
        $tplEngine = $formObj->m_TemplateEngine;
        $tplAttributes = FormRenderer::buildTemplateAttributes($formObj); 
        /*if (isset($formObj->m_jsClass)) {
            $subForms = ($formObj->m_SubForms) ? implode(";", $formObj->m_SubForms) : "";
            if ($formObj->m_StaticOutput != true) {
                $formScript = "\n<script>Openbiz.newFormObject('$formObj->m_Name','$formObj->m_jsClass','$subForms'); </script>\n";
            }
            if ($formObj->m_AutoRefresh > 0) {
                $formScript .= "\n<script>setTimeout(\"Openbiz.CallFunction('$formObj->m_Name.UpdateForm()');\",\"" . ($formObj->m_AutoRefresh * 1000) . "\") </script>\n";
            }
        }*/
        
        if ($tplEngine == "Smarty" || $tplEngine == null)
            return FormRenderer::renderSmarty($formObj, $tplAttributes) . $formScript;
        else
            return FormRenderer::renderPHP($formObj, $tplAttributes) . $formScript;
    }

    /**
     * Gather all template variables needed. Should play well with Smarty or Zend templates
     *
     * @param EasyView $formObj
     * @return array associative array holding all needed VIEW based template variables
     */
    static public function buildTemplateAttributes ($formObj)
    {
        // Assocative Array to hold all Template Values
        // Fill with default viewobj attributes
        $tplAttributes = array();

        $tplAttributes['title'] = $formObj->m_Title;
        $tplAttributes['errors'] = $formObj->m_Errors;
        $tplAttributes['notices'] = $formObj->m_Notices;
        $tplAttributes['formname'] = $formObj->m_Name;
        $tplAttributes['module'] = $formObj->getModuleName($formObj->m_Name);
        
        // if the $formobj form type is list render table, otherwise render record
        if (strtoupper($formObj->m_FormType) == 'LIST') {
            $tplAttributes['dataPanel'] = $formObj->m_DataPanel->render();
        } else {
            $tplAttributes['dataPanel'] = $formObj->m_DataPanel->render();
        }
        
        if (isset($formObj->m_SearchPanel)) {
            $search_record = $formObj->m_SearchPanelValues;
            foreach ($formObj->m_SearchPanel as $elem) {
                if (! $elem->m_FieldName)
                    continue;
                $post_value = BizSystem::clientProxy()->getFormInputs($elem->m_Name);
                if ($post_value) {
                    $search_record[$elem->m_FieldName] = $post_value;
                }
            }
            $tplAttributes['searchPanel'] = $formObj->m_SearchPanel->renderRecord($search_record);
        } else {
            $tplAttributes['searchPanel'] = $formObj->m_SearchPanel->render();
        }
        $tplAttributes['actionPanel'] = $formObj->m_ActionPanel->render();
        $tplAttributes['navPanel'] = $formObj->m_NavPanel->render();
        if($formObj->m_WizardPanel)
        {
        	$tplAttributes['wizardPanel'] = $formObj->m_WizardPanel->render();
        }
                
        $tplAttributes['form'] = $formObj->outputAttrs();
		$outputAttrs = $formObj->outputAttrs();
		foreach ($outputAttrs as $k=>$v) {
			$tplAttributes[$k] = $v;
		}
        
        return $tplAttributes;
    }

    /**
     * Render smarty template for form object
     *
     * @param EasyForm $formObj
     * @param string $tplFile
     * @return string result of rendering process
     */
    static protected function renderSmarty ($formObj, $tplAttributes = Array())
    {
        $smarty = BizSystem::getSmartyTemplate();
        $tplFile = BizSystem::getTplFileWithPath($formObj->m_TemplateFile, $formObj->m_Package);
                
        //Translate Array of template variables to Zend template object
		//print_r($tplAttributes);
        foreach ($tplAttributes as $key => $value) {
            $smarty->assign($key, $value);
        };     
        
        return $smarty->fetch($tplFile);
    }

    /**
     * Render PHP template for form object
     *
     * @param EasyForm $formObj
     * @param string $tplFile
     * @return string result of rendering process
     */
    static protected function renderPHP ($formObj, $tplAttributes = Array())
    {
        $form = BizSystem::getZendTemplate();
        $tplFile = BizSystem::getTplFileWithPath($formObj->m_TemplateFile, $formObj->m_Package);
        $form->addScriptPath(dirname($tplFile));
        
        /*$formOutput = $formObj->outputAttrs();
        foreach ($formOutput as $k=>$v) {
            $form->$k = $v;
        }*/

        foreach ($tplAttributes as $key => $value) {
            if ($value == NULL) {
                $form->$key = '';
            } else {
                $form->$key = $value;
            }
        }
        
        // render the formobj attributes
        //$form->form = $formOutput;

        return $form->render($formObj->m_TemplateFile);
    }
}
?>