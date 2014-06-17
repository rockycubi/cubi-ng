<?php
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.data.private
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: TableJoin.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

/**
 * TableJoin class defines the table join used in BizDataObj
 *
 * Configuration of TabelJoin stored in BizDataObj xml file.
 *
 * @package openbiz.bin.data.private
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 */
class TableJoin extends MetaObject
{
    /**
     * Name of tabel that joined to master table
     *
     * @var string
     */
    public $m_Table;

    /**
     * Column/field name of table that joined to master table as reference
     *
     * @var string
     */
    public $m_Column;

    /**
     * ????
     *
     * @var string
     * @todo blank description
     */
    public $m_JoinRef;

    /**
     * Column name of master table as reference for join table
     *
     * @var string
     */
    public $m_ColumnRef;

    /**
     * SQL command for join type like INNER JOIN, LEFT JOIN, RIGHT JOIN or OUTER JOIN
     *
     * @var string
     */
    public $m_JoinType;
	
	/**
     * Additional join condition other than the foriegn matching
     *
     * @var string
     */
	public $m_JoinCondition;

    /**
     *
     * @var <type>
     * @todo what is mean?
     */
    public $m_OnSaveDataObj;

    /**
     * Initialize TableJoin with xml array
     *
     * @param array $xmlArr
     * @param BizDataObj $bizObj
     * @return void
     */
    function __construct(&$xmlArr, $bizObj)
    {
        $this->m_Name = isset($xmlArr["ATTRIBUTES"]["NAME"]) ? $xmlArr["ATTRIBUTES"]["NAME"] : null;
        $this->m_Package = $bizObj->m_Package;
        $this->m_Description= isset($xmlArr["ATTRIBUTES"]["DESCRIPTION"]) ? $xmlArr["ATTRIBUTES"]["DESCRIPTION"] : null;
        $this->m_Table = isset($xmlArr["ATTRIBUTES"]["TABLE"]) ? $xmlArr["ATTRIBUTES"]["TABLE"] : null;
        $this->m_Column = isset($xmlArr["ATTRIBUTES"]["COLUMN"]) ? $xmlArr["ATTRIBUTES"]["COLUMN"] : null;
        $this->m_JoinRef = isset($xmlArr["ATTRIBUTES"]["JOINREF"]) ? $xmlArr["ATTRIBUTES"]["JOINREF"] : null;
        $this->m_ColumnRef = isset($xmlArr["ATTRIBUTES"]["COLUMNREF"]) ? $xmlArr["ATTRIBUTES"]["COLUMNREF"] : null;
        $this->m_JoinType = isset($xmlArr["ATTRIBUTES"]["JOINTYPE"]) ? $xmlArr["ATTRIBUTES"]["JOINTYPE"] : null;
		$this->m_JoinCondition = isset($xmlArr["ATTRIBUTES"]["JOINCONDITION"]) ? $xmlArr["ATTRIBUTES"]["JOINCONDITION"] : null;
        $this->m_OnSaveDataObj = isset($xmlArr["ATTRIBUTES"]["ONSAVEDATAOBJ"]) ? $xmlArr["ATTRIBUTES"]["ONSAVEDATAOBJ"] : null;

        $this->m_BizObjName = $this->prefixPackage($this->m_BizObjName);
    }
}

?>