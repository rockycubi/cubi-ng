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
 * @version   $Id: queryService.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

/**
 * queryService class is the plug-in service of fetching query results
 *
 * @package   openbiz.bin.service
 * @author    Rocky Swen
 * @copyright Copyright (c) 2003-2009, Rocky Swen
 * @access    public
 */
class queryService
{

    /**
     * Initialize queryService
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Fetch all data from specified data object
     *
     * @param string $doName data object name
     * @param string $searchRule rule of serach
     * @return array array of records
     */
    public function fetchAll($doName, $searchRule)
    {
        $do = BizSystem::getObject($doName);
        if (!$do)
        {
            throw new Exception("System cannot get object of $doName.");
            return;
        }
        return $do->directFetch($searchRule);
    }

    /**
     * Fetch record
     * 
     * @param string $doName data object name
     * @param string $searchRule rule of search
     * @return mixed
     */
    public function fetchRecord($doName, $searchRule)
    {
        $do = BizSystem::getObject($doName);
        if (!$do)
        {
            throw new Exception("System cannot get object of $doName.");
            return;
        }
        $r = $do->directFetch($searchRule, 1);
        if (count($r)>0)
            return $r[0];
        return null;
    }

    /**
     * Fetch field
     *
     * @param string $doName data object name
     * @param string $searchRule search rule
     * @param string $fieldName field name
     * @return mixed
     */
    public function fetchField($doName, $searchRule, $fieldName)
    {
        $rec = $this->fetchRecord($doName, $searchRule);
        if ($rec)
            return $rec[$fieldName];
        return null;
    }
}

?>