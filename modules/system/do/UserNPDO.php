<?php 
/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.system.do
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 */
 
class UserNPDO extends BizDataObj
{
	// set password to empty
    protected function _fetch_record(&$resultSet)
    {
    	if(!is_array($resultSet)){
    		return null;
    	}
        if ($sqlArr = current($resultSet))
        {
            $this->m_CurrentRecord = $this->m_BizRecord->convertSqlArrToRecArr($sqlArr);
            $this->m_CurrentRecord = $this->m_BizRecord->getRecordArr($sqlArr);
			// mask password field
			$this->m_CurrentRecord['password'] = '********';
			$this->m_CurrentRecord['password_repeat'] = '********';
            $this->m_RecordId = $this->m_CurrentRecord["Id"];
            next($resultSet);
        }
        else
        {
            return null;
        }
        return $this->m_CurrentRecord;
    }
}  
?>
