<?PHP
/**
 * phpOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.phpOpenBiz.org/license/
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@phpOpenBiz.org so we can send you a copy immediately.
 *
 * @package    openbiz.bin.easy.element
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version    $Id: LabelText.php 501 2009-08-30 20:36
 */

/**
 * Base class for Element.
 */
//include_once("Element.php");

/**
 * ColumnValue - class ColumnValue is element that view value in column
 *
 * @package    openbiz.bin.easy.element
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @version 1.2
 * @access public
 */
class ColumnValue extends ColumnText
{    
    /**
     * Draw the element
     *
     * @returns string HTML text
     */
    public function render()
    {
        $val = $this->m_Value;
        return $val;
    }

}

?>
