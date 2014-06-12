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
 * @version   $Id: chartService.php 2553 2010-11-21 08:36:48Z mr_a_ton $
 */

/**
 * chartService class is the plug-in service of printing bizform to chart
 *
 * @package   openbiz.bin.service
 * @author    Rocky Swen
 * @copyright Copyright (c) 2005-2009, Rocky Swen
 * @access    public
 */
class chartService
{
    /**
     * List of plot objects used in drawing group plot and accurated plot
     *
     * @var array
     */
    public $m_PlotList;

    /**
     * Initialize chartService with xml array metadata
     *
     * @param array $xmlArr
     * @return void
     */
    function __construct(&$xmlArr)
    {
    }

    /**
     * Render the chart output
     *
     * @param string $objName object name which is the bizform name
     * @return void
     */
    Public function render($objName)
    {
        // get the value of the control that issues the call
        $chartName = BizSystem::clientProxy()->getFormInputs("__this");

        // get the current UI bizobj
        $formObj = BizSystem::getObject($objName);    // get the existing bizform object
        $bizDataObj = $formObj->getDataObj();

        // get chart config xml file
        $chartXmlFile = BizSystem::GetXmlFileWithPath($objName."_chart");
        $xmlArr = BizSystem::getXmlArray($chartXmlFile);

        ob_clean();
        // get the chart section from config xml file
        foreach($xmlArr["BIZFORM_CHART"]["CHARTLIST"]["CHART"] as $chart)
        {
            if (count($xmlArr["BIZFORM_CHART"]["CHARTLIST"]["CHART"]) == 1)
                $chart = $xmlArr["BIZFORM_CHART"]["CHARTLIST"]["CHART"];
            // try to match the chartName, if no chartName given, always draw the first chart defined in xml file
            if (($chartName && $chart["ATTRIBUTES"]["NAME"] == $chartName) || !$chartName)
            {
                if ($chart["ATTRIBUTES"]["GRAPHTYPE"] == 'XY')
                {
                    $this->xyGraphRender($bizDataObj, $chart);
                    break;
                }
                if ($chart["ATTRIBUTES"]["GRAPHTYPE"] == 'Pie')
                {
                    $this->pieGraphRender($bizDataObj, $chart);
                    break;
                }
            }
        }
    }

    /**
     * Get plot data array
     *
     * @param BizObj $bizObj object reference of bizobj
     * @param array $fields list of bizobj fields
     * @param array $labelField label field of bizobj
     * @return array reference of the array [field][index]
     */
    public function &getPlotData(&$bizObj, $fields, $labelField)
    {
        $oldCacheMode = $bizObj->GetCacheMode();
        $bizObj->SetCacheMode(0);    // turn off cache mode, not affect the current cache
        $bizObj->runSearch(-1);  // don't use page search
        while (1)
        {
            $recArray = $bizObj->GetRecord(1);
            if (!$recArray) break;
            $bizObj->UnformatInputRecArr($recArray);

            foreach($fields as $fld)
                $recMatrix[$fld][] = $recArray[$fld];   // get data without format
            $recMatrix[$labelField][] = $recArray[$labelField];   // get symbol with format
        }
        $bizObj->SetCacheMode($oldCacheMode);
        return $recMatrix;
    }

