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
 * @version   $Id: app_init.php 3815 2012-08-02 16:07:17Z rockyswen@gmail.com $
 */
include_once ('device_util.php');

/* * **************************************************************************
  openbiz core path
 * ************************************************************************** */
//define('OPENBIZ_HOME', 'absolute_dir/Openbiz');
define('OPENBIZ_HOME', dirname(dirname(__FILE__)) . "/openbiz");

/* * **************************************************************************
  application related path
 * ************************************************************************** */
define('APP_HOME', dirname(dirname(__FILE__)));

/* website url. please change the localhost to real url */
if (isset($_SERVER["HTTP_HOST"])) {
    define('SITE_URL', 'http://local.openbiz.me/');
} else {
    define('SITE_URL', 'http://local.openbiz.me/');
}

define('DEFAULT_SYSTEM_NAME', 'Cubi Platform');

/* figure out APP_URL and APP_INDEX
  [SCRIPT_NAME] => /cubi-ng/*.php, APP_URL is /cubi-ng, APP_INDEX is APP_URL/index.php */
$indexScript = "/index.php"; // or "", or "/?"
preg_match("/(\S+)\/\w+\.php/",$_SERVER['SCRIPT_NAME'],$m);
define('APP_URL', $m[1]);
define('APP_INDEX', APP_URL.$indexScript);

/* define modules path */
define('MODULE_PATH', APP_HOME . DIRECTORY_SEPARATOR . "modules");

/* define modules extension path that can store custom code who overrides default module logic */
//define('MODULE_EX_PATH',APP_HOME.DIRECTORY_SEPARATOR."xmodules");

/* define messages files path */
define('MESSAGE_PATH', APP_HOME . DIRECTORY_SEPARATOR . "messages");

/* define themes const */
define('USE_THEME', 1);
define('THEME_URL', APP_URL . "/themes");
define('THEME_PATH', APP_HOME . DIRECTORY_SEPARATOR . "themes");    // absolution path the themes
define('DEFAULT_THEME_NAME', 'default');     // name of the theme. theme files are under themes/theme_name
define('SMARTY_CPL_PATH', APP_HOME . DIRECTORY_SEPARATOR . "files/tpl_cpl");    // smarty template compiling path

/* js lib base */
define('JS_URL', APP_URL . "/js");

define('OTHERS_URL', APP_URL . "/others");
/* Log file path */
define("LOG_PATH", APP_HOME . DIRECTORY_SEPARATOR . "log");


/* file path. */
define('APP_FILE_PATH', APP_HOME . DIRECTORY_SEPARATOR . "files");
define('APP_FILE_URL', APP_URL . "/files");

/* define session save handler */
define('USE_CUSTOM_SESSION_HANDLER', true);
if (is_file(APP_FILE_PATH . '/install.lock') && defined('USE_CUSTOM_SESSION_HANDLER') && USE_CUSTOM_SESSION_HANDLER ==true) {
    define("SESSION_HANDLER", MODULE_PATH . "/system/lib/SessionDBHandler"); // save session in DATABASE 
    //define("SESSION_HANDLER", MODULE_PATH."/system/lib/SessionMCHandler"); // save session in MEMCACHE
    define("SESSION_PATH", APP_HOME . DIRECTORY_SEPARATOR . "session"); // for default FILE type session handler
} else {
    define("SESSION_PATH", APP_HOME . DIRECTORY_SEPARATOR . "session"); // for default FILE type session handler^M
}
/* resources path. */
define('RESOURCE_PATH', APP_HOME . DIRECTORY_SEPARATOR . "resources");
define('RESOURCE_URL', APP_URL . "/resources");
define('RESOURCE_PHP', APP_URL . "/rs.php");
/* secured upload / attachment file path. files cannot be accessed by a direct url */
define('SECURE_UPLOAD_PATH', APP_HOME . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR . "sec_upload");

/* public upload file path. for example, uploaded image files. files can be accessed by a direct url */
define('PUBLIC_UPLOAD_PATH', APP_HOME . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR . "upload");
define('PUBLIC_UPLOAD_URL', APP_FILE_URL . '/upload');

/* file cache.DIRECTORY_SEPARATOR."rectory */
define('CACHE_PATH', APP_HOME . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR . "cache");

/* temopary files directory */
define('TEMPFILE_PATH', APP_HOME . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR . "tmp");

/* metadata cache files directory */
define('CACHE_METADATA_PATH', APP_HOME . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "metadata");

