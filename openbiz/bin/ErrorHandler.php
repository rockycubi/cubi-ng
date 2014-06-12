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
 * @license   http://www.opensource.org/licenses/bsd-license.php     BSD
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: ErrorHandler.php 5186 2013-01-19 15:39:24Z hellojixian@gmail.com $
 */


/**
 * Openbiz Error Handler can handle php error and uncaught exception.
 * The default behavior is to show popup on client side. Developer can overide it to add more logic
 *
 * @package   openbiz.bin
 * @author    Rocky Swen <rocky@phpopenbiz.org>
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class OB_ErrorHandler
{
    public static $errorMode = 'html';
    
    /**
     * User error handler
     *
     * @param number $errNo error number
     * @param string $errMsg error message
     * @param string $fileName file name where the error occurred
     * @param number $lineNum line number where the error occurred
     * @param <type> $vars (not yet implemented)
     * @return void
     */
    public static function errorHandler ($errNo, $errMsg, $fileName, $lineNum, $vars)
    {
        // don't respond to the error if it
        // was suppressed with a '@'
        if (error_reporting() == 0)
            return;
        if ($errNo == E_NOTICE || $errNo == E_STRICT) { // || $errno == E_WARNING) 
			//echo "errorHandler ($errNo, $errMsg, $fileName, $lineNum, $vars)\n";
			return; // ignore notice error
		}
        $debug_array = debug_backtrace();
        $back_trace = self::_errorBacktrace($debug_array);
        $err = self::_getOutputErrorMsg($errNo, $errMsg, $fileName, $lineNum, $back_trace);
        //Send Error to Log Service;
        require_once dirname(__FILE__).'/BizSystem.php';
        BizSystem::logError ($errNo, "ErrorHandler", $errMsg, null, $back_trace);
        if ((defined('CLI') && CLI) || self::$errorMode == 'text')
        {
        	echo $err;
        }
        else
        {
        	BizSystem::clientProxy()->showErrorMessage($err, true);     
        }
        if ($errNo == E_USER_ERROR || $errNo == E_ERROR){
			BizSystem::clientProxy()->printOutput();
        	exit();
        }
    }

    /**
     * User exception handler
     *
     * @param  object $exc hold error data
     * @return void
     **/
    public static function exceptionHandler($exc)
    {
        $errno = $exc->getCode();
        $errmsg = $exc->getMessage();
        $filename = $exc->getFile();
        $linenum = $exc->getLine();
        $debug_array = $exc->getTrace();
        $back_trace = self::_errorBacktrace($debug_array);
        $err = self::_getOutputErrorMsg($errno, $errmsg, $filename, $linenum, $back_trace);

        BizSystem::logError ($errno, "ExceptionHandler", $errmsg, null, $back_trace);
        if ((defined('CLI') && CLI) || self::$errorMode == 'text'){
        	echo $err;
        }else{
        	BizSystem::clientProxy()->showErrorMessage($err, true);        	
        }
        if(!$exc->no_exit)
        {
        	BizSystem::clientProxy()->printOutput();
        	exit();
        }
    }

    /**
     * Get output error message
     *
     * @param number $errno error number
     * @param string $errmsg error message
     * @param string $filename file name where the error occurred
     * @param number $linenum line number where the error occurred     * @param <type> $filename
     * @param string $back_trace back trace message (print out call stack of the error)
     * @return string error message in html
     */
    private static function _getOutputErrorMsg ($errno, $errmsg, $filename, $linenum, $back_trace)
    {
        // timestamp for the error entry
        date_default_timezone_set('GMT'); // to avoid error PHP 5.1
        $dt = date("Y-m-d H:i:s (T)");
        // TODO: use CSS class for style
        $err = "<div style='font-size: 12px; color: blue; font-family:Arial; font-weight:bold;'>\n";
        $err .= "[$dt] An exception occurred while executing this script:<br>\n";
        $err .= "Error message: <font color=maroon> #$errno, $errmsg</font><br>\n";
        $err .= "Script name and line number of error: <font color=maroon>$filename:$linenum</font><br>\n";
        $err .= "<div style='font-weight:normal;'>".$back_trace."</div>\n";
        //$err .= "Variable state when error occurred: $vars<br>";
        $err .= "<hr>";
        $err .= "Please ask system administrator for help...</div>\n";
        if ((defined('CLI') && CLI) || self::$errorMode == 'text')
        	$err = strip_tags($err);
        return $err;
    }

    /**
     * Print out call stack of the error
     *
     * @param array $debug_array
     * @return string
     */
    private static function _errorBacktrace ($debug_array = NULL)
    {
        if ($debug_array == NULL)
            $debug_array = debug_backtrace();
        $counter = count($debug_array);
        $msg = "";
        for ($tmp_counter = 0; $tmp_counter != $counter; ++ $tmp_counter)
        {
            $msg .= "<br><b>function:</b> ";
            $msg .= $debug_array[$tmp_counter]["function"] . " ( ";
            //count how many args a there
            $args_counter = count($debug_array[$tmp_counter]["args"]);
            //print them
            for ($tmp_args_counter = 0; $tmp_args_counter != $args_counter; ++ $tmp_args_counter)
            {
                $a = $debug_array[$tmp_counter]["args"][$tmp_args_counter];
                switch (gettype($a))
                {
                    case 'integer':
                    case 'double':
                        $msg .= $a;
                        break;
                    case 'string':
                        //$a = htmlspecialchars(substr($a, 0, 64)).((strlen($a) > 64) ? '...' : '');
                        //$msg .= "\"$a\"";
                        $a = htmlspecialchars($a);
                        $msg .= "\"$a\"";
                        break;
                    case 'array':
                        $msg .= 'Array('.count($a).')';
                        break;
                    case 'object':
                        $msg .= 'Object('.get_class($a).')';
                        break;
                    case 'resource':
                        $msg .= 'Resource('.strstr($a, '#').')';
                        break;
                    case 'boolean':
                        $msg .= $a ? 'True' : 'False';
                        break;
                    case 'NULL':
                        $msg .= 'Null';
                        break;
                    default:
                        $msg .= 'Unknown';
                }
                if (($tmp_args_counter + 1) != $args_counter)
                    $msg .= (", ");
                else
                    $msg .= (" ");
            }
            $msg .= ") @ ";
            if(isset($debug_array[$tmp_counter]["file"]))
            {
                $msg .= ($debug_array[$tmp_counter]["file"] . " ");
            }
            if(isset($debug_array[$tmp_counter]["line"]))
            {
                $msg .= ($debug_array[$tmp_counter]["line"]);
            }
            if (($tmp_counter + 1) != $counter)
                $msg .= "\n";
        }
        return $msg;
    }

}
?>
