<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.common.widget
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: DashboardConfigWidget.php 3355 2012-05-31 05:43:33Z rockyswen@gmail.com $
 */

class DashboardConfigWidget extends EasyForm
{
	protected $userWidgetDOName = "common.do.UserWidgetDO";
	
	// add a widget to current dashboard view
	public function addWidget($widgetName)
	{
		// remove "_widget" from the widget name
		$widgetName = str_replace("_widget", "", $widgetName);
		// add widget to user_widget table
		$userWidgetDo = BizSystem::getObject($this->userWidgetDOName);
		$userWidgetTable = $userWidgetDo->m_MainTable;
		$db = $userWidgetDo->getDbConnection();
		
		$myProfile = BizSystem::getUserProfile();
		$myUserId = $myProfile['Id'];
		$currentView = BizSystem::instance()->getCurrentViewName();
		
		$searchRule = "[user_id]=$myUserId and [widget]='$widgetName' and [view]='$currentView'";
		$record = $userWidgetDo->fetchOne($searchRule);
		if ($record) {
			BizSystem::clientProxy()->showClientAlert("The widget $widgetName is already on the page.");
		}
		else {
			$data = array('user_id'=>$myUserId, 'widget'=>$widgetName, 'view'=>$currentView, 'ordering'=>0);
			$db->insert($userWidgetTable, $data);
		}
	}
	
	// remove a widget from current dashboard view
	public function removeWidget($widgetName)
	{
		// remove "_widget" from the widget name
		$widgetName = str_replace("_widget", "", $widgetName);
		// remove widget from the user_widget table
		$userWidgetDo = BizSystem::getObject($this->userWidgetDOName);
		$userWidgetTable = $userWidgetDo->m_MainTable;
		$db = $userWidgetDo->getDbConnection();
		
		$myProfile = BizSystem::getUserProfile();
		$myUserId = $myProfile['Id'];
		$currentView = BizSystem::instance()->getCurrentViewName();
		
		$searchRule = "[user_id]=$myUserId and [widget]='$widgetName' and [view]='$currentView'";
		$record = $userWidgetDo->fetchOne($searchRule);
		if ($record) {
			//echo "to delete id=".$record['Id'];
			$db->delete($userWidgetTable, "id=".$record['Id']);
		}
		 // reload current page
		BizSystem::clientProxy()->runClientFunction("window.location.reload()");
	}
	
	// reoder widgets on current dashboard view
	//  column_1=item_2,item_1&column_2=&
	public function reorderWidgets()
	{
		$sortorder = BizSystem::clientProxy()->getFormInputs('_widgets');
		
		// get the widgets ordering of columns
		parse_str($sortorder, $output);
		$columns = array();
		$columnCounts = array();
		$n = 0;
		foreach ($output as $k=>$val) {
			if (strpos($k, 'column')===0) {
				$columns[$n] = explode(",",$val);
				$columnCounts[$n] = count($columns[$n]);
				$n++;
			}
		}
		//print_r($columns);
		
		// update ordering of all user_widget records
		$userWidgetDo = BizSystem::getObject($this->userWidgetDOName);
		$userWidgetTable = $userWidgetDo->m_MainTable;
		$db = $userWidgetDo->getDbConnection();
		
		$myProfile = BizSystem::getUserProfile();
		$myUserId = $myProfile['Id'];
		$currentView = BizSystem::instance()->getCurrentViewName();
		
		$m = 1;
		foreach ($columns as $column) {
			$n = 1;
			foreach ($column as $widgetName) {
				if (empty($widgetName)) continue;
				// remove "_widget" from the widget name
				$widgetName = str_replace("_widget", "", $widgetName);
				// find the widget by name in the current view, set the new order
				$searchRule = "[user_id]=$myUserId and [widget]='$widgetName' and [view]='$currentView'";
				$record = $userWidgetDo->fetchOne($searchRule);
				$ordering = $n*10;
				if ($record) {	// update the order
					$data = array('column'=>$m, 'ordering'=>$ordering);
					$db->update($userWidgetTable, $data, "id=".$record['Id']);
				}
				else {	// insert a record with the order
					$data = array('user_id'=>$myUserId, 'widget'=>$widgetName, 'view'=>$currentView, 'column'=>$m, 'ordering'=>$ordering);
					$db->insert($userWidgetTable, $data);
				}
				$n++;
			}
			$m++;
		}
	}
}
?>