<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.service
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: UpgradeService.php 3485 2012-06-18 23:41:37Z agus.suhartono@gmail.com $
 */

class UpgradeService
{
    protected $_upgradeModules = array();
/*
    protected $repositoryUrl; // repository url
    
    public function __construct($repositoryUrl) 
    {
        $this->repositoryUrl = $repositoryUrl;
    }
    
    public function getUpgradeList()
    {
        // collect installed modules/packages
        
        // find if repository has newer versions
        
    }
    
    public function downloadPackage($package)
    {
     
    }
*/
    /*
        phing unzip package
        upgrader.php to install package
            backup, copy, sql upgrade
            load module
    */
    public function upgradePackage($cpkFilePath)
    {
        // tmp folder
        $tmpFolder = APP_HOME."/files/tmpfiles";
        $toFolder = $tmpFolder."/".time();
        // unzip cpk file
        try {
            $this->unpack($cpkFilePath, $toFolder);
        }
        catch (Exception $e) {
            echo "ERROR in unpack. ".$e->getMessage();
            return false;
        }
        echo "OK. $cpkFilePath is unpacked to $toFolder\n";
        
        // copy files to target folder from the tmp folder
        $this->filecopy($toFolder);
        
        // invoke module upgrade command
        echo "invoke module upgrade command\n";
    }
    
    protected function filecopy($tmpFolder)
    {
        $dir0s = ob_scandir($tmpFolder);
        // copy module to cubi/upgrade folder
        foreach ($dir0s as $dir0) {
            $dirs = ob_scandir($tmpFolder."/$dir0");
            foreach ($dirs as $dir) {
                $srcDir = $tmpFolder."/$dir0/$dir";
                $dstDir = ($dir == 'modules') ? APP_HOME."/upgrade/modules" : APP_HOME."/$dir";
                echo "copy $srcDir to $dstDir \n";
                recurse_copy($srcDir, $dstDir);
                if ($dir == 'modules')
                    $this->_upgradeModules = ob_scandir($srcDir);
            }
        }
        //print_r($this->_upgradeModules);
    }
    
    protected function unpack($tarfile, $toFolder)
    {
        // include PEAR Tar class
        include_once(APP_HOME."/bin/phing/classes/Archive/Tar.php");
        if (!class_exists('Archive_Tar')) {
            throw new Exception("You must have installed the PEAR Archive_Tar class in order to use UntarTask.");
        }
        $tar = $this->initTar($tarfile);
        $result = $tar->extract($toFolder);
        if (!$result) {
            throw new Exception("Could not extract tar file: $tarfile");
        }
    }
    
    /**
     * Init a Archive_Tar class with correct compression for the given file.
     *
     * @param $tarfile
     * @return Archive_Tar the tar class instance
     */
    private function initTar($tarfile)
    {
        $compression = null;
        $tarfileName = basename($tarfile);
        $mode = strtolower(substr($tarfileName, strrpos($tarfileName, '.')));
        if ($mode == ".cpk")
            $mode = ".gz";

        $compressions = array(
                'gz' => array('.gz', '.tgz',),
                'bz2' => array('.bz2',),
            );
        foreach ($compressions as $algo => $ext) {
            if (array_search($mode, $ext) !== false) {
                $compression = $algo;
                break;
            }
        }
        //echo "tarfilename is $tarfileName, mode is $mode, $compression \n";
        return new Archive_Tar($tarfile, $compression);
    }
}

function ob_scandir($dir)
{
    $retDirs = null;
    $dir0s = scandir($dir);
    foreach ($dir0s as $dir0) {
        if (( $dir0 == '.' ) || ( $dir0 == '..' ) || ( $dir0 == '.svn' )) continue;
        $retDirs[] = $dir0;
    }
    //print_r($retDirs);
    return $retDirs;
}

function recurse_copy($src,$dst) {
    $dir = opendir($src);
    @mkdir($dst, 0777, true);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' ) && ( $file != '.svn' )) {
            if ( is_dir($src . '/' . $file) ) {
                recurse_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
} 

/* test code
define ('APP_HOME', "C:\\xampp\\htdocs\\ob3\\cubi");
$upgradeSvc = new UpgradeService();
$upgradeSvc->upgradePackage(APP_HOME."/files/tmpfiles/cubi_help-0.1_T1235_20110330_1414.cpk");
*/
