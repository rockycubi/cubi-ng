<?php
include_once OPENBIZ_BIN.'/easy/element/Checkbox.php'; 
class LicenseCheckbox extends Checkbox
{
    protected function getText()
    {
    	$text = parent::getText();
    	$appInfo = $this->getFormObj()->getAppInfo();
    	$link = $appInfo['APP_EULA'];
    	$a_open = "<a href=\"$link\" target=\"_blank\">";
    	$a_close = "</a>";
    	$text = str_replace("[url]",$a_open,$text);
    	$text = str_replace("[/url]",$a_close,$text);
    	return $text;
    }
}
?>