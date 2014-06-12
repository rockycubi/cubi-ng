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
 * @version   $Id: PagesizeSelector.php 2711 2010-11-30 18:00:24Z jixian2003 $
 */

//include_once("DropDownList.php");

/**
 * InputText class is element for input text
 *
 * @package openbiz.bin.easy.element
 * @author Jixian W.
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class PagesizeSelector extends DropDownList
{

	protected $m_FormPrefix = true;
    protected function getList(){
    	$pagesizes = array(5,10,15,20,25,50);
    	$list = array();
    	foreach($pagesizes as $size){
    		array_push($list,array("val"=>$size,"txt"=>$size));
    	}
    	return $list;
    }

}

?>