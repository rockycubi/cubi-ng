<?php
/*
 * create license command line script
 */
if ($argc<3) {
	echo "usage: php makelic_module.php module_name pass_code".PHP_EOL;
	exit;
}

include_once ("../app_init.php");
if(!defined("CLI")){
	exit;
}

$ENCODER_PATH = "C:\\Program Files\\ionCube Pro PHP Encoder 7.0";
$ENCODER_PATH = "C:\\ioncube";
$module = $argv[1];
$encoder_cmd = $ENCODER_PATH.DIRECTORY_SEPARATOR."ioncube_encoder5";
$source_dir = MODULE_PATH.DIRECTORY_SEPARATOR.$module;
$target_dir = MODULE_PATH.DIRECTORY_SEPARATOR.$module."_encoded";
$license_file = "license_".$module.".txt";
if($argv[2])
{
	$pass_code = $argv[2];	
}else
{	
	$pass_code = "pass_".$module;
}
$callback_file = "callback_".$module.".php";
$properties = "product='cubi-".$module;

$cmd = "\"$encoder_cmd\" \"$source_dir\" -o \"$target_dir\" --with-license $license_file --passphrase $pass_code --license-check auto --callback-file $callback_file --properties \"$properties\" --include-if-property \"$properties\"";
echo $cmd."\n";
system($cmd);

?>