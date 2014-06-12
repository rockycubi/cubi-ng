<?php
require_once('app_init.php');
if (!APPBUILDER)
{
    echo "Sorry, AppBuilder/MetaEdit disable.";
    exit;
}

$modsvc = BizSystem::getObject("system.lib.ModuleService");
if(!$modsvc->isModuleInstalled('appbuilder'))
{
    echo "Sorry, AppBuilder is not installed.";
    exit;	
}
if($_GET['action']=='launch')
{
	$url = APP_INDEX."/appbuilder/dashboard";
	header("LOCATION: $url");
	exit;
}
$metaobj = $_GET['metaobj'];
$url = APP_INDEX."/appbuilder/xml_edit/metaobj=".$metaobj;
header("LOCATION: $url");
?>