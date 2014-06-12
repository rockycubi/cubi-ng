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
 * @version   $Id: pdfService.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

/**
 * pdfService class is the plug-in service of printing bizform to pdf
 *
 * @package   openbiz.bin.service
 * @author    Rocky Swen
 * @copyright Copyright (c) 2003-2009, Rocky Swen
 * @access    public
 */
class pdfService
{
    /**
     * Initialize pdfService
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Render the pdf output
     *
     * @global BizSystem $g_BizSystem
     * @param string $viewName name of view object
     * @return void
     */
    public function renderView($viewName)
    {
        $viewObj = BizSystem::getObject($viewName);
        if($viewObj)
        {
            $viewObj->setConsoleOutput(false);
            $sHTML = $viewObj->render();
            //$sHTML = "Test";
            //require_once("dompdf/dompdf_config.inc.php");
            $domPdf = new DOMPDF();
            $domPdf->load_html($sHTML);
            //$dompdf->set_paper($_POST["paper"], $_POST["orientation"]);
            $domPdf->render();
            $this->output($domPdf);
            //$dompdf->stream("dompdf_out.pdf");
        }
    }


    /**
     * Save PDF to file and inform download link to client
     *
     * @param DOMPDF $domPdf
     */
    public function output($domPdf)
    {
        //$tmpfile = getcwd()."/tmpfiles";
        $tmpDir = APP_HOME."/tmpfiles";
        //echo $tmpfile;
        $this->cleanFiles($tmpDir, 100);
        //Determine a temporary file name in the current directory
        $tmpFile = tempnam($tmpDir,'tmp');
        $fileName = $tmpFile.'.pdf';
        $fileName = str_replace("\\","/",$fileName);
        unlink($tmpFile);
        //Save PDF to file
        $pdfText = $domPdf->output();
        $fileHandle = fopen($fileName, 'w') or die("can't open pdf file to write");
        fwrite($fileHandle, $pdfText) or die("can't write to the pdf file");
        fclose($fileHandle);
        //JavaScript redirection
        $path_parts = pathinfo($fileName);
        $file_download = "tmpfiles/".$path_parts['basename'];
        echo "<HTML><BODY onload=\"window.location.href='../$file_download';\"</BODY></HTML>";
    }

    /**
     * Delete (temporary) files on specified directory
     *
     * @param string $dir filly directori name
     * @param number $seconds
     */
    public function cleanFiles($dir, $seconds)
    {
        $currentTime = time();
        $dirHandle=opendir($dir);
        while($file = readdir($dirHandle))
        {
            if(substr($file,0,3) == 'tmp' && substr($file,-4) == '.pdf')
            {
                $path = $dir.'/'.$file;
                if($currentTime - filemtime($path) > $seconds)
                    unlink($path);
            }
        }
        closedir($dirHandle);
    }
}
?>