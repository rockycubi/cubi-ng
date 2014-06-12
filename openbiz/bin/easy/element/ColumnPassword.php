<?PHP
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.easy.element
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: ColumnPassword.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

//include_once("ColumnText.php");

/**
 * ColumnPassword class is element for ColumnPassword,
 * show password text on data list
 *
 * @package openbiz.bin.easy.element
 * @author jixian2003
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class ColumnPassword extends ColumnText
{
    public $m_Sortable;
    public $m_ColumnStyle;
    public $m_MaskChar='*';
    public $m_MaskLength=6;
    
    /**
     * Read array meta data, and store to meta object
     *
     * @param array $xmlArr
     * @return void
     */
    protected function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_MaskChar = isset($xmlArr["ATTRIBUTES"]["MASKCHAR"]) ? $xmlArr["ATTRIBUTES"]["MASKCHAR"] : null;
        $this->m_MaskLength = isset($xmlArr["ATTRIBUTES"]["MASKLENGTH"]) ? $xmlArr["ATTRIBUTES"]["MASKLENGTH"] : null;
    }

    /**
     * Render element, according to the mode
     *
     * @return string HTML text
     */
    public function render()
    {
        $sHTML = "<span $style $func> ".str_repeat($this->m_MaskChar, $this->m_MaskLength)." </span>";
        return $sHTML;
    }

}

?>