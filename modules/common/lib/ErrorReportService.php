<?php 
include_once(MODULE_PATH."/common/lib/httpClient.php");

class ErrorReportService
{
	protected $m_ReportServer;
	
  	function __construct(&$xmlArr)
   	{      
   	   $this->readMetadata($xmlArr);
   	}

   	protected function readMetadata(&$xmlArr)
   	{      
     	 $this->m_ReportServer 	= $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["REPORTSERVER"];      
   	}
   
   	public function report($reportData)
	{
		$params['data'] = $reportData;	
		return $this->_remoteCall('CollectErrorReport',$params);
	}	
   
	protected function _remoteCall($method,$params=null)
    {
    	$uri = $this->m_ReportServer;
        $cache_id = md5($this->m_Name.$uri. $method .serialize($params));         
        $cacheSvc = BizSystem::getService(CACHE_SERVICE,1);
        $cacheSvc->init($this->m_Name,$this->m_CacheLifeTime);        		
    	if(substr($uri,strlen($uri)-1,1)!='/'){
        	$uri .= '/';
        }
        
        $uri .= "ws.php/udc/CollectService";            
           
        if($cacheSvc->test($cache_id) && (int) $this->m_CacheLifeTime>0)
        {
            $resultSetArray = $cacheSvc->load($cache_id);
        }else{
        	try{        		
		        $argsJson = urlencode(json_encode($params));
        		$query = array(	"method=$method","format=json","argsJson=$argsJson");
		        
		        $httpClient = new HttpClient('POST');
		        foreach ($query as $q)
		            $httpClient->addQuery($q);
		        $headerList = array();
		        $out = $httpClient->fetchContents($uri, $headerList);		        
		        $cats = json_decode($out, true);
		        $resultSetArray = $cats['data'];
		        $cacheSvc->save($resultSetArray,$cache_id);
        	}
        	catch(Exception $e)
        	{
        		$resultSetArray = array();
        	}
        }        
        return $resultSetArray;
    }
}
?>