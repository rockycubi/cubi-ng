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
 * @version   $Id: HTMLBlock.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

//include_once("Element.php");

/**
 * HTMLBlock class is element that write HTML block borderd by <div> tag
 * 
 * <code>
 * <div ID='$name' $htmlAtribut $style $func> $text </div>
 * </code>
 *
 * @package openbiz.bin.easy.element
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class HTMLBlock extends Element
{
    /**
     * Render/Draw the control according to the mode
     *
     * @return string HTML text
     */
    public function render()
    {
        $style = $this->getStyle();
        $func = $this->getFunction();

        return "<div ID='$this->m_Name' $this->m_HTMLAttr $style $func>".$this->m_Text."</div>";
    }
}

?>