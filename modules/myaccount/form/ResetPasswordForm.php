<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.myaccount.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ResetPasswordForm.php 5521 2014-06-05 14:03:12Z rockyswen@gmail.com $
 */

/**
 * ResetPasswordForm class - implement the logic of reset password form
 *
 * @package user.form
 * @author Jixian Wang
 * @copyright Copyright (c) 2005-2009
 * @access public
 */

class ResetPasswordForm extends EasyForm
{
	public function outputAttrs() {
		$profile= BizSystem::getUserProfile();
		$userId = $profile['Id'];
		
		$output = parent::outputAttrs();
		$output['queryString'] = "Id=".$userId;
		
		return $output;
	}
}  
?>   