/* data cache files directory */
define('CACHE_DATA_PATH', APP_HOME . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "data");


// define default data service provider
//define('DEFAULT_DATASERVICE_PROVIDER', "http://localhost/cubing/cubi/rest.php");
define('DEFAULT_DATASERVICE_PROVIDER', APP_URL."/rest.php");

/* * **************************************************************************
  application system level constances
 * ************************************************************************** */
/* whether print debug infomation or not */
define("DEBUG", 0);

/* check whether user logged in */
//define("CHECKUSER", "Y");
/* session timeout seconds */
define("TIMEOUT", 86400);  // 86400 = 1 day
//I18n
//define('DEFAULT_LANGUAGE','en_US');
define('DEFAULT_CURRENCY', 'CNY');
define('DEFAULT_LANGUAGE', 'en_US');
define("LANGUAGE_PATH", APP_HOME . DIRECTORY_SEPARATOR . "languages");
/* define locale to be set in typemanager.php depending on selected language */
//$local["es"]="es_ES.utf8";
//$local["en"]="en_EN.utf8";
//{$row.fld_latitude},{$row.fld_longtitude}
define('DEFAULT_LATITUDE', '39.92');
define('DEFAULT_LONGTITUDE', '116.46');


//session strict 
//0=allow concurrent session
//1=limited to single session
define('SESSION_STRICT', '0');
// login page
define('USER_LOGIN_VIEW', "user.view.LoginView");

// session timeout page
define('USER_TIMEOUT_VIEW', "common.view.TimeoutView");

// access deny page
define('ACCESS_DENIED_VIEW', "common.view.AccessDenyView");

// security deny page
define('SECURITY_DENIED_VIEW', "common.view.SecurityDenyView");

// not found page
define('NOTFOUND_VIEW', "common.view.NotfoundView");

// internal error page
define('INTERNAL_ERROR_VIEW', "common.view.ErrorView");

// define service namings
define('EVENTLOG_SERVICE', "eventlogService");
define('USER_EMAIL_SERVICE', "userEmailService");
define('VISIBILITY_SERVICE', "visService");
define('PDF_SERVICE', "pdfService");
define('PREFERENCE_SERVICE', "preferenceService");
define('DATAPERM_SERVICE', "dataPermService");
define('UTIL_SERVICE', "utilService");
define('LOV_SERVICE', "lovService");


define('DENY', 0);
define('ALLOW', 1);
define('ALLOW_OWNER', 2);

define('APPBUILDER', '1'); // 0: hidden, 1: show
// load default theme
if (FORCE_DEFAULT_THEME == 1) {
    define('THEME_NAME', DEFAULT_THEME_NAME);
} else {
    if (@isset($_GET['theme'])) {
        //$_GET
        define('THEME_NAME', $_GET['theme']);
        //save cookies
        setcookie("THEME_NAME", $_GET['theme'], time() + 86400 * 365, "/");
    } elseif (@isset($_COOKIE['THEME_NAME'])) {
        define('THEME_NAME', $_COOKIE['THEME_NAME']);
    } else {
        //default
        define('THEME_NAME', DEFAULT_THEME_NAME);
    }
}
include_once(OPENBIZ_HOME . "/bin/sysheader_inc.php");

// service alias. used in expression engine
$g_ServiceAlias = array('validate' => VALIDATE_SERVICE, 'query' => QUERY_SERVICE, 'vis' => VISIBILITY_SERVICE, 'preference' => PREFERENCE_SERVICE, 'util' => UTIL_SERVICE);


//init default timezone setting 
define('DEFAULT_TIMEZONE', 'Asia/Chongqing');

//please keep below code , the DEFAULT timezone sett could be change in your admin's preference setting panel,
//if remove below may cause error, which break entire system, php will generate a warning level error and our handler will end up the script. 
//$DefaultTimezone = BizSystem::sessionContext()->getVar("TIMEZONE");
$DefaultTimezone = "";
// default language
if ($DefaultTimezone == "") {
    $DefaultTimezone = DEFAULT_TIMEZONE;
}
date_default_timezone_set($DefaultTimezone);



define('FusionChartVersion', "Pro");

define('GROUP_DATA_SHARE', '1');
define('DATA_ACL', '1');
define('DEFAULT_OWNER_PERM', '3');
define('DEFAULT_GROUP_PERM', '1');
define('DEFAULT_OTHER_PERM', '0');
