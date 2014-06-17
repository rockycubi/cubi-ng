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
 * @version   $Id: doTriggerService.php 3836 2011-04-21 09:44:31Z jixian2003 $
 */

/*
<PluginService Name="" Description="" Package="" Class="" BizObjectName="">
  <DOTrigger TriggerType="INSERT|UPDATE|DELETE"> *
   <TriggerCondition Expression="" ExtraSearchRule="" />
   <TriggerActions>
      <TriggerAction Action="CallService|ExecuteSQL|ExecuteShell|CreateInboxItem|SendEmail|..." Immediate="Y|N" DelayMinutes="" RepeatMinutes="">
         <ActionArgument Name="" Value="" /> *
      </TriggerAction>
   </TriggerActions>
  </DOTrigger>
</PluginService>
*/

/**
 * class doTriggerService is the plug-in service of handle DataObject trigger
 *
 * @package   openbiz.bin.service
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class doTriggerService extends MetaObject
{
    /**
     * Name of {@link BizDataObj}
     *
     * @var string
     */
    public $m_DataObjName;

    /**
     * List of DOTrigger object
     *
     * @var array array of {@link DOTrigger}
     */
    public $m_DOTriggerList = array();

    /**
     * Initialize chartService with xml array metadata
     *
     * @param array $xmlArr
     * @return void
     */
    function __construct(&$xmlArr)
    {
        $this->readMetadata($xmlArr);
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
        
        $this->m_DataObjName = $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["DATAOBJECTNAME"];
        $this->m_DataObjName = $this->prefixPackage($this->m_DataObjName);
        $this->readMetaCollection($xmlArr["PLUGINSERVICE"]["DOTRIGGER"], $tmpList);
        if (!$tmpList)
            return;
        foreach ($tmpList as $triggerXml)
        {
            $this->m_DOTriggerList[] = new DOTrigger($triggerXml);
        }
    }

    /**
     * Execute trigger
     *
     * @param BizDataObj $dataObj
     * @param <type> $triggerType
     * @return void
     */
    public function execute($dataObj, $triggerType)
    {
        /* @var $doTrigger DOTrigger */
        foreach ($this->m_DOTriggerList as $doTrigger)
        {        	
            if ($doTrigger->m_TriggerType == $triggerType){            	;
                $this->executeAllActions($doTrigger, $dataObj);
            }
        }
    }

    /**
     * Execute all action
     *
     * @param DOTrigger $doTrigger
     * @param BizDataObj $dataObj
     * @return void
     */
    protected function executeAllActions($doTrigger, $dataObj)
    {
        if (! $this->matchCondition($doTrigger, $dataObj))
            return;
        /* @var $triggerAction TriggerAction */            
        foreach ($doTrigger->m_TriggerActions as $triggerAction)
        {
            $this->executeAction($triggerAction, $dataObj);
        }
    }

    /**
     * Match condition
     *
     * @param DOTrigger $doTrigger
     * @param BizDataObj $dataObj
     * @return boolean
     */
    protected function matchCondition($doTrigger, $dataObj)
    {
        // evaluate expression
        $condExpr = $doTrigger->m_TriggerCondition["Expression"];
        if ($condExpr)
        {
            $exprVal = Expression::evaluateExpression($condExpr, $dataObj);
            if ($exprVal !== true)
                return false;
        }
        // do query with extra search rule, check if there's any record returned
        $extraSearchRule = $doTrigger->m_TriggerCondition["ExtraSearchRule"];
        if ($extraSearchRule)
        {
            $realSearchRule = Expression::evaluateExpression($extraSearchRule, $dataObj);
            $recordList = array();
            // get one record of the first page with additional searchrule
            $dataObj->fetchRecords($realSearchRule, $recordList, 1, 1, false);
            if (count($recordList) == 0)
                return false;
        }
        return true;
    }

    /**
     * Execute action
     *
     * @param TriggerAction $triggerAction
     * @param BizDataObj $dataObj
     * @return void
     */
    protected function executeAction($triggerAction, $dataObj)
    {
        // action method        
        $methodName = $triggerAction->m_Action;        
        // action method arguments
        if (method_exists($this, $methodName))
        {        	
            // evaluate arguments as expression support
            foreach ($triggerAction->m_ArgList as $argName => $argValue)
                $argList[$argName] = Expression::evaluateExpression($argValue, $dataObj);
            // check the immediate flag
            if ($triggerAction->m_Immediate == "Y") // call the method if Immediate is "Y"
                $this->$methodName($argList);
            else
            { // put it to a passive queue
                /* $passiveQueueSvc->Push($methodName,
                                      $argList, 
                                      $triggerAction->m_DelayMinutes, 
                                      $triggerAction->m_RepeatMinutes); */
            }
        }
    }

    /**
     * Compose action message
     *
     * @param TriggerAction $triggerAction
     * @param string $methodName
     * @param array $argList
     * @return void
     */
    private function _composeActionMessage($triggerAction, $methodName, $argList)
    {
        $actionMsg["Method"] = $methodName;
        $actionMsg["ArgList"] = $argList;
        $actionMsg["DelayMinutes"] = $triggerAction->m_DelayMinutes;
        $actionMsg["RepeatMinutes"] = $triggerAction->m_RepeatMinutes;
        $actionMsg["StartTime"] = strftime("%Y-%m-%d %H:%M:%S");
    }

    protected function callService($argList)
    {
    	$svcobj = $argList['Service'];
    	$method = $argList['Method'];
    	$svcobj = BizSystem::getObject($svcobj);
    	if(!method_exists($svcobj,$method))
    	{
    		return;
    	}  	 
    	unset($argList['Service']);
    	unset($argList['Method']);
    	return call_user_func_array(array($svcobj,$method),$argList);
    	
    }
    /**
     * Execute shell
     *
     * @param array $argList
     * @return void
     */
    protected function executeShell($argList)
    {
        $Script = $argList["Script"];
        $Inputs = $argList["Inputs"];
        $command = "$Script $Inputs";
        //$result = exec($command, $output);
        exec($command);
    }


    /**
     * Execute SQL
     *
     * @param array $argList
     * @return void
     */
    protected function executeSQL($argList)
    {
        $dbName = $argList["DBName"];
        if (! $dbName)
            $dbName = "Default";
        $sql = $argList["SQL"];
        $db = BizSystem::dbConnection($dbName);
        try
        {
            $resultSet = $db->query($sql);
        }
        catch (Exception $e)
        {
            $errorMessage = "Error in run SQL: " . $sql . ". " . $e->getMessage();
        }
    }

    /**
     * Send email
     *
     * @param array $argList
     * @return boolean|string if sending error return error messege, if success return true
     */
    protected function sendEmail($argList)
    {
        // emailService
        $emailServiceName = $argList["EmailService"];
        $emailService = BizSystem::getObject($emailServiceName);
        if ($emailService == null)
            return;

        $emailService->useAccount($argList["Account"]);
        $TOs = doTriggerService::_makeArray($argList["TOs"]);
        $CCs = doTriggerService::_makeArray($argList["CCs"]);
        $BCCs = doTriggerService::_makeArray($argList["BCCs"]);
        $Attachments = doTriggerService::_makeArray($argList["Attachments"]);
        $subject = $argList["Subject"];
        $body = $argList["Body"];
        $ok = $emailService->sendEmail($TOs, $CCs, $BCCs, $subject, $body, $Attachments);
        if ($ok == false)
        {
            return $emailService->getErrorMsg();
        }
        return $ok;
    }

    /**
     * Audit trail
     *
     * @param array $argList
     * @return void
     */
    protected function auditTrail($argList)
    {
        $auditServiceName = $argList["AuditService"];
        $auditService = BizSystem::getObject($auditServiceName);
        if ($auditService == null)
            return;
        $dataObjName = $argList["DataObjectName"];
        $ok = $auditService->audit($dataObjName);
        if ($ok == false)
        {
            // log $auditSvc->getErrorMsg();
        }
    }

    /**
     * Make array from string with semi colon delimited
     *
     * @param string $string string with ";" (semi colon) delimited
     * @return array
     */
    static private function _makeArray($string)
    {
        if (! $string)
            return null;
        $arr = explode(";", $string);
        $size = count($arr);
        for ($i = 0; $i < $size; $i ++)
            $arr[$i] = trim($arr[$i]);
        return $arr;
    }

    /**
     * Create inbox item
     *
     * @param array $argList
     * @return void
     */
    protected function createInboxItem($argList)
    {    // call inbox service
    }
}


