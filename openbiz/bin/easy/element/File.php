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
 * @version   $Id: File.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

//include_once("InputElement.php");

/**
 * File class is the element for File
 *
 * @package openbiz.bin.easy.element
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class File extends InputElement
{

    /**
     * Render the element, according to the mode
     *
     * @return string HTML text
     */
    public function render()
    {
        $disabledStr = ($this->getEnabled() == "N") ? "disabled=\"true\"" : "";
        $style = $this->getStyle();
        $func = $this->getFunction();
		$sHTML .= "<input type=\"file\" name='$this->m_Name' id=\"" . $this->m_Name ."\" value='$this->m_Value' $disabledStr $this->m_HTMLAttr $style $func />";
        return $sHTML;
    }

}

?>