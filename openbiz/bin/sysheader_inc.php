<?php

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
 * @version   $Id: sysheader_inc.php 4179 2011-05-26 07:40:53Z rockys $
 */
include_once "sysclass_inc.php";

if (isset($_SERVER['SERVER_NAME']))
{
    define('CLI', 0);
    define('nl', "<br/>");
} else
{
    define('CLI', 1);
    define('nl', "\n");
}

/* * **************************************************************************
  openbiz core path
 * ************************************************************************** */
//define('OPENBIZ_HOME', 'absolute_dir/Openbiz');
if (!defined('OPENBIZ_HOME'))
    define('OPENBIZ_HOME', dirname(dirname(__FILE__)));
if (!defined('OPENBIZ_BIN'))
    define('OPENBIZ_BIN', OPENBIZ_HOME . "/bin/");
if (!defined('OPENBIZ_META'))
    define('OPENBIZ_META', OPENBIZ_HOME . "/metadata/");

/* * **************************************************************************
  third party library path
 * ************************************************************************** */
// Smarty package
if (!defined('SMARTY_DIR'))
    define('SMARTY_DIR', OPENBIZ_HOME . "/others/Smarty/libs/");

/* * **************************************************************************
  application services
 * ************************************************************************** */
if (!defined('AUTH_SERVICE'))
    define('AUTH_SERVICE', "service.authService");
if (!defined('ACCESS_SERVICE'))
    define('ACCESS_SERVICE', "service.accessService");
if (!defined('ACL_SERVICE'))
    define('ACL_SERVICE', "service.aclService");
if (!defined('PROFILE_SERVICE'))
    define('PROFILE_SERVICE', "service.profileService");
if (!defined('LOG_SERVICE'))
    define('LOG_SERVICE', "service.logService");
if (!defined('EXCEL_SERVICE'))
    define('EXCEL_SERVICE', "service.excelService");
if (!defined('PDF_SERVICE'))
    define('PDF_SERVICE', "service.pdfService");
if (!defined('IO_SERVICE'))
    define('IO_SERVICE', "service.ioService");
if (!defined('EMAIL_SERVICE'))
    define('EMAIL_SERVICE', "service.emailService");
if (!defined('DOTRIGGER_SERVICE'))
    define('DOTRIGGER_SERVICE', "service.doTriggerService");
if (!defined('GENID_SERVICE'))
    define('GENID_SERVICE', "service.genIdService");
if (!defined('VALIDATE_SERVICE'))
    define('VALIDATE_SERVICE', "service.validateService");
if (!defined('QUERY_SERVICE'))
    define('QUERY_SERVICE', "service.queryService");
if (!defined('SECURITY_SERVICE'))
    define('SECURITY_SERVICE', "service.securityService");
if (!defined('EVENTLOG_SERVICE'))
    define('EVENTLOG_SERVICE', "service.eventlogService");
if (!defined('CACHE_SERVICE'))
    define('CACHE_SERVICE', "service.cacheService");
if (!defined('CRYPT_SERVICE'))
    define('CRYPT_SERVICE', "service.cryptService");
if (!defined('LOCALEINFO_SERVICE'))
    define('LOCALEINFO_SERVICE', "service.localeInfoService");

/* whether print debug infomation or not */
if (!defined('DEBUG'))
    define("DEBUG", 1);
if (!defined('PROFILING'))
    define("PROFILING", 1);

/* check whether user logged in */
//if(!defined('CHECKUSER')) define("CHECKUSER", "N");
/* session timeout seconds */
if (!defined('TIMEOUT'))
    define("TIMEOUT", -1);  // -1 means never timeout.

    
//include system message file
include_once(OPENBIZ_HOME . "/messages/system.msg");

// defined Zend framework library home as ZEND_FRWK_HOME
define('ZEND_FRWK_HOME', OPENBIZ_HOME . "/others/");

