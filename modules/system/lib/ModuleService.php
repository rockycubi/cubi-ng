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
 * @version   $Id: ModuleService.php 4449 2012-10-22 06:21:42Z hellojixian@gmail.com $
 */

class ModuleService
{
	protected $m_ModuleDO = "system.do.ModuleCachedDO";
	
	public function isModuleInstalled($module)
	{
		$do = BizSystem::getObject($this->m_ModuleDO);
		$modRec = $do->fetchOne("[name]='$module'");
		if($modRec)
		{
			return $modRec['version'];
		}
		else
		{
			return false;	
		}
	}
}
?>