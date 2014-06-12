<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.common.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: LicenseInvalidForm.php 4971 2012-12-28 13:01:51Z hellojixian@gmail.com $
 */

require_once "LicenseForm.php";
class LicenseInvalidForm extends LicenseForm
{
	public function fetchData()
	{
		$this->getAppRegister();	
		return parent::fetchData();
	}
	
	public function outputAttrs()
	{
		$this->getAppModuleName();			
		$result = parent::outputAttrs();		
		$result['license_message'] = $this->getErrorMessage();
		return $result;
	}

}
?>