<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.service
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: eventlogService.php 3371 2012-05-31 06:17:21Z rockyswen@gmail.com $
 */



class eventlogService
{
   
   public $m_logDataObj;
   public $m_MessageFile;
   public $m_Messages;
   
   function __construct(&$xmlArr)
   {      
      $this->readMetadata($xmlArr);
   }

   protected function readMetaData($xmlArr)
   {      
      $this->m_logDataObj 	= $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["BIZDATAOBJ"];      
      $this->m_MessageFile = isset($xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["MESSAGEFILE"]) ? $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["MESSAGEFILE"] : null;
      $this->m_Messages = Resource::loadMessage($this->m_MessageFile , "eventlog.");
   }
   
   
	public function Log($eventName,$eventMessage,$eventComment=array())
	{
  		global $g_BizSystem;
      	$logDataObj = BizSystem::getObject($this->m_logDataObj);
      	if (!$logDataObj) return false; 
         
		$profile = $g_BizSystem->getUserProfile();  
        $recArr['user_id'] = $profile["Id"];
        $recArr['ipaddr'] = $_SERVER['REMOTE_ADDR'];
        $recArr['event'] = $eventName;
        $recArr['message'] = $eventMessage;
        $recArr['comment'] = serialize($eventComment);
        $recArr['timestamp'] = date("Y-m-d H:i:s");
         
        $ok = $logDataObj->insertRecord($recArr);
        if ($ok == false){
            BizSystem::log(LOG_ERR, "EVENTLOG", $logDataObj->getErrorMessage());
            return false;
        }		
	}

	
	public function GetLogMessage($msgId, $params="")
	{	
		$message = isset($this->m_Messages[$msgId]) ? $this->m_Messages[$msgId] : constant($msgId);
        $message = I18n::t($message, $msgId, 'eventlog');
        $params  =  unserialize($params);
        $result  = vsprintf($message,$params);
		return $result;		
	}
	
 	public function exportCSV()
    {
    	$separator=",";
    	$ext="csv";
        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: filename=EventLog_".date('Y-m-d') . "." . $ext);
        header("Content-Transfer-Encoding: binary");
        
        $recordList = $this->getLogData();
        foreach ($recordList as $row)
        {
            $line = "";
            foreach ($row as $cell)
                $line .= "\"" . strip_tags($cell) . "\"$separator";
            $line = rtrim($line, $separator);
            echo rtrim($line) . "\n";
        }
        return;
    }
    
    protected function getLogData()
    {
        $logDataObj = BizSystem::getObject($this->m_logDataObj);         
	    $recordList = array();
	    $logDataObj->fetchRecords("", $recordList);    	    		    
	    for($i=0;$i<count($recordList);$i++){    	
	    	$data[$i]['timestamp'] 	= $recordList[$i]['timestamp'];
	    	$data[$i]['ipaddr'] 		= $recordList[$i]['ipaddr'];
	    	$data[$i]['event'] 		= $this->GetLogMessage($recordList[$i]['event']);
	    	$data[$i]['message']		= $this->GetLogMessage($recordList[$i]['message'],
	    														$recordList[$i]['comment']);	
    		$data[$i]['event']		= $this->convertEncoding($data[$i]['event']);
    		$data[$i]['message']		= $this->convertEncoding($data[$i]['message']);
    		
	    }        
        return $data;         
    }	
    
    //convert encoding for Microsoft Excel, It doesnt supports UTF-8 encoding
    protected function convertEncoding($message){
    	$lang=strtolower(I18n::getInstance()->getCurrentLanguage());    	
    	switch($lang){
    		case 'zh_cn':
    			$message = iconv("UTF-8","GB2312//IGNORE",$message);    			
    			break;
    		case 'zh_tw':
    			$message = iconv("UTF-8","BIG5//IGNORE",$message);
    			break;
    	}
    	
    	return $message;
    }
}

?>