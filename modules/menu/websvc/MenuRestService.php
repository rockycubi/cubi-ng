<?php

include_once MODULE_PATH.'/websvc/lib/RestService.php';
include_once OPENBIZ_BIN.'data/private/BizDataObj_SQLHelper.php';

class MenuRestService extends RestService
{
	protected $resourceDOMap = array('menus'=>'menu.do.MenuTreeDO');
	protected $extraDataReturn = null;
	
	/*
	 * Query by page, rows, sort, sorder
	 *
	 * @param string $resource
	 * @param Object $request, Slim Request object
	 * @param Object $response, Slim Response object
     * @return void 
	 */
	public function query($resource, $request, $response)
    {
		$depth = $request->params('depth');
		$PId = $request->params('PId');
		if ($depth) {
			return $this->queryMenuTree($resource, $request, $response);
		}
		else {
			// run normal query to get dataset, also get parent menu nodes
			$DOName = $this->getDOName($resource);
			$dataObj = BizSystem::getObject($DOName);
			$this->extraDataReturn = array("parentNodes"=>$this->getNodeParents($dataObj, $PId)); 
			return parent::query($resource, $request, $response);
		}
	}
	
	public function queryMenuTree($resource, $request, $response)
	{
		$DOName = $this->getDOName($resource);
		if (empty($DOName)) {
			$response->status(404);
			$response->body("Resource '$resource' is not found.");
			return;
		}
		
		// get page and sort parameters
		$allGetVars = $request->get();
		$queryParams = array();
		foreach ($allGetVars as $key=>$value) {
			if ($key == 'depth' || $key == 'format') {
				continue;
			}
			//if ($value !== null && $value !== '') {
				$queryParams[$key] = $value;
			//}
		}
		$depth = $request->params('depth');
		if (!$depth) $depth = 1;
		
		$dataObj = BizSystem::getObject($DOName);
		$tree = $dataObj->fetchTreeByQueryParams($queryParams, $depth);
		/*
		// include app tab - PId's sibling nodes
		$PId = $request->params('PId');
		// first find the menu record with Id=PId and get its app_root_menu_PId
		$appRootMenuRec = $dataObj->fetchById($PId);
		$appRootMenuRecPId = $appRootMenuRec['PId'];
		// then find menu records whose PId=app_root_menu_PId
		$appTab = $dataObj->fetchTreeBySearchRule("[PId]='$appRootMenuRecPId' AND [published]=1", 1);
		
		$comboMenus = array('tree'=>$tree,'tab'=>$appTab);
		*/
		$format = strtolower($request->params('format'));
		
		$response->status(200);
		if ($format == 'json') {
			$response['Content-Type'] = 'application/json';
			$response->body(json_encode($tree));
		}
		else {
			$response['Content-Type'] = "text/xml; charset=utf-8"; 
			$xml = new array2xml('Data');
			$xml->createNode($tree);
			$response->body($xml);
		}
		return;
    }
	
	protected function setResponse($dataArray, $response, $format) {
		if ($this->extraDataReturn) {
			foreach ($this->extraDataReturn as $k=>$v) {
				$dataArray[$k] = $v;
			}
		}
		return parent::setResponse($dataArray, $response, $format);
	}
	
	protected function getNodeParents($dataObj, $id) {
   		$pathArray = array();
	    $this->fetchNodePath($dataObj, "[Id]='$id'", $pathArray);
	    return $pathArray;
   	}
   
	protected function fetchNodePath($dataObj, $nodeSearchRule, &$pathArray) {
    	$recordList = $dataObj->directFetch($nodeSearchRule);
    	if(count($recordList)>=1){
    		if($recordList[0]['PId']!='' && $recordList[0]['PId']!='0'){
    			$searchRule = "[Id]='".$recordList[0]['PId']."'";
    			$this->fetchNodePath($dataObj, $searchRule, $pathArray);
    		}
    		array_push ($pathArray,$recordList[0]);
    		return $pathArray;
    	}
    }
}
?>