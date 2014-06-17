<?php
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.service
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: logService.php 4042 2011-05-01 06:36:18Z rockys $
 */

require_once 'Zend/Log.php';
require_once 'Zend/Log/Writer/Stream.php';

/**
 * ioService class is the plug-in service of handling file import/export
 *
 * @package   openbiz.bin.service
 * @author    Rocky Swen
 * @copyright Copyright (c) 2003-2009, Rocky Swen
 * @access    public
 */
class logService extends MetaObject
{
    /**
     * Log Levels are the highest level of logging to record.
     * By default, will log all entries up to the selected level
     * If this is set to NULL, logging is disabled

     * EMERG   = 0;  // Emergency: system is unusable
     * ALERT   = 1;  // Alert: action must be taken immediately
     * CRIT    = 2;  // Critical: critical conditions
     * ERR     = 3;  // Error: error conditions
     * WARN    = 4;  // Warning: warning conditions
     * NOTICE  = 5;  // Notice: normal but significant condition
     * INFO    = 6;  // Informational: informational messages
     * DEBUG   = 7;  // Debug: debug messages
     *
     * @var int
     */
    private $_level;

    /**
     * Logging format options include CSV, XML and HTML
     *
     * @var string
     */
    private $_format;
    
    /**
     * Organize files by in different ways:
     * PROFILE - pased on a profile userID
     * LEVEL - Store log files groups by their level
     * DATE - write log entries into a different file for each day
     *
     * @var string
     */
    private $_org;
    /**
     * Holds the formatter for a log object.  This object dictates how a log file is logged.
     *
     * @var object
     */
    private $_formatter;
    /**
     * Holds the file extension set based on the preferred format
     *
     * @var string
     */
    private $_extension = '.csv';

    private $_daystolive;
    /**
     * Initialize logService with xml array metadata
     *
     * @param array $xmlArr
     * @return void
     */
    public function __construct(&$xmlArr)
    {
        $this->readMetadata($xmlArr);
        //Check that logging is enabled
        if (is_int($this->_level) == FALSE)
            return;
        $this->setFormatter();
        $this->setExtension();
    }

    /**
     * Read array meta data, and store to meta object
     *
     * @param array $xmlArr
     * @return void
     */
    protected function readMetadata(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $level = strtoupper($xmlArr["PLUGINSERVICE"]["LOG_CONFIG"]["ATTRIBUTES"]["LEVEL"]);
        $this->_level = (int) $level;
        $this->_format = strtoupper($xmlArr["PLUGINSERVICE"]["LOG_CONFIG"]["ATTRIBUTES"]["FORMAT"]);
        $this->_org = strtoupper($xmlArr["PLUGINSERVICE"]["LOG_CONFIG"]["ATTRIBUTES"]["ORG"]);
        $this->_daystolive = $xmlArr["PLUGINSERVICE"]["LOG_CONFIG"]["ATTRIBUTES"]["DAYSTOLIVE"]?strtoupper($xmlArr["PLUGINSERVICE"]["LOG_CONFIG"]["ATTRIBUTES"]["DAYSTOLIVE"]):'0';
    }

