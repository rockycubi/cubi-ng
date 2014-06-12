<?php

/**
 * Openbiz Cubi Application Platform
 *
 * LICENSE http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 *
 * @package   cubi.common.form
 * @copyright Copyright (c) 2005-2011, Openbiz Technology LLC
 * @license   http://code.google.com/p/openbiz-cubi/wiki/CubiLicense
 * @link      http://code.google.com/p/openbiz-cubi/
 * @version   $Id: ArrayListForm.php 5327 2013-03-25 05:09:15Z agus.suhartono@gmail.com $
 */
class ArrayListForm extends EasyForm {

    public $m_TotalRecords;

    public function runSearch() {
        //include_once(OPENBIZ_BIN . "/easy/SearchHelper.php");
        $searchRule = "";
        foreach ($this->m_SearchPanel as $element) {
            $searchStr = '';
            if (method_exists($element, "getSearchRule")) {
                $searchStr = $element->getSearchRule();
            } else {
                if (!$element->m_FieldName)
                    continue;

                $value = BizSystem::clientProxy()->getFormInputs($element->m_Name);
                if ($element->m_FuzzySearch == "Y") {
                    $value = "*$value*";
                }
                if ($value != '') {
                    $searchStr = inputValToRule($element->m_FieldName, $value, $this);
                    $values[] = $value;
                }
            }
            if ($searchStr) {
                if ($searchRule == "")
                    $searchRule .= $searchStr;
                else
                    $searchRule .= " AND " . $searchStr;
            }
        }
        $this->m_SearchRule = $searchRule;
        $this->m_SearchRuleBindValues = $values;

        $this->m_RefreshData = true;

        $this->m_CurrentPage = 1;

        BizSystem::log(LOG_DEBUG, "FORMOBJ", $this->m_Name . "::runSearch(), SearchRule=" . $this->m_SearchRule);

        $recArr = $this->readInputRecord();

        $this->m_SearchPanelValues = $recArr;


        $this->runEventLog();
        $this->rerender();
    }

    public function fetchDataSet() {
        $resultRaw = $this->getRecordList();
        if (!is_array($resultRaw)) {
            return array();
        }


        $searchRule = $this->m_SearchRule;

        preg_match_all("/\[(.*?)\]/si", $searchRule, $match);
        $i = 0;
        $searchFilter = array();
        if (is_array($this->m_SearchRuleBindValues)) {
            foreach ($this->m_SearchRuleBindValues as $key => $value) {
                $fieldName = $match[1][$i];
                $fieldValue = $value;
                $i++;
                $searchFilter[$fieldName] = $fieldValue;
            }
        }
        if (count($searchFilter)) {

            foreach ($resultRaw as $record) {
                $testField = false;
                foreach ($searchFilter as $field => $value) {
                    if ($record[$field] != $value) {
                        $testField = true;
                        break;
                    }
                }
                if (!$testField) {
                    $result[] = $record;
                }
            }
        } else {
            $result = $resultRaw;
        }

        //set default selected record
        if (!$this->m_RecordId) {
            $this->m_RecordId = $result[0]["Name"];
        }
        //set paging 
        $this->m_TotalRecords = count($result);

        if ($this->m_Range && $this->m_Range > 0)
            $this->m_TotalPages = ceil($this->m_TotalRecords / $this->m_Range);

        if ($this->m_CurrentPage > $this->m_TotalPages) {
            $this->m_CurrentPage = $this->m_TotalPages;
        }

        if (is_array($result)) {
            $result = array_slice($result, ($this->m_CurrentPage - 1) * $this->m_Range, $this->m_Range);
        }

        return $result;
    }

    public function getRecordList() {
        return array();
    }

}

?>