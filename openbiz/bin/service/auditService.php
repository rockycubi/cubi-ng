<?php
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.service
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: auditService.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

/**
 * auditService class is the plug-in service of handling audit trail of DataObj
 *
 * @package openbiz.bin.service
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class auditService
{
    public $m_AuditDataObj = "system.obj.d_audit_log";

    /**
     * Initialize auditService with xml array metadata
     *
     * @param array $xmlArr
     * @return void
     */
    function __construct(&$xmlArr)
    {
        $this->readMetadata($xmlArr);
    }

    /**
     * Read array meta data, and store to meta object
     *
     * @param array $xmlArr
     * @return void
     */
    protected function readMetadata(&$xmlArr)
    {
        $this->m_AuditDataObj 	= $xmlArr["PLUGINSERVICE"]["ATTRIBUTES"]["BIZDATAOBJ"];
    }

    /**
     * Audit DataObj
     *
     * @param string $dataObjName
     * @return boolean
     * @todo all return false? really?
     */
    public function audit($dataObjName)
    {
        // get audit dataobj
        $auditDataObj = BizSystem::getObject($this->m_AuditDataObj);
        if (!$auditDataObj) return false;

        // get the source dataobj
        $srcDataObj = BizSystem::getObject($dataObjName);
        if (!$srcDataObj) return false;

        // for each onaudit field, add a record in audit dataobj
        $auditFields = $srcDataObj->getOnAuditFields();
        foreach ($auditFields as $field)
        {
            if ($field->m_OldValue == $field->m_Value)
                continue;
            $recArr = $auditDataObj->newRecord();
            if ($recArr == false)
            {
                BizSystem::log(LOG_ERR, "DATAOBJ", $auditDataObj->getErrorMessage());
                return false;
            }

            $profile = BizSystem::getUserProfile();
            $recArr['DataObjName'] = $dataObjName;
            $recArr['ObjectId'] = $srcDataObj->getFieldValue("Id");
            $recArr['FieldName'] = $field->m_Name;
            $recArr['OldValue'] = $field->m_OldValue;
            $recArr['NewValue'] = $field->m_Value;
            $recArr['ChangeTime'] = date("Y-m-d H:i:s");
            $recArr['ChangeBy'] = $profile["USERID"];
            $recArr['ChangeFrom'] = $_SERVER['REMOTE_ADDR'];
            $recArr['RequestURI'] = $_SERVER['REQUEST_URI'];
            $recArr['Timestamp'] = date("Y-m-d H:i:s");
            $ok = $auditDataObj->insertRecord($recArr);
            if ($ok == false)
            {
                BizSystem::log(LOG_ERR, "DATAOBJ", $auditDataObj->getErrorMessage());
                return false;
            }
        }
    }
}
?>