    /**
     * The primary log function that will take an incoming message and specified paramter and log in the
     * appropriate location and format
     *
     * @param int $level - Follow common BSD error levels
     * @param string $package - A way to group log messages
     * @param string $message - The actual message to log
     * @return boolean -TRUE on success otherwise FALSE
     */
    public function log($priority, $package, $message)
    {
        if (DEBUG == 0 && $priority == LOG_DEBUG)
        	return true;
    	//Adapt PHP LOG priority
        switch ($priority)
        {
            case LOG_EMERG : // 	system is unusable
            case LOG_ALERT :	// action must be taken immediately
            case LOG_CRIT :	// critical conditions
                $level = 2;
                break;
            case LOG_ERR :	// error conditions
                $level = 3;
                break;
            case LOG_WARNING : // warning conditions
                $level = 4;
                break;
            case LOG_NOTICE : //	normal, but significant, condition
            case LOG_INFO :	// informational message
            case LOG_DEBUG :	// debug-level message
                $level = 7;
                break;
        }
        //echo "log levels: ".LOG_EMERG.", ".LOG_ALERT.", ".LOG_CRIT.", ".LOG_ERR.", ".LOG_WARNING.", ".LOG_NOTICE.", ".LOG_INFO.", ".LOG_DEBUG."<br>";

        if ($this->_level == FALSE)
            return true;
        //Get the file path
        $this->_level = $level;
        $path = $this->_getPath($file_name);
        if(!is_file($path))
        {
        	@touch($path);
        	@chmod($path,0777);
        }
        if (!is_writable($path))
            return false;        
        $this->prepFile($path);
        //Create the log writer object
        $writer = new Zend_Log_Writer_Stream($path);
        $writer->setFormatter($this->_formatter);
        //Instantiate logging object
        $logger = new Zend_Log($writer);
        //Filter log entries to those allowed by configuration
        $filter = new Zend_Log_Filter_Priority($this->_level);
        $logger->addFilter($filter);

        $logger->setEventItem('back_trace', 'n/a');
        $logger->setEventItem('package', $package);
        $logger->setEventItem('date', date("m/d/Y"));
        $logger->setEventItem('time', date("H:i:s"));

        //Write Log Entry
        $logger->log($message, $level);
        $this->closeFile($path);
        return TRUE;
    }

    /**
     * The primary log function that will take an incoming message and specified paramter and log in the
     * appropriate location and format
     *
     * @param int $level - Follow common BSD error levels
     * @param string $package - A way to group log messages
     * @param string $message - The actual message to log
     * @param string $fileName - An optional specific file name where the message should be logged
     * @param string $string - An optional list of the Application stack at the time of error
     * @return boolean -TRUE on success otherwise FALSE
     */
    public function logError($level = 7, $package, $message, $fileName = NULL, $backTrace = '')
    {
        //Adapt PHP Errors
        switch ($level)
        {
            case 8:          // E_NOTICE
            case 1024:       // E_USER_NOTICE
                $level = 4;
                break;

            case 2048:       //E_STRICT
            case 8192:       //E_DEPRICIATED
            case 16384:      //E_DEPRICIATED
                $level = 5;
                break;

            case 2:          //E_WARNING
            case 512:        //E_USER_WARNING
                $level = 3;
                break;

            case 256:
                $level = 2;
                break;
            
            default:
                $level = 2;
        }

        if ($this->_level == FALSE)
            return true;
        //Get the file path
        $this->_level = $level;
        $path = $this->_getPath($fileName);
        if (!is_writable($path))
            return false;
        $this->prepFile($path);

        //Create the log writer object
        $writer = new Zend_Log_Writer_Stream($path);
        $writer->setFormatter($this->_formatter);

        //Instantiate logging object
        $logger = new Zend_Log($writer);

        //Filter log entries to those allowed by configuration
        $filter = new Zend_Log_Filter_Priority($this->_level);
        $logger->addFilter($filter);
        //Set additional information about the class and function that called the log


        // Add custom elements to default log package
        $logger->setEventItem('back_trace', $backTrace);
        $logger->setEventItem('package', $package);
        $logger->setEventItem('date', date("m/d/Y"));
        $logger->setEventItem('time', date("H:i:s"));
        //Write Log Entry
        $logger->log($message, $level);
        $this->closeFile($path);
        return TRUE;
    }

