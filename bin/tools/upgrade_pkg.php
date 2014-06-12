#!/usr/bin/env php
<?php
/*
 * Cubi upgrade command line script
 * - first copy the new module source to /cubi/upgrade/modules/mod_name/ folder
 * - then run php /cubi/tool/upgrade.php mod_name
 */

if ($argc<2) {
	echo "usage: php upgrade_pkg.php package_file".PHP_EOL;
	exit;
}
$package_file = $argv[1];

include_once ("../app_init.php");
if(!defined("CLI")){
	exit;
}

include_once (MODULE_PATH."/service/UpgradeService.php");

// locate the package file
if (!file_exists($package_file)) {
    $tmpPackageFolder = APP_HOME."/files/packages";
    $package_file = $tmpPackageFolder."/$package_file";
    if (!file_exists($package_file)) {
        echo "Cannot locate the package at $package_file\n";
        exit;
    }
}

$upgradeSvc = new UpgradeService();
$upgradeSvc->upgradePackage($package_file);

?>