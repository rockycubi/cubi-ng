<?PHP

include_once(OPENBIZ_HOME."/bin/easy/element/ColumnText.php");

/**
 * PivotColumnText class is element for pivot column,
 * show text on data list
 *
 * @package openbiz.bin.easy.element
 * @author Rocky Swen
 * @copyright Copyright (c) 2005-2009
 * @access public
 */
class PivotColumnText extends ColumnText
{
    public $m_PivotType;

    /**
     * Read array meta data, and store to meta object
     *
     * @param array $xmlArr
     * @return void
     */
    protected function readMetaData(&$xmlArr)
    {
        parent::readMetaData($xmlArr);
        $this->m_PivotType = isset($xmlArr["ATTRIBUTES"]["PIVOTTYPE"]) ? $xmlArr["ATTRIBUTES"]["PIVOTTYPE"] : null;     
	}
}
?>