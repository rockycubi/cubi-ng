<?php

include_once (MODULE_PATH."/common/lib/Pivot.php");

class PivotTableForm extends EasyForm
{
    protected $pivotConfig, $pivotMeta;
    protected $queryLimit = 1000;
	protected $queryId;
    
    function __construct(&$xmlArr)
	{
		parent::__construct($xmlArr);
		
		$this->initPivotConfig();
	}
    
    protected function initPivotConfig()
    {
        $this->pivotConfig['columns'] = array();
		$this->pivotConfig['rows'] = array();
		$this->pivotConfig['datas'] = array();
		foreach ($this->m_DataPanel as $elem) {
			if ($elem->m_PivotType == 'Column') {
				$this->pivotConfig['columns'][$elem->m_FieldName] = $elem->m_Label;
			}
			else if ($elem->m_PivotType == 'Row') {
				$this->pivotConfig['rows'][$elem->m_FieldName] = $elem->m_Label;
			}
			else if ($elem->m_PivotType == 'Data') {
				$this->pivotConfig['datas'][$elem->m_FieldName] = $elem->m_Label;
			}
		}
    }
    
    protected function getPivotData() 
	{
		$recordset = parent::fetchDataSet();
		
		// convert the normal record set to pivot data array
		$data = Pivot::factory($recordset)
				->pivotOn(array_keys($this->pivotConfig['rows']))
				->addColumn(array_keys($this->pivotConfig['columns']), array_keys($this->pivotConfig['datas']))
				->fullTotal()
				->pivotTotal()
				->fetch(1);
		//print_r($data); exit;
		return $data;
	}
    
    public function fetchDataSet()
    {
    }
	
	public function outputAttrs()
    { 
        $output['name'] = $this->m_Name;
        $output['title'] = $this->m_Title;
        $output['description'] = str_replace('\n', "<br />", $this->m_Description);
        $output['meta'] = $this->pivotConfig;
		$data = $this->getPivotData();
		$output['headers'] = $data['splits'];
        $output['data'] = $data['data'];
        return $output;
    }
}
?>