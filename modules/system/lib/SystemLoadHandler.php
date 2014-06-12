<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.system.lib
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: SystemLoadHandler.php 3372 2012-05-31 06:19:06Z rockyswen@gmail.com $
 */

include_once (MODULE_PATH."/system/lib/ModuleLoadHandler.php");

class SystemLoadHandler implements ModuleLoadHandler
{
    public function beforeLoadingModule($moduelLoader)
    {
    }
    
    public function postLoadingModule($moduelLoader)
    {
    }
}

?>