    /**
     * Draw the XY type graph (can have > 1 plots)
     *
     * @param BizObj $bizObj object reference of bizobj
     * @param array $xmlArr xml array reference
     * @return void
     */
    public function xyGraphRender(&$bizObj, &$xmlArr)
    {
        include_once (JPGRAPH_DIR.'/jpgraph.php');

        $graph = new Graph($xmlArr["ATTRIBUTES"]["WIDTH"],$xmlArr["ATTRIBUTES"]["HEIGHT"],"auto");
        //$graph->img->SetAntiAliasing();
        $graph->SetScale("textlin");
        $graph->yaxis->scale->SetGrace(10);
        list($m1, $m2, $m3, $m4) = explode(',', $xmlArr["ATTRIBUTES"]["MARGIN"]);
        $graph->img->SetMargin($m1, $m2, $m3, $m4);

        // get the data set
        foreach($xmlArr['DATASET']['DATA'] as $dtmp)
        {
            if ($xmlArr['DATASET']['DATA']['ATTRIBUTES'])
                $dtmp = $xmlArr['DATASET']['DATA'];
            $fieldName = $dtmp["ATTRIBUTES"]["FIELD"];
            if ($fieldName)
                $fields[$fieldName] = $fieldName;
        }

        $labelFld = $xmlArr['XAXIS']['ATTRIBUTES']['LABELFIELD'];

        $recArray = &$this->getPlotData($bizObj, $fields, $labelFld);

        $i = 0;
        foreach($xmlArr['DATASET']['DATA'] as $dtmp)
        {
            if ($xmlArr['DATASET']['DATA']['ATTRIBUTES'])
                $dtmp = $xmlArr['DATASET']['DATA'];
            $data = $recArray[$dtmp["ATTRIBUTES"]["FIELD"]];
            $plot = $this->renderXYPlot($data, $dtmp);
            if ($plot)
                $graph->Add($plot);
            $i++;
        }

        // render titles
        $graph->title->Set($xmlArr['TITLE']['ATTRIBUTES']['CAPTION']);
        $this->_drawString($graph->title,$xmlArr['TITLE']['ATTRIBUTES']['FONT'],$xmlArr['TITLE']['ATTRIBUTES']['COLOR']);

        // render xaxis
        $this->_drawAxis($graph->xaxis, $recArray[$labelFld],
                $xmlArr['XAXIS']['ATTRIBUTES']['FONT'], $xmlArr['XAXIS']['ATTRIBUTES']['COLOR'],
                $xmlArr['XAXIS']['ATTRIBUTES']['LABELANGLE'], $xmlArr['XAXIS']['ATTRIBUTES']['TITLE'],
                $xmlArr['XAXIS']['ATTRIBUTES']['TITLEFONT'], $xmlArr['XAXIS']['ATTRIBUTES']['TITLECOLOR'],
                $xmlArr['XAXIS']['ATTRIBUTES']['TITLEMARGIN']);
        // render yaxis
        $this->_drawAxis($graph->yaxis, null,
                $xmlArr['YAXIS']['ATTRIBUTES']['FONT'], $xmlArr['YAXIS']['ATTRIBUTES']['COLOR'],
                $xmlArr['YAXIS']['ATTRIBUTES']['LABELANGLE'], $xmlArr['YAXIS']['ATTRIBUTES']['TITLE'],
                $xmlArr['YAXIS']['ATTRIBUTES']['TITLEFONT'], $xmlArr['YAXIS']['ATTRIBUTES']['TITLECOLOR'],
                $xmlArr['YAXIS']['ATTRIBUTES']['TITLEMARGIN']);

        // render legend
        $this->_drawLegend($graph->legend,$xmlArr['LAGEND']['ATTRIBUTES']['POSITION'],
                $xmlArr['LAGEND']['ATTRIBUTES']['LAYOUT'], $xmlArr['legend']['ATTRIBUTES']['FONT'],
                $xmlArr['LAGEND']['ATTRIBUTES']['COLOR'], $xmlArr['legend']['ATTRIBUTES']['FILLCOLOR']);
        $graph->Stroke();
    }

    /**
     * draw the Pie type graph (can have 1 pie plot)
     *
     * @param BizObj $bizObj object reference of bizobj
     * @param array $xmlArr xml array reference
     * @return void
     */
    public function pieGraphRender(&$bizObj, &$xmlArr)
    {
        include_once (JPGRAPH_DIR.'/jpgraph.php');
        include_once (JPGRAPH_DIR.'/jpgraph_pie.php');
        include_once (JPGRAPH_DIR.'/jpgraph_pie3d.php');

        $graph = new PieGraph($xmlArr["ATTRIBUTES"]["WIDTH"],$xmlArr["ATTRIBUTES"]["HEIGHT"]);
        //$graph->SetAntiAliasing();

        // get the data set - only support one data
        $fields[0] = $xmlArr['DATASET']['DATAPIE']["ATTRIBUTES"]["FIELD"];
        $legendFld = $xmlArr['DATASET']['DATAPIE']["ATTRIBUTES"]["LEGENDFIELD"];

        $recArray = &$this->getPlotData($bizObj, $fields, $legendFld);

        $chartData = $xmlArr['DATASET']['DATAPIE'];
        $plot = $this->renderPiePlot($recArray[$fields[0]], $chartData);
        $plot->SetLegends($recArray[$legendFld]);
        $graph->Add($plot);

        // render titles
        $graph->title->Set($xmlArr['TITLE']['ATTRIBUTES']['CAPTION']);
        $this->_drawString($graph->title,$xmlArr['TITLE']['ATTRIBUTES']['FONT'],$xmlArr['TITLE']['ATTRIBUTES']['COLOR']);

        // render legend
        $this->_drawLegend($graph->legend,$xmlArr['LEGEND']['ATTRIBUTES']['POSITION'],
                $xmlArr['LEGEND']['ATTRIBUTES']['LAYOUT'], $xmlArr['LEGEND']['ATTRIBUTES']['FONT'],
                $xmlArr['LEGEND']['ATTRIBUTES']['COLOR'], $xmlArr['LEGEND']['ATTRIBUTES']['FILLCOLOR']);
        $graph->Stroke();
    }

