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
 * @version   $Id: excelService.php 2792 2010-12-06 01:17:09Z rockys $
 */

include_once (OPENBIZ_HOME."/messages/excelService.msg");

/**
 * excelService -
 * class excelService is the plug-in service of printing {@link EasyForm} to excel
 *
 * @package   openbiz.bin.service
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class excelService
{

    /**
     * Initialize excelService with xml array metadata
     *
     * @param array $xmlArr
     * @return void
     */
    function __construct(&$xmlArr)
    {
    }

    /**
     * Render the excel output with CSV format
     *
     * @param string $objName object name which is the bizform name
     * @return void
     */
    function renderCSV ($objName)
    {
        $this->render($objName, ",", "csv");

        //Log This Export
        BizSystem::log(LOG_INFO, "ExcelService", "Export CSV file.");
    }

    /**
     * Render the excel output with TSV format
     *
     * @param string $objName object name which is the bizform name
     * @return void
     */
    function renderTSV ($objName)
    {
        $this->render($objName, "\t", "tsv");
        //Log This Export
        BizSystem::log(LOG_INFO, "ExcelService", "Export TSV file.");
    }

    /**
     * Import from CSV file
     * NOTE: This method must be called from a popup form where a file is uploaded.
     *       The parent form of the popup form is the target to import.
     *
     * @param string $objName
     * @return void
     */
    public function importCSV ($objName)
    {
        // read in file from $_FILE
        foreach ($_FILES as $file)
        {
            $error = $file['error'];
            if ($error != 0)
            {
                $this->reportError($error);
                return;
            }

            $tmpFileName  = $file['tmp_name'];
            break;
        }
        //echo "upload file name = $tmpFileName";
        $filename = $file['name'];
        if (strpos($filename,".csv")===false)
        {
        	$errorMsg = BizSystem::getMessage("EXCELSVC_INVALID_FILE",array($filename));
        	BizSystem::log(LOG_ERR, "EXCEL SERVICE", "Import error = ".$errorMsg);
            BizSystem::clientProxy()->showClientAlert($errorMsg);
            return;
        }
        /* @var $formObj EasyForm */
        $formObj = BizSystem::objectFactory()->getObject($objName); // get the existing EasyForm object
        $parentFormObj = BizSystem::objectFactory()->getObject($formObj->m_ParentFormName);
        $dataObj = $parentFormObj->getDataObj();

        $handle = fopen($tmpFileName, "r");
        $fields = fgetcsv($handle, 2000, ",");
        if (!$fields || count($fields)<2)
        {
        	$errorMsg = BizSystem::getMessage("EXCELSVC_INVALID_FILE",array($filename));
        	BizSystem::log(LOG_ERR, "EXCEL SERVICE", "Import error = ".$errorMsg);
            BizSystem::clientProxy()->showClientAlert($errorMsg);
            return;
        }

        // convert form element names to DO field names
        foreach ($parentFormObj->m_DataPanel as $element)
        {
            $elem_fields[$element->m_Label] = $element->m_FieldName;
        }
        
        // validate with dataobj fields
        for ($i=0; $i<count($fields); $i++)
        {
            $fields[$i] = $elem_fields[$fields[$i]];
            $field = $fields[$i];
            if (!$dataObj->getField($field))
            {
                $errorMsg = BizSystem::getMessage("EXCELSVC_INVALID_COLUMN",array($field, $dataObj->m_Name));
                BizSystem::log(LOG_ERR, "EXCEL SERVICE", "Import error = ".$errorMsg);
                BizSystem::clientProxy()->showClientAlert($errorMsg);
                return;
            }
        }

        while (($arr = fgetcsv($handle, 2000, ",")) !== FALSE)
        {
            if (count($arr) != count($fields))
                continue;
            unset($recArr);
            $i = 0;
            for ($i=0; $i<count($arr); $i++)
            {
                $recArr[$fields[$i]] = $arr[$i];
            }
            //print_r($recArr); echo "<hr>";

            $dataRec = new DataRecord(null, $dataObj);
            foreach ($recArr as $k => $v)
                $dataRec[$k] = $v;

            $ok = $dataRec->save();
            if (! $ok)
            {
                // NOTE: EasyForm::processDataObjError() not return any value (void)
                return $formObj->processDataObjError($ok);
            }

        }
        fclose($handle);

        // in case of popup form, close it, then rerender the parent form
        if ($formObj->m_ParentFormName)
        {
            $formObj->close();

            $formObj->renderParent();
        }
    }

    /**
     * Render excel data
     *
     * @param string $objName object form name
     * @param string $separator sparator between column
     * @param string $ext file extension
     * @return void
     */
    protected function render($objName, $separator=",", $ext="csv")
    {
        ob_end_clean();
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: filename=" . $objName."_".date('Y-m-d') . "." . $ext);
        header("Content-Transfer-Encoding: binary");

        $dataTable = $this->getDataTable($objName);
        foreach ($dataTable as $row)
        {
            $line = "";
            foreach ($row as $cell) {
                $txt = $this->strip_cell($cell);
                //if (!empty($txt))
				//Changed condition from empty()to is_null() to allow null fields from being truncated out when csv generated (cyril ogana 2011-11-11)
				//TODO: Need to add condition to leave out fields which have no column name e.g. the first field which has rowcheckboxes?
				if (!is_null($txt))
                    $line .= "\"" . $txt . "\"$separator";
            }
            $line = rtrim($line, $separator);
            $line = iconv("UTF-8","GB2312//IGNORE",$line);
            echo rtrim($line) . "\n";
        }
    }

    protected function strip_cell($str){
    	$str = strip_tags($str);
    	$str = str_replace("\n",'',$str);
    	$str = str_replace("\r",'',$str);
    	return $str;
    }


    /**
     * Get raw data to display in the spreadsheet. header and data table
     *
     * @param string $objName
     * @return array
     */
    protected function getDataTable($objName)
    {
        /* @var $formObj EasyForm */
        $formObj = BizSystem::objectFactory()->getObject($objName); // get the existing EasyForm|BizForm object

        // if BizForm, call BizForm::renderTable
        if ($formObj instanceof BizForm)
        {
            $dataTable = $formObj->renderTable();
        }
        // if EasyForm, call EasyForm->DataPanel::renderTable
        if ($formObj instanceof EasyForm)
        {
            $recordSet = $formObj->fetchDataSet(); 
            $dataSet = $formObj->m_DataPanel->renderTable($recordSet);
            foreach ($dataSet['elems'] as $elem)
            {
                $labelRow[] = $elem['label'];
            }
            $dataTable = array_merge(array($labelRow), $dataSet['data']);
        }

        return $dataTable;

    }

    /**
     * Show error message
     *
     * @param int $error error number
     */
    protected function reportError($error)
    {
        if ($error==1)
            $errorStr = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
        else if ($error==2)
            $errorStr = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
        else if ($error==3)
            $errorStr = "The uploaded file was only partially uploaded";
        else if ($error==4)
            $errorStr = "No file was uploaded";
        else if ($error==6)
            $errorStr = "Missing a temporary folder";
        else if ($error==7)
            $errorStr = "Failed to write file to disk";
        else
            $errorStr = "Error in file upload";

        BizSystem::clientProxy()->showErrorMessage($errorStr);
    }
}
?>