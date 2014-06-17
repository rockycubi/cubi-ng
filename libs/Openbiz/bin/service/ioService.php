<?php
/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin.easy
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id: ioService.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

/**
 * ioService class is the plug-in service of handling file import/export
 *
 * @package   openbiz.bin.service
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class ioService
{
    /**
     * Initialize ioService with xml array metadata
     *
     * @param array $xmlArr
     * @return void
     */
    public function __construct(&$xmlArr)
    {
    }

    /**
     * Export to XML format (print to browser)
     *
     * @param string $objName
     * @return void
     */
    public function exportXML($objName)
    {
        // get the current UI bizobj
        /* @var $bizForm EasyForm */
        $bizForm = BizSystem::getObject($objName);    // get the existing bizform object
        $bizObj = $bizForm->getDataObj();

        $recList = array();
        $bizObj->fetchRecords("", $recList, -1, -1, false);

        $xmlString = "<?xml version='1.0' standalone='yes'?>\n";
        $xmlString .= "<BizDataObj Name=\"".$bizObj->m_Name."\">\n";
        foreach ($recList as $rec)
        {
            $xmlRecord = "\t<Record>\n";
            foreach ($rec as $fld=>$val)
            {
                $xmlRecord .= "\t\t<Field Name=\"$fld\" Value=\"$val\" />\n";
            }
            $xmlRecord .= "\t</Record>\n";
            $xmlString .= $xmlRecord;
        }
        $xmlString .= "</BizDataObj>";

        // output variables
        $name = str_replace(".","_",$bizObj->m_Name).".xml";
        $size = strlen($xmlString);
        $type = "text/plain";

        ob_clean();

        header("Cache-Control: ");// leave blank to avoid IE errors
        header("Pragma: ");// leave blank to avoid IE errors
        header("Content-Disposition: attachment; filename=\"$name\"");
        header("Content-length: $size");
        header("Content-type: $type");
        
        echo $xmlString;

        exit;
    }

    /**
     * Import from XML file, the file read from client (uploaded by user)
     *
     * @param string $objName
     * @return void
     */
    public function importXML($objName)
    {
        // read in file from $_FILE
        // read in file data and attributes
        foreach ($_FILES as $file)
        {
            $error = $file['error'];
            if ($error != 0)
            {
                $this->reportError($error);
                return;
            }

            $tmpName  = $file['tmp_name'];
            $xml = simplexml_load_file($tmpName);
            if (!$xml)
            {
                $errorMsg = "Invalid input data format, could not create xml object.";
                BizSystem::clientProxy()->showErrorMessage($errorMsg);
                return;
            }
            // only read the first one
            break;
        }

        // get the current UI bizobj
        /* @var $form EasyForm */
        $form = BizSystem::getObject($objName);    // get the existing bizform object
        /* @var $parentForm EasyForm */
        $parentForm = BizSystem::getObject($form->GetParentForm());
        /* @var $dataObj BizDataObj */
        $dataObj = $parentForm->getDataObj();

        //$oldCacheMode = $dataObj->GetCacheMode();
        //$dataObj->SetCacheMode(0);    // turn off cache mode, not affect the current cache

        // check if BizDataObj name matches
        $dataObjName = $xml['Name'];
        if ($dataObj->m_Name != $$dataObjName)
        {
            $errorMsg = "Invalid input data. Input data object is not same as the current data object.";
            BizSystem::clientProxy()->showErrorMessage($errorMsg);
            return;
        }

        // read records
        foreach ($xml->Record as $record)
        {
            // insert record
            // todo: check if there's same user keys in the table
            $recArray = null;
            $recArray = $dataObj->newRecord();
            foreach ($record as $field)
            {
                $value = "";
                foreach ($field->attributes() as $attributeName=>$attributeValue)
                {
                    if ($attributeName == 'Name') $name = $attributeValue."";
                    else if ($attributeName == 'Value') $value = $attributeValue."";
                }
                if ($name != "Id")
                    $recArray[$name] = $value;
            }

            if (!$dataObj->insertRecord($recArray))
            {
                $errorMsg = $dataObj->getErrorMessage();
                BizSystem::clientProxy()->showErrorMessage($errorMsg);
                return;
            }
        }
        // $dataObj->SetCacheMode($oldCacheMode);  // restore cache mode

        $form->setFormState(1); // indicate the import is done
    }

    /**
     * Report Error
     *
     * @param number $error Error code
     * @return void
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

        global $g_BizSystem;
        BizSystem::clientProxy()->showErrorMessage($errorStr);
    }
}

?>