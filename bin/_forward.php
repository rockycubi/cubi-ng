<?php

/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.bin
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: _forward.php 5173 2013-01-19 06:51:01Z hellojixian@gmail.com $
 */


// map url parameters to openbiz view, form, ...
//http://localhost/?/user/login			 => http://localhost/bin/controller.php?view=user.view.LoginView
//http://localhost/?/user/reset_password => http://localhost/bin/controller.php?view=user.view.RestPasswordView
//http://localhost/?/article/1 			 => http://localhost/bin/controller.php?view=page.view.ArticleView&fld:Id=1
//($DEFAULT_MODULE="page")
//http://localhost/?/article/1/f_catid_20=> http://localhost/bin/controller.php?view=page.view.ArticleView&fld:Id=1&fld:catid=20
//($DEFAULT_MODULE="page")
//http://localhost/?/article/catid_20 	 => http://localhost/bin/controller.php?view=page.view.ArticleView&catid=20
//($DEFAULT_MODULE="page")
define("USE_CUSTOM_SESSION_HANDLER",true);        	

include 'app_init.php';

$DEFAULT_VIEW = "LoginView";
$DEFAULT_MODULE = CLIENT_DEVICE=='mobile' ? "user_mob" : "user";
$DEFAULT_URL = "index.php/$DEFAULT_MODULE/login";

$script = $_SERVER['SCRIPT_NAME'];
$script = quotemeta($script);

$pattern = "|^$script?\?\/?(.*?)(\.html)?$|si";

if ($_SERVER["REDIRECT_QUERY_STRING"]) {
    $url = $_SERVER["REDIRECT_QUERY_STRING"];
} elseif (preg_match($pattern, $_SERVER['REQUEST_URI'], $match)) {
    //supports for http://localhost/?/user/login format
    //supports for http://localhost/index.php?/user/login format
    $url = $match[1];
} elseif (strlen($_SERVER['REQUEST_URI']) > strlen($_SERVER['SCRIPT_NAME'])) {
    //supports for http://localhost/index.php/user/login format
    $url = str_replace($_SERVER['SCRIPT_NAME'], "", $_SERVER['REQUEST_URI']);
    preg_match("/\/?(.*?)(\.html)?$/si", $url, $match);
    $url = $match[1];
} else {
    // REQUEST_URI = /cubi/
    // SCRIPT_NAME = /cubi/index.php
    $url = "";
}

//remove repeat slash // 
$url = preg_replace("/([\/\/]+)/", "/", $url);
preg_match("/\/?(.*?)(\.html)?$/si", $url, $match);
$url = $match[1];

$urlArr = array();
if ($url) {
    $urlArr = preg_split("/\//si", $url);
	
    if (preg_match("/^[a-z_]*$/si", $urlArr[1])) {
        // http://localhost/?/ModuleName/ViewName/
        $module_name = $urlArr[0];
        $view_name = getViewName($urlArr);
    } elseif (preg_match("/^[a-z_]*$/si", $urlArr[0])) {
        // http://localhost/?/ViewName/
        $module_name = $DEFAULT_MODULE;
        $view_name = getViewName($urlArr);
    }
    if (empty($urlArr[count($urlArr) - 1])) {
        //if its empty
        unset($urlArr[count($urlArr) - 1]);
    }
    /*if (preg_match("/\./si", $urlArr[count($urlArr) - 1])) {
        // if its trying to solve a file, like something.jpg, should be return a 404 header
   //     header("HTTP/1.1 404 Not Found");
   //     exit;
    }*/
} else {
    // http://localhost/
    $module_name = $DEFAULT_MODULE;
    $view_name = $DEFAULT_VIEW;
    $profile = BizSystem::getUserProfile();
    if ($profile['roleStartpage'][0]) {
        $DEFAULT_URL = APP_INDEX . $profile['roleStartpage'][0];
    }
    header("Location: $DEFAULT_URL");
}

$TARGET_VIEW = $module_name . ".view." . $view_name;
$_GET['view'] = $_REQUEST['view'] = $TARGET_VIEW;

$PARAM_MAPPING = getParameters($urlArr);
if (isset($PARAM_MAPPING)) {
    foreach ($PARAM_MAPPING as $param => $value) {
        //if (isset($_GET[$param])) 
        $_GET[$param] = $_REQUEST[$param] = $value;
    }
}

if(XHPROF && function_exists("xhprof_enable"))
{
	xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
}

$foo = __FILE__;
include dirname(__FILE__) . '/controller.php';

if(XHPROF && function_exists("xhprof_disable"))
{
	$xhprof_data = xhprof_disable();
	include_once XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
	include_once XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";
	$xhprof_runs = new XHProfRuns_Default();
	$run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_testing");
	echo "<div style=\"text-align:center\">xhprof id: <a target=\"_target\" href=\"".XHPROF_URL."$run_id\">$run_id</a></div>";
}

function getViewName($urlArr)
{
    $url_path = $urlArr[1];
    if (!$url_path) {
        return gotoDefaultView($urlArr[0]);
    }
    if (preg_match_all("/([a-z]*)_?/si", $url_path, $match)) {
        $view_name = "";
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

function gotoDefaultView($module)
{
    $module = strtolower($module);
    $modfile = MODULE_PATH . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'mod.xml';
    $xml = simplexml_load_file($modfile);
    $defaultURL = APP_INDEX . $xml->Menu->MenuItem['URL'];
    header("Location: $defaultURL");
}

function getParameters($urlArr)
{
    $PARAM_MAPPING = array();
    //foreach($urlArr as $path)
    for ($i = 2; $i < count($urlArr); $i++) { // ignore the first 2 parts 
        //only numberic like 20 parse it as fld:Id=20
        if (preg_match("/^([0-9]*)$/si", $urlArr[$i], $match)) {
            $PARAM_MAPPING["fld:Id"] = $match[1];
            continue;
        }
        //Cid_20 parse it as fld:Cid=20
        // http://localhost/cubi/some/thing/Cid_20
        // echo $_GET['Cid'];  // 20 
        // http://local.openbiz.me/index.php/collab/task_manage/fld_type_1/
        // array(1) { ["fld:fld_type"]=> string(1) "1" }
        elseif (preg_match("/^([a-z_]*?)_([^\/]*)$/si", $urlArr[$i], $match)) {
            $PARAM_MAPPING["fld:" . $match[1]] = $match[2];
            $_GET[$match[1]] = $match[2];
            continue;
        }
        // parse the string to query string
        parse_str($urlArr[$i], $arr);
        foreach ($arr as $k => $v) {
            $_GET[$k] = $v;
            $PARAM_MAPPING[$k] = $v;
        }
    }
    return $PARAM_MAPPING;
}

