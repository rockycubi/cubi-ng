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
 * @version   $Id$
 */

/**
 * localeInfoService class is the plug-in service of setting localeInfo
 *
 * @package openbiz.bin.service
 * @author    Agus Suhartono
 * @copyright Copyright (c) 2010, Rocky Swen
 * @access    public
 */
class localeInfoService extends  MetaObject
{

    /**
     * Initialize localInfoService with xml array metadata
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
    }

    /**
     * Get local info
     * @return array array like  
     */
    public function getLocaleInfo()
    {
        return array(
            'decimal_point'     => ',',
            'thousands_sep'     => '.',
            'int_curr_symbol'   => 'IDR',
            'currency_symbol'   => 'Rp.',
            'mon_decimal_point' => ',',
            'mon_thousands_sep' => '.',
            'positive_sign'     => '',
            'negative_sign'     => '-',
            'int_frac_digits'   => 4,
            'frac_digits'       => 4,
            'p_cs_precedes'     => 4,
            'p_sep_by_space'    => 4,
            'n_cs_precedes'     => 4,
            'n_sep_by_space'    => 4,
            'p_sign_posn'       => 4,
            'n_sign_posn'       => 4
        );
    }

}

?>
