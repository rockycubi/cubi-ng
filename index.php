<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   \
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: index.php 5513 2014-06-04 04:34:06Z rockyswen@gmail.com $
 */


/* cubi rest web service entry point
  request example:
  url
    http://host/cubi/rest.php/system/users?start=10&limit=10
*/

include_once 'bin/app_init.php';
include_once OPENBIZ_HOME."/bin/ErrorHandler.php";

require 'bin/Slim/Slim.php';
\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

// start session context object
BizSystem::sessionContext();

// GET form
$app->get('/f/:module/:form(/:id)', function ($module,$form,$id=null) {
	$app = \Slim\Slim::getInstance();
	// get real form name module.form.formnameView
	$realFormName = getFormName($form, $module);
	// render view
	$formObj = BizSystem::getObject($realFormName);
	if (!$formObj) {
		// render NOTFOUND_FORM
		return;
	}
	if ($id) $formObj->setRequestParams(array('Id'=>$id));
	print $formObj->render();
});

// GET view
$app->get('/:module/:view(/:id)(/?:querystring+)', function ($module,$view,$id=null,$querystring=null) {
	$app = \Slim\Slim::getInstance();
	// get real view name module.view.viewnameView
	$realViewName = getViewName($view, $module);
	// render view
	$viewObj = BizSystem::getObject($realViewName);
	if (!$viewObj) {
		// render NOTFOUND_VIEW
		return;
	}
	if ($id) $viewObj->setRequestId($id);
	$viewObj->render();
});

$app->run();

function getViewName($url_path, $module)
{
	$view_name = "";
    if (preg_match_all("/([a-z]*)_?/si", $url_path, $match)) {
        $view_name = $module.".view.";
        $match = $match[1];
        foreach ($match as $part) {
            if ($part) {
                $part = ucwords($part); //ucwords(strtolower($part));
                $view_name .= $part;
            }
        }
        $view_name.="View";
    }
    return $view_name;
}

function getFormName($url_path, $module)
{
	$form_name = "";
    if (preg_match_all("/([a-z]*)_?/si", $url_path, $match)) {
        $form_name = $module.".form.";
        $match = $match[1];
        foreach ($match as $part) {
            if ($part) {
                $part = ucwords($part); //ucwords(strtolower($part));
                $form_name .= $part;
            }
        }
        $form_name.="Form";
    }
    return $form_name;
}
?>