#!/usr/bin/env php
<?php
/*
 * Cubi angular tool
 * - convert existing view/form xml to new format working with angularjs
 */

if ($argc<2) {
	echo "usage: php ng_tool.php module".PHP_EOL;
	exit;
}

include_once ("../app_init.php");

restore_error_handler ();

$viewRules[] = array('/TemplateFile="view.tpl"/','TemplateFile="system_right_panel.tpl.html" PageTemplate="view.tpl.html"');
$formRules[] = array('/BizDataObj="(\w+).do.(\w+)DO" DataService="(\S+)"/','DataService="$3"');
$formRules[] = array('/BizDataObj="(\w+).do.(\w+)DO"/','DataService="/$1/$2s"');
$formRules[] = array('/Link="(\S+)({@:Elem[fld_Id].Value})"/','Link="$1{{dataobj.Id}}"');
$formRules[] = array('/DeleteRecord\(\)/','delete()');
$formRules[] = array('/js:history.go\(-1\)/','back()');
$navText ='
        <Element Name="btn_first" Class="Button" CssClass="button_gray_navi first" Click="gotoPage(1)"/>
        <Element Name="btn_prev" Class="Button" CssClass="button_gray_navi prev" Click="gotoPage(currentPage-1)"/>
        <Element Name="txt_page" Class="LabelText" Text="{{currentPage}} of {{totalPage}}"/>
        <Element Name="btn_next" Class="Button" CssClass="button_gray_navi next" Click="gotoPage(currentPage+1)"/>
        <Element Name="btn_last" Class="Button" CssClass="button_gray_navi last" Click="gotoPage(totalPage)"/>';
$listformRules[] = array('/<NavPanel>(.+)<\/NavPanel>/s','<NavPanel>'.$navText.'</NavPanel>');

if (is_file($argv[1])) {
	convertFile($argv[1]);
	return;
}

$module = $argv[1];
$moduleDir = MODULE_PATH.DIRECTORY_SEPARATOR.$module;

print "To read xml files\n";

$max = 300;
$count = 1;
$files = glob_recursive($moduleDir."/*.xml");
foreach ($files as $file) {
	convertFile($file);
}

function convertFile($file) {
	global $viewRules, $formRules, $listformRules;
	$content = file_get_contents($file);
	if (strpos($file, 'View')>0) {
		echo "Converting view metadata file $file. \n";
		foreach ($viewRules as $r) {
			$content = preg_replace($r[0], $r[1], $content);
		}
		file_put_contents($file, $content);
		if ($count++>$max) break;
	} else if (strpos($file, 'Form')>0) {
		echo "Converting form metadata file $file. \n";
		foreach ($formRules as $r) {
			$content = preg_replace($r[0], $r[1], $content);
		}
		if (strpos($file, 'ListForm')>0) {
			echo "Converting list form metadata file $file. \n";
			foreach ($listformRules as $r) {
				$content = preg_replace($r[0], $r[1], $content);
			}
		}
		file_put_contents($file, $content);
		if ($count++>$max) break;
	} else {
		echo "Skip file $file. \n";
	}
}

function glob_recursive($pattern, $flags = 0)
{
	$files = glob($pattern, $flags);
   
	foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir)
	{
		$files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
	}
   
	return $files;
}
?>