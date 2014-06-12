#!/usr/bin/env php
<?php
set_time_limit(0);
if ($argc<3) {
	echo "usage: php gen_lang.php [module] [locale] [tranlsate]".PHP_EOL;
	echo "generate language pack for system only: # php gen_lang.php ! zh_CN -t".PHP_EOL;
	echo "generate language pack for one module: # php gen_lang.php user zh_CN -t".PHP_EOL;
	echo "generate language pack for all modules and system: # php gen_lang.php all zh_CN -t".PHP_EOL;
	echo "generate language pack for a theme: # php gen_lang.php themes/default zh_CN -t".PHP_EOL;
	exit;
}
$argv_bak = $argv;
$module = $argv[1];
$lang = $argv[2];
$tranlsate = (isset($argv[3]) && $argv[3]=='-t') ? true : false;

include_once dirname(dirname(__FILE__))."/app_init.php";
if(!defined("CLI")){
	exit;
}

include_once dirname(__FILE__)."/require_auth.php";

include_once MODULE_PATH."/translation/lib/LangPackCreator.php";
if ($module == '!') {
	genLangSystemOnly($lang,$tranlsate);
}
else {
	if ($module != 'all') {
		genLangModule($module,$lang,$tranlsate);
	}
}

if ($module == 'all') {
	foreach (glob(MODULE_PATH.DIRECTORY_SEPARATOR.'*',GLOB_ONLYDIR) as $dir)
    {
    	$module_name = str_replace(MODULE_PATH.DIRECTORY_SEPARATOR,"",$dir);
    	genLangModule($module_name,$lang,$tranlsate);   	
    }
}

function genLangModule($module, $lang, $tranlsate)
{
	$creator = new LangPackCreator($lang);
	$creator->module = $module;
	$result = $creator->createNew($tranlsate);
}

function genLangSystemOnly($lang, $tranlsate)
{
	$creator = new LangPackCreator($lang);
	$creator->systemOnly = true;
	$result = $creator->createNew($tranlsate);
}

?>

