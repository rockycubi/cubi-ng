<?php 
class MenuService extends MetaObject
{
	// return list of bc items
	public function getBreadCrumb()
	{
		$menuTreeDo = BizSystem::getObject("menu.do.MenuTreeDO"); 
		$breadCrumb = $menuTreeDo->getBreadCrumb($_SERVER['REQUEST_URI']);
		// only output Id, Name, URL of each breadcrumb item
		$bc = array();
		foreach ($breadCrumb as $menuRecord) {
			$id = $menuRecord->m_Id;
			$name = $menuRecord->m_Name;
			$url = $menuRecord->m_URL;
			$bc[] = array('id'=>$id, 'name'=>$name, 'url'=>$url);
		}
		return $bc;
	}
}
?>