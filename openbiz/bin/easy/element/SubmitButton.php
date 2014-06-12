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
 * @version   $Id: SubmitButton.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

//include_once("InputElement.php");


/**
 * SubmitButton class is element for render html submit button
 *
 * @package openbiz.bin.easy.element
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class SubmitButton extends InputElement
{

    /**
     * Render, draw the element according to the mode
     *
     * @return string HTML text
     */
    public function render()
    {
        $disabledStr = ($this->getEnabled() == "N") ? "DISABLED=\"true\"" : "";
        $style = $this->getStyle();
        $func = $this->getFunction();
        $sHTML .= "<INPUT TYPE=SUBMIT NAME='$this->m_Name' ID=\"" . $this->m_Name ."\" VALUE='$this->m_Text' $disabledStr $this->m_HTMLAttr $style $func />";
        return $sHTML;
    }
}

?>