/* Popup Suffix for Modal or Popup Windows */
define('Popup_Suffix', "_popupx_");

// add zend framework to include path
set_include_path(get_include_path() . PATH_SEPARATOR . ZEND_FRWK_HOME);

/* global variables */

include_once("BizClassLoader.php");

$coreClassMap = include( __DIR__ . DIRECTORY_SEPARATOR . 'autoload_classmap.php' );
BizClassLoader::registerClassMap($coreClassMap);

//$othersClassMap = include( realpath(__DIR__ . '/../others/autoload_classmap.php' ) );
$othersClassMap = include( realpath(__DIR__ . '/../others/autoload_classmap_selected.php' ) );
BizClassLoader::registerClassMap($othersClassMap);

//echo realpath( __DIR__ . '/../others/autoload_classmap.php' );
//echo "<pre>";
//print_r($othersClassMap);
//exit;


// error handling 
error_reporting(E_ALL ^ (E_NOTICE | E_STRICT));
ini_set('display_errors', 1);

// if use user defined error handling function, all errors are reported to the function
//$default_error_handler = set_error_handler("userErrorHandler");
//$default_exception_handler = set_exception_handler('userExceptionHandler');

// set DOCUMENT_ROOT
setDocumentRoot();

/**
 * Search for the php file required to load the class
 *
 * @package openbiz.bin
 * @param string $className
 * @return void
 * */
function __autoload_openbiz($className)
{
    BizClassLoader::autoload($className);
}

if (!function_exists("__autoload"))
{
    spl_autoload_register("__autoload_openbiz");
}

//include_once("BizSystem.php");
$g_BizSystem = BizSystem::instance();

/**
 * User error handler function
 *
 * @package openbiz.bin
 */
function userErrorHandler($errno, $errmsg, $filename, $linenum, $vars)
{
    //include_once(OPENBIZ_BIN.'ErrorHandler.php');
    OB_ErrorHandler::ErrorHandler($errno, $errmsg, $filename, $linenum, $vars);
}

/**
 * User exception handler function
 * @package openbiz.bin
 * @param <type> $exc
 */
function userExceptionHandler($exc)
{
    //include_once(OPENBIZ_BIN.'ErrorHandler.php');
    OB_ErrorHandler::ExceptionHandler($exc);
}

/*
 * Set DOCUMENT_ROOT in case the server doesn't have DOCUMENT_ROOT setting (e.g. IIS). 
 * Reference from http://fyneworks.blogspot.com/2007/08/php-documentroot-in-iis-windows-servers.html
 */

function setDocumentRoot()
{
    if (!isset($_SERVER['DOCUMENT_ROOT']))
    {
        if (isset($_SERVER['SCRIPT_FILENAME']))
        {
            $_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0 - strlen($_SERVER['PHP_SELF'])));
        };
    };
    if (!isset($_SERVER['DOCUMENT_ROOT']))
    {
        if (isset($_SERVER['PATH_TRANSLATED']))
        {
            $_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0 - strlen($_SERVER['PHP_SELF'])));
        };
    };
}

/**
 *  formatted output given variable by using print_r() function
 *
 *  @author Garbin
 *  @param  any
 *  @return void
 */
function dump($arr, $debug = false)
{
    if ($debug)
        $debug_fun = 'debug_print_backtrace();';
    echo '<pre>';
    array_walk(func_get_args(), create_function('&$item, $key', 'print_r($item);' . $debug_fun . ''));
    echo '</pre>';
    exit();
}

/**
 *  formatted output given variable by using var_dump() function
 *
 *  @author Garbin
 *  @param  any
 *  @return void
 */
function vdump($arr, $debug = false)
{
    if ($debug)
        $debug_fun = 'debug_print_backtrace();';
    echo '<pre>';
    array_walk(func_get_args(), create_function('&$item, $key', 'var_dump($item);' . $debug_fun . ''));
    echo '</pre>';
    exit();
}

?>