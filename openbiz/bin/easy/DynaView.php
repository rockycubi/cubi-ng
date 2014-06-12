<?PHP
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
 * @version   $Id: DynaView.php 3284 2011-02-22 06:56:33Z rockys $
 */

/**
 * DynaView class is the class that contains list of forms dinamicaly.
 * View is same as html page.
 *
 * @package openbiz.bin.easy
 * @author rocky swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class DynaView extends EasyView
{
    /**
     * Initialize DynaView with xml array
     *
     * @param array $xmlArr
     * @return void
     */
    public function __construct(&$xmlArr)
    {
        parent::__construct($xmlArr);

        $this->processURL();
    }

    /**
     * Process URL to get the form param and add the form in the FormRefs
     *
     * @return void
     */
    protected function processURL()
    {
        // if url has form=...
        $paramForm = isset($_GET['form']) ? $_GET['form'] : null;
        $paramCForm = isset($_GET['cform']) ? $_GET['cform'] : null;

        if (!$paramForm)
            return;

        // add the form in FormRefs
        if ($paramForm)
        {
        	if($this->isInFormRefLibs($paramForm))
	        	{
	            $xmlArr["ATTRIBUTES"]["NAME"] = $paramForm;
	            $xmlArr["ATTRIBUTES"]["SUBFORMS"] = $paramCForm ? $paramCForm : "";
	            $formRef = new FormReference($xmlArr);
	            $this->m_FormRefs->set($paramForm, $formRef);
	            if ($paramCForm)
	            {
	            	if($this->isInFormRefLibs($paramCForm))
	            	{
		                $xmlArr["ATTRIBUTES"]["NAME"] = $paramCForm;
		                $xmlArr["ATTRIBUTES"]["SUBFORMS"] = "";
		                $cformRef = new FormReference($xmlArr);
		                $this->m_FormRefs->set($paramCForm, $cformRef);
	            	}
	            }
        	}
        }

        // check url arg as fld:name=val
        $getKeys = array_keys($_GET);
        $paramFields = null;
        foreach ($getKeys as $key)
        {
            if (substr($key, 0, 4) == "fld:")
            {
                $fieldName = substr($key, 4);
                $fieldValue = $_GET[$key];
                $paramFields[$fieldName] = $fieldValue;
            }
        }

        if (!$paramFields)
            return;

        $paramForm = $this->prefixPackage($paramForm);
        $formObj = BizSystem::objectFactory()->getObject($paramForm);
        $formObj->setRequestParams($paramFields);
    }
}

?>