    /**
     * Set the formatter based on the type of format selected in app.inc
     *
     * @return void
     */
    public function setFormatter()
    {
        switch ($this->_format)
        {
            case 'HTML':
            //HTML Format
                $this->_formatter = new Zend_Log_Formatter_Simple('<tr><td>%date%</td> <td>%time%</td> <td>%priorityName%</td> <td>%package%</td> <td>%message%</td> <td>%back_trace%</td> </tr>' . PHP_EOL);
                break;
            case 'XML':
            //XML Format
                require_once 'Zend/Log/Formatter/Xml.php';
                $this->_formatter = new Zend_Log_Formatter_Xml();
                break;
            case 'CSV':
            default:
            //CSV Format
                $this->_formatter = new Zend_Log_Formatter_Simple("'%date%','%time%','%priorityName%','%package%','%message%','%back_trace%'" . PHP_EOL);
                break;
        }
    }
    /**
     * Set the extension based on the type of format selected in app.inc
     *
     * @return void
     */
    public function setExtension()
    {
        switch ($this->_format)
        {
            case 'HTML':
                $this->_extension = '.html';
                break;
            case 'XML':
                $this->_extension = '.xml';
                break;
            case 'CSV':
            default:
                $this->_extension = '.log';
                break;
        }
    }
    /**
     * Either open a new file with the correct string or remove a previous closing string
     *
     * @return void
     */
    public function prepFile($path)
    {
        //Check for existing file and HTML format
        if (file_exists($path) and $this->_format == 'HTML')
        {
            $file = file($path);
            array_pop($file);
            file_put_contents($path, $file);
        } elseif ($this->_format == 'HTML')
        {
            $html = '<html><head></head><body><table border="1">' . PHP_EOL;
            $file = fopen($path, 'a');
            fwrite($file, $html);
            fclose($file);
        } elseif (file_exists($path) and $this->_format == 'XML')
        {
            $file = file($path);
            array_pop($file);
            file_put_contents($path, $file);
        } elseif ($this->_format == 'XML')
        {
            $xml = '<?xml version="1.0" standalone="no"?><log>' . PHP_EOL;
            $file = fopen($path, 'a');
            fwrite($file, $xml);
            fclose($file);
        }
    }

    /**
     * Close a file with the correct string depending on the configured format
     *
     * @return void
     */
    public function closeFile($path)
    {
        //Close up the file if needed
        switch ($this->_format)
        {
            case 'HTML':
                $html = "</table></body></html>";
                $file = fopen($path, 'a');
                fwrite($file, $html);
                fclose($file);
                break;
            case 'XML':
                $xml = "</log>";
                $file = fopen($path, 'a');
                fwrite($file, $xml);
                fclose($file);
                break;
            default:
                break;
        }
    }

    /**
     * Get path based on config options
     *
     * @global BizSystem $g_BizSystem
     * @param string $fileName
     * @return string log_path - The path where a log entry should be written
     */
    private function _getPath($fileName = null)
    {
        $level = $this->_level;
        if ($fileName)
            return LOG_PATH . '/' . $fileName . $this->_extension;
        switch ($this->_org)
        {
            case 'DATE':
                return LOG_PATH . '/' . date("Y_m_d") . $this->_extension;
                break;
            case 'LEVEL':
                $level = $this->_level2filename($level);                
                return LOG_PATH . '/' . $level . $this->_extension;
                break;
            case 'LEVEL-DATE':
                $level = $this->_level2filename($level);
                //delete old log files                
                if($this->_daystolive>0){
	                if(is_array(glob(LOG_PATH . '/' . $level .'-*' .  $this->_extension))){
		                foreach (glob(LOG_PATH . '/' . $level .'-*' .  $this->_extension) as $filename) {
						    $mtime = filemtime($filename);
						    if((time() - $mtime) >= $this->_daystolive*86400 ){
						    	@unlink($filename);
						    }
						}
                	}
                }
                return LOG_PATH . '/' . $level .'-'. date("Y_m_d") .  $this->_extension;
                break;
            case 'PROFILE':
                $profile = BizSystem::getUserProfile('USERID');
                if (! $profile)
                    $profile = 'Guest';
                return LOG_PATH . '/' . $profile . $this->_extension;
                break;
            default:
                ;
                break;
        }
    }
    
    private function _level2filename($level)
    {
   				switch ($this->_level)
                {
                    case 0:
                        $level = 'EMERG';
                        break;
                    case 1:
                        $level = 'ALERT';
                        break;
                    case 2:
                        $level = 'CRIT';
                        break;
                    case 3:
                        $level = 'ERR';
                        break;
                    case 4:
                        $level = 'WARN';
                        break;
                    case 5:
                        $level = 'NOTICE';
                        break;
                    case 6:
                        $level = 'INFO';
                        break;
                    case 7:
                        $level = 'DEBUG';
                        break;
                    default:
                        ;
                        break;
                }    
                return $level;
    }
}