    /**
     * Draw the XY type plot
     *
     * @param array $data plot data array reference
     * @param array $xmlArr xml array reference
     * @return object refernce XY plot object reference
     */
    public function renderXYPlot(&$data, &$xmlArr)
    {
        $id = $xmlArr['ATTRIBUTES']['ID'];
        $field = $xmlArr['ATTRIBUTES']['FIELD'];
        $chartType = $xmlArr['ATTRIBUTES']['CHARTTYPE'];
        $pointType = $xmlArr['ATTRIBUTES']['POINTTYPE'];
        $weight = $xmlArr['ATTRIBUTES']['WEIGHT'];
        $color = $xmlArr['ATTRIBUTES']['COLOR'];
        $fillColor = $xmlArr['ATTRIBUTES']['FILLCOLOR'];
        $showVal = $xmlArr['ATTRIBUTES']['SHOWVALUE'];
        $legend = $xmlArr['ATTRIBUTES']['LEGENDFIELD'];
        $visible = $xmlArr['ATTRIBUTES']['VISIBLE'];

        if ($chartType == 'Line' or $chartType == 'Bar')
        {
            if ($chartType == 'Line')
            {
                include_once (JPGRAPH_DIR.'/jpgraph_line.php');
                $plot = new LinePlot($data);
                $this->_drawMark($plot->mark,
                        $xmlArr['POINTMARK']['ATTRIBUTES']['TYPE'], $xmlArr['POINTMARK']['ATTRIBUTES']['COLOR'],
                        $xmlArr['POINTMARK']['ATTRIBUTES']['FILLCOLOR'], $xmlArr['POINTMARK']['ATTRIBUTES']['SIZE']);
                $plot->SetBarCenter();
                $plot->SetCenter();
            }
            else if ($chartType == 'Bar')
            {
                include_once (JPGRAPH_DIR.'/jpgraph_bar.php');
                $plot = new BarPlot($data);
                $plot->SetAlign('center');
            }
            if ($color) $plot->SetColor($color);
            if ($fillColor) $plot->SetFillColor($fillColor);
            if ($weight) $plot->SetWeight($weight);
            if ($showVal == 1) $plot->value->Show();
            if ($legend) $plot->SetLegend($legend);
            $this->_drawString($plot->value,$xmlArr['VALUE']['ATTRIBUTES']['FONT'],$xmlArr['VALUE']['ATTRIBUTES']['COLOR']);
        }

        if ($chartType == 'GroupBar' or $chartType == 'AccBar')
        {
            $children = $xmlArr['ATTRIBUTES']['CHILDREN'];
            $childList = explode(",",$children);
            foreach($childList as $child)
            {
                $childPlotList[] = $this->m_PlotList[$child];
            }
            if ($chartType == 'GroupBar')
                $plot = new GroupBarPlot($childPlotList);
            else if ($chartType == 'AccBar')
                $plot = new AccBarPlot($childPlotList);
        }

        $this->m_PlotList[$id] = $plot;

        if ($visible == 1)
            return $plot;
        return null;
    }

    /**
     * chartService::renderPiePlot() - draw the Pie type plot
     *
     * @param array $data plot data array reference
     * @param array $xmlArr xml array reference
     * @return object refernce Pie plot object reference
     */
    public function renderPiePlot(&$data, &$xmlArr)
    {
        $id = $xmlArr['ATTRIBUTES']['ID'];
        $field = $xmlArr['ATTRIBUTES']['FIELD'];
        $chartType = $xmlArr['ATTRIBUTES']['CHARTTYPE'];
        $size = $xmlArr['ATTRIBUTES']['SIZE'];
        $center = $xmlArr['ATTRIBUTES']['CENTER'];
        $height = $xmlArr['ATTRIBUTES']['HEIGHT'];
        $angle = $xmlArr['ATTRIBUTES']['ANGLE'];
        $labelPos = $xmlArr['ATTRIBUTES']['LABELPOS'];
        $legendField = $xmlArr['ATTRIBUTES']['LAGENDFIELD'];

        if ($chartType == "Pie")
        {
            $plot = new PiePlot($data);
            $plot->SetLabelPos($labelPos);
        }
        else if ($chartType == "Pie3D")
        {
            $plot = new PiePlot3D($data);
            $plot->SetHeight($height);
            $plot->SetAngle($angle);
        }
        list($c1, $c2) = explode(',', $center);
        $plot->SetCenter($c1,$c2);
        $plot->SetSize($size);

        $this->_drawString($plot->value,$xmlArr['VALUE']['ATTRIBUTES']['FONT'],$xmlArr['VALUE']['ATTRIBUTES']['COLOR']);

        return $plot;
    }