/**
 * DOTrigger class
 *
 * @package   openbiz.bin.service
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class DOTrigger
{
    public $m_TriggerType;

    /**
     * Trigger condition
     *
     * @var array
     */
    public $m_TriggerCondition = array();

    /**
     * Iterator of TriggerAction
     * 
     * @var MetaIterator
     */
    public $m_TriggerActions;

    /**
     * Initialize DOTrigger with xml array metadata
     *
     * @param array $xmlArr
     * @return void
     */
    public function __construct ($xmlArr)
    {
        $this->m_TriggerType = $xmlArr["ATTRIBUTES"]["TRIGGERTYPE"];
        // read in trigger condition
        $this->m_TriggerCondition["Expression"] = $xmlArr["TRIGGERCONDITION"]["ATTRIBUTES"]["EXPRESSION"];
        $this->m_TriggerCondition["ExtraSearchRule"] = $xmlArr["TRIGGERCONDITION"]["ATTRIBUTES"]["EXTRASEARCHRULE"];
        if($xmlArr["TRIGGERACTIONS"]["TRIGGERACTION"][0]){
        	foreach($xmlArr["TRIGGERACTIONS"]["TRIGGERACTION"] as $key=>$value){	
        		$this->m_TriggerActions[] = new TriggerAction($xmlArr["TRIGGERACTIONS"]["TRIGGERACTION"][$key]);
        	}
        }else{
        	$this->m_TriggerActions = new MetaIterator($xmlArr["TRIGGERACTIONS"]["TRIGGERACTION"], "TriggerAction");
        }
        
    }
}

/**
 * TriggerAction class
 *
 * @package   openbiz.bin.service
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class TriggerAction extends MetaObject
{
    public $m_Name;
    public $m_Action;
    public $m_Immediate;
    public $m_DelayMinutes;
    public $m_RepeatMinutes;
    public $m_ArgList = array();

    /**
     * Initialize TriggerAction with xml array metadata
     *
     * @param array $xmlArr
     * @return void
     */
    public function __construct ($xmlArr)
    {
        $this->m_Name = $xmlArr["ATTRIBUTES"]["NAME"];
        $this->m_Action = $xmlArr["ATTRIBUTES"]["ACTION"];
        $this->m_Immediate = $xmlArr["ATTRIBUTES"]["IMMEDIATE"];
        $this->m_DelayMinutes = $xmlArr["ATTRIBUTES"]["DELAYMINUTES"];
        $this->m_RepeatMinutes = $xmlArr["ATTRIBUTES"]["REPEATMINUTES"];
        $this->readMetaCollection($xmlArr["ACTIONARGUMENT"], $tmpList);
        if (! $tmpList)
            return;
        foreach ($tmpList as $arg)
        {
            $this->m_ArgList[$arg["ATTRIBUTES"]["NAME"]] = $arg["ATTRIBUTES"]["VALUE"];
        }
    }
}