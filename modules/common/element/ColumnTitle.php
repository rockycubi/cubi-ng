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
 * @version   $Id: ColumnTitle.php 5433 2014-05-04 05:54:04Z rockyswen@gmail.com $
 */

class ColumnTitle extends ColumnText
{
	/*public function getIDPrefix()
	{
		$rec = $this->getFormObj()->getActiveRecord();
		
		$id = $rec["Id"];
		if(!$id && $this->m_FieldName=='Id'){
			$id = $this->m_Value;
		}
		$id_display = "<span class=\"title_id\" >$id</span>";
		return $id_display;
	}
	
	public function render(){
		$sHTML = parent::render();
		if($this->m_FieldName!='Id'){
			$sHTML = $this->getIDPrefix().$sHTML;
		}
		else{
			$sHTML = $this->getIDPrefix();
		}
		return $sHTML;
	}*/
}
?>