    /**
     * draw string
     *
     * @param object $g plot object reference
     * @access private
     * @return void
     */
    private function _drawString(&$g, $font=null, $color=null)
    {
        if ($font)
        {
            list($ft,$fs,$size) = explode(",",$font);
            $g->SetFont($this->_getFont($ft),$this->_getFontStyle($fs),$size);
        }
        if ($color) $g->SetColor($color);
    }

    /**
     * Draw legend
     *
     * @param object $g plot object reference
     * @access private
     * @return void
     */
    private function _drawLegend(&$g, $pos, $layout, $font, $color, $fcolor)
    {
        $this->_drawString($g,$font,$color);
        if ($fcolor) $g->SetFillColor($fcolor);
        if ($pos)
        {
            list($x,$y,$hap,$vap) = explode(",",$pos);
            $g->SetPos($x,$y,$hap,$vap);
        }
        if ($layout && $layout == 'HOR')
        {
            $g->SetLayout(LEGEND_HOR);
        }
    }

    /**
     * Draw Axis (legend?)
     *
     * @param object $g plot object reference
     * @access private
     * @return void
     */
    private function _drawAxis(&$g, $labels, $font, $color, $labelAng, $title, $titleFont, $titleColor, $titleMargin)
    {
        $this->_drawString($g,$font, $color);
        if ($title) $g->title->Set($title);
        $this->_drawString($g->title,$titleFont, $titleColor);
        if ($labels) $g->SetTickLabels($labels);
        if ($labelAng) $g->SetLabelAngle($labelAng);
        if ($titleMargin) $g->SetTitleMargin($titleMargin);
    }

    /**
     * Draw mark
     *
     * @param object $g plot object reference
     * @access private
     * @return void
     */
    private function _drawMark(&$g, $type, $color, $fcolor, $size)
    {
        if ($type) $g->SetType($this->_getMark($type));
        if ($color) $g->SetColor($color);
        if ($fcolor) $g->SetFillColor($fcolor);
        if ($size) $g->SetSize($size);
    }

    /**
     * Get the point make number
     *
     * @param string $mark point mark string
     * @access private
     * @return integer mark number
     */
    private function _getMark($mark)
    {
        switch (strtoupper($mark))
        {
            case "SQUARE": return MARK_SQUARE;
            case "UTRIANGLE": return MARK_UTRIANGLE;
            case "DTRIANGLE": return MARK_DTRIANGLE;
            case "DIAMOND": return MARK_DIAMOND;
            case "CIRCLE": return MARK_CIRCLE;
            case "FILLEDCIRCLE": return MARK_FILLEDCIRCLE;
            case "CROSS": return MARK_CROSS;
            case "STAR": return MARK_STAR;
            case "X": return MARK_X;
            default: return 0;
        }
    }

    /**
     * Get the font number
     *
     * @param string $font font string
     * @access private
     * @return integer font number
     */
    private function _getFont($font)
    {
        switch (strtoupper($font))
        {
            case "ARIAL": return FF_ARIAL;
            case "COURIER;": return FF_COURIER;
            case "TIMES": return FF_TIMES;
            case "VERDANA": return FF_VERDANA;
            case "COMIC": return FF_COMIC;
            case "GEORGIA": return FF_GEORGIA;
            default: return FF_FONT1;
        }
    }

    
    /**
     * Get the font style number
     *
     * @param string $fontStyle font style string
     * @access private
     * @return integer font style number
     */
    private function _getFontStyle($fontStyle)
    {
        switch (strtoupper($fontStyle))
        {
            case "NORMAL": return FS_NORMAL;
            case "BOLD": return FS_BOLD;
            case "ITALIC": return FS_ITALIC;
            case "BOLDITALIC": return FS_BOLDITALIC;
            default: return FS_NORMAL;
        }
    }
}

?>