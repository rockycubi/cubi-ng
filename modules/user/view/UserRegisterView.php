<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.user.view
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: UserRegisterView.php 3375 2012-05-31 06:23:11Z rockyswen@gmail.com $
 */

class UserRegisterView extends EasyView
{
	public function allowAccess(){
		$result = parent::allowAccess();
        $do = BizSystem::getObject("myaccount.do.PreferenceDO");
        $rs = $do->fetchOne("[user_id]='0' AND  [section]='Register' AND [name]='open_register'");
      
        $value = $rs->value;
        if($value==0 || $value==null){
        	return 0 ;
        }else{
        	return $result;
        }		
	}
    

}
?>