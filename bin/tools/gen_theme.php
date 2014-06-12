#!/usr/bin/env php
<?php
set_time_limit(0);
$theme = @$argv[1];
if (!$theme) {
	echo "usage: php gen_theme.php [theme]".PHP_EOL;
	echo "sample: php gen_theme.php new_theme".PHP_EOL;	
	exit;
}


include_once (dirname(dirname(__FILE__))."/app_init.php");
if(!defined("CLI")){
	exit;
}

include_once dirname(__FILE__)."/require_auth.php";

include_once MODULE_PATH."/theme/lib/ThemeCreator.php";
$creator = new ThemePackCreator($theme);
$result = $creator->createNew();

?>

