<?php
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.common.element
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: LabelTitle.php 3355 2012-05-31 05:43:33Z rockyswen@gmail.com $
 */

class LabelTitle extends LabelText
{
	public function getIDPrefix()
	{
		$rec = $this->getFormObj()->getActiveRecord();
		$id = $rec["Id"];
		$id_display = "<span class=\"title_id\" style='margin-left:10px;' >$id</span>";
		return $id_display;
	}
	
	public function render(){
		$sHTML = parent::render();
		$sHTML = $sHTML.$this->getIDPrefix();
		return $sHTML;
	}
}
?>