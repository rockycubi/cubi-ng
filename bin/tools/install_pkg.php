#!/usr/bin/env php
<?php
/*
 * Cubi package install command line script
 */

if ($argc<2) {
	echo "usage: php install_pkg.php module_name".PHP_EOL;
	exit;
}
 
include_once (dirname(dirname(__FILE__))."/app_init.php");

$pkgname = $argv[1];

$packageService = "package.lib.PackageService";
// get package service 
$pkgsvc = BizSystem::GetObject($packageService);

$pkgfile = $pkgsvc->downloadPackage($pkgname);

?>