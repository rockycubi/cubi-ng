<?PHP
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
 * @version   $Id: SessionContext.php 4105 2011-05-06 08:48:08Z jixian2003 $
 */

/**
 * Index of $_SESSION array that saves all information regarding statefull objects
 */
define('OB_STATEFUL_DATA_SESSION_INDEX', 'ob_stateful_data');
define('OB_TRANSIENT_DATA_SESSION_INDEX', 'ob_transient_data');


/**
 * SessionContext class is Session management class that has additional methods
 * to save/get session variables of metadata based stateful objects
 * through their GetSessionVars|SetSessionVars interfaces
 *
 * @package   openbiz.bin
 * @author    Rocky Swen <rocky@phpopenbiz.org>
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class SessionContext
{
    protected $_lastAccessTime;
    protected $_timeOut = false;
    protected $_sessObjArr = null;
    protected $_statefulSessObjArr = null;
    protected $_viewHistory = null;
    protected $_prevViewObjNames = array();

    /**
     * Constructor of SessionContext, init session and set session file path
     *
     * @return void
     **/
    function __construct()
    {        
        // get session save handler MC or DB
        if (defined("SESSION_HANDLER") && SESSION_HANDLER != "" && defined('USE_CUSTOM_SESSION_HANDLER') && USE_CUSTOM_SESSION_HANDLER ==true) {
            include_once SESSION_HANDLER.".php";
        }
        else {
        	if(!file_exists(SESSION_PATH)){        		
        		@mkdir(SESSION_PATH,0777,true);
        	}
            // default is file session
            if (defined('SESSION_PATH') && is_writable(SESSION_PATH))
                session_save_path(SESSION_PATH);
            // we cannot write in the session save path; aborting
            if(!is_writable(session_save_path())) 
                trigger_error("Unable to write in the session save path [".session_save_path()."]", E_USER_ERROR);
        }
        
        ini_set('session.gc_probability', 1);   // force gc 
        ini_set('session.gc_divisor', 100);
        if (defined('TIMEOUT') && TIMEOUT > 0)
            ini_set("session.gc_maxlifetime", TIMEOUT);
        else
            ini_set("session.gc_maxlifetime", 21600); // 6 hours
        
        		
            
        if (!defined('CLI') || CLI == 0) {
            if(isset($_REQUEST['cubi_sess_id'])){
                session_id($_REQUEST['cubi_sess_id']);
            }
            session_start();
        }
        // record access time
        $curTime = time();
        if (isset($_SESSION["LastAccessTime"]))
            $this->_lastAccessTime = $_SESSION["LastAccessTime"];
        else
            $this->_lastAccessTime = $curTime;
        $_SESSION["LastAccessTime"] =$curTime;

        // see if timeout
        $this->_timeOut = false;
        if ((TIMEOUT > 0) && (($curTime - $this->_lastAccessTime) > TIMEOUT))
            $this->_timeOut = true;
    }

    /**
     * Set single session variable
     *
     * @param string $varName
     * @param mixed $value
     * @return void
     **/
    public function setVar($varName, $value)
    {
        $_SESSION[$varName] = $value;
    }
    
    public function mergeVar($varName, $value)
    {
        $var = $_SESSION[$varName];
        if(is_array($var)){        	
        	foreach($value as $key=>$value){
        		$var[$key]=$value;
        	}
        	$_SESSION[$varName] = $var;
        }else{
        	$_SESSION[$varName] = $value;
        }	
    }
    /**
     * Get single session variable
     *
     * @param string $varName name of session variable
     * @return string
     **/
    public function getVar($varName)
    {
        if (!isset($_SESSION[$varName]))
            return "";
        return $_SESSION[$varName];
    }

    /**
     * Clear/Unset single session variable
     * NOTE: NYU - not yet used
     * 
     * @param string $varName
     * @return void
     **/
    public function clearVar($varName)
    {
        unset($_SESSION[$varName]);
    }

    /**
     * Is variable exist in the session
     * 
     * @param string $varName variable name that checked
     * @return boolean TRUE if the var exists in the session, otherwise FALSE
     **/
    public function varExists($varName)
    {
        $exists =  (array_key_exists($varName, $_SESSION)) ? TRUE : FALSE;
        return $exists;
    }
    
    public function getNamespace()
    {
        $view = BizSystem::instance()->getCurrentViewName();
        if ($view)
            $namespace = $view;
        else
            $namespace = 'DEFAULT_NS';
        return $namespace;
    }

    /**
     * Set single session variable of a stateful object
     *
     * @param string $objName - object name
     * @param string $varName - vaiable name
     * @param mixed $value - reference of the value (in/out)
     * @param boolean $stateful - is stateful?
     * @return void
     **/
    public function setObjVar($objName, $varName, &$value, $stateful=false )
    {
    	if(preg_match('/\./si',$objName)){
        	$objName = $this->getNamespace().'#'.$objName;
    	}
        if (!$stateful)
            $this->_sessObjArr[$objName][$varName] = $value;
        else
            $this->_statefulSessObjArr[$objName][$varName] = $value;
    }

    /**
     * Clean Object
     *
     * @param string $objName object name
     * @param boolean $stateful
     * @return void
     */
    public function cleanObj($objName, $stateful=false)
    {
    	if(preg_match('/\./si',$objName)){
        	$objName = $this->getNamespace().'#'.$objName;
    	}
        if (!$stateful)
            unset($this->_sessObjArr[$objName]);
        else
            unset($this->_statefulSessObjArr[$objName]);
    }

    /**
     * Get single session variable of a stateful object
     *
     * @param string $objName - object name
     * @param string $varName - vaiable name
     * @param mixed $value - reference of the value (in/out)
     * @param boolean $stateful - is stateful?
     * @return void
     */
    public function getObjVar($objName, $varName, &$value, $stateful=false)
    {
    	if(preg_match('/\./si',$objName)){
        	$objName = $this->getNamespace().'#'.$objName;
    	}
        if (!$stateful)
        {
            if (!$this->_sessObjArr)
                return;
            if (isset($this->_sessObjArr[$objName][$varName]))
                $value = $this->_sessObjArr[$objName][$varName];
        }
        else
        {
            if (!$this->_statefulSessObjArr)
                return;
            if (isset($this->_statefulSessObjArr[$objName][$varName]))
                $value = $this->_statefulSessObjArr[$objName][$varName];
        }
    }

    /**
     * Save session variables of all stateful objects into sessionid_obj file
     *
     * @return void
     **/
    public function saveSessionObjects()
    {
        // loop all objects (bizview, bizform, bizdataobj) collect their session vars
        $allobjs = BizSystem::objectFactory()->getAllObjects();
        foreach ($allobjs as $obj)
        {
            if (method_exists($obj, "SetSessionVars"))
            {
                //after calling $obj->setSessionVars SessObjArr and StatefulSessObjArr are filled
                $obj->setSessionVars($this);
            }
            // if previous view's object is used in current view, don't discard its session data
            if (isset($obj->m_Name) && key_exists($obj->m_Name, $this->_prevViewObjNames)) {
                unset($this->_prevViewObjNames[$obj->m_Name]);
                BizSystem::log(LOG_ERR, "SESSION", "unset ".$obj->m_Name);
            }
        }

        // discard useless previous view's session objects
        //foreach($this->_prevViewObjNames as $objName=>$tmp)
        //    unset($this->_sessObjArr[$objName]);

        $this->_sessObjArr["ViewHist"] = $this->_viewHistory;        
        
        $this->setVar(OB_TRANSIENT_DATA_SESSION_INDEX, $this->_sessObjArr);
        $this->setVar(OB_STATEFUL_DATA_SESSION_INDEX, $this->_statefulSessObjArr);
    }

    /**
     * Retrieve/Get session variables of all stateful objects from sessionid_obj file
     *
     * @return void
     **/
    public function retrieveSessionObjects()
    {
        $this->_sessObjArr = $this->getVar(OB_TRANSIENT_DATA_SESSION_INDEX);
        $this->_statefulSessObjArr = $this->getVar(OB_STATEFUL_DATA_SESSION_INDEX);

        if (!is_array($this->_sessObjArr))
            $this->_sessObjArr = array();
        if (!is_array($this->_statefulSessObjArr))
            $this->_statefulSessObjArr = array();

        $this->_viewHistory = array_key_exists('ViewHist', $this->_sessObjArr)
            ? $this->_sessObjArr["ViewHist"]
            : NULL;
        return TRUE;
    }

    /**
     * Clear session variables of all stateful objects
     *
     * @param boolean $keepObjects
     * @return void
     */
    public function clearSessionObjects($keepObjects = false)
    {
        if ($keepObjects == false)
        {
            unset($this->_sessObjArr);
            $this->_sessObjArr = array();
        }
        else // add previous view's session object names in to a map
        {
            if (isset($this->_sessObjArr))
            {
                foreach($this->_sessObjArr as $objName=>$sessobj)
                {
                    //echo "save sess $objName <br/>";
                    $this->_prevViewObjNames[$objName] = 1;
                }
            }
        }
    }

    /**
     * Save a JSON array in session
     *
     * @param string $jsonValue
     * @param string $jsonName
     * @return void
     **/
    public function saveJSONArray($jsonValue, $jsonName = NULL)
    {
        $jsonArray = json_decode($jsonValue);

        if((bool)$jsonName)
        { //If I want save all array in session I send the name of the array in session
            $this->setVar($jsonName, $jsonArray);
        }
        else
        {//I save each value in session
            foreach($jsonArray as $varName=>$value)
            {
                $this->setVar($varName, $value);
            }
        }
    }

    /**
     * Get view history data of given BizForm/EasyForm from saved in session file
     *
     * @param string $formName - name of BizForm/EasyForm
     * @return array - view history data represented by an associated array
     **/
    public function getViewHistory($formName)
    {
        $view = BizSystem::instance()->getCurrentViewName();
        $view_form = $formName; //$view."_".$formname;
        return $this->_viewHistory[$view_form];
    }

    /**
     * Set view history data of given bizform into session file
     *
     * @param string $formName - name of bizform
     * @param array $historyInfo - view history data represented by an associated array
     * @return void
     **/
    public function setViewHistory($formName, $historyInfo)
    {
        $view = BizSystem::instance()->getCurrentViewName();
        $view_form = $formName; //$view."_".$formname;
        if (!$historyInfo)
            unset($this->_viewHistory[$view_form]);
        else
            $this->_viewHistory[$view_form] = $historyInfo;
    }

    /**
     * Destroy/free all session data of the current session
     *
     * @return void
     **/
    public function destroy()
    {
        unset($this->_viewHistory);
        unset($this->_sessObjArr);
        unset($this->_statefulSessObjArr);

        session_destroy();
    }

    /**
     * Check if user logged in or not
     * NOTE: NYU - not yet used
     *
     * @return boolean
     */
    public function isUserValid()
    {
        if (CHECKUSER == "N")
            return true;
        if ($this->getVar("UserId") != "")
            return true;
        else
            return false;
    }

    /**
     * Check if current session is timeout
     *
     * @return boolean
     **/
    public function isTimeout()
    {
        return $this->_timeOut;
    }
}

?>
