{php}
$js_url = $this->_tpl_vars['js_url'];
$theme_js_url = $this->_tpl_vars['theme_js_url'];
$css_url = $this->_tpl_vars['css_url'];

$includedScripts = BizSystem::clientProxy()->getAppendedScripts();
if (JSLIB_BASE!='JQUERY') {
	$includedScripts .= "
	<script src=\"".JS_URL."/jquery.js\"></script>
	<script>try{var \$j=jQuery.noConflict();}catch(e){}</script>
	<script src=\"".JS_URL."/jquery-ui-1.8.12.custom.min.js\"></script>
	";
}
$includedScripts .= "
<script type=\"text/javascript\" src=\"$js_url/cookies.js\"></script>
<script type=\"text/javascript\" src=\"$js_url/general_ui.js\"></script>
<script type='text/javascript' src='$js_url/uploadify/swfobject.js'></script>
<script type='text/javascript' src='$js_url/uploadify/jquery.uploadify.v2.1.4.js'></script>
";

$this->_tpl_vars['scripts'] = $includedScripts;

$appendStyle = BizSystem::clientProxy()->getAppendedStyles();
$appendStyle .= "\n"."
<script type='text/javascript' src='//maps.googleapis.com/maps/api/js?sensor=false'></script>
<link rel=\"stylesheet\" href=\"$css_url/general.css\" type=\"text/css\" />
<link rel=\"stylesheet\" href=\"$css_url/system_backend.css\" type=\"text/css\" />
<link rel=\"stylesheet\" href=\"$css_url/system_menu_icons.css\" type=\"text/css\" />
";
$this->_tpl_vars['style_sheets'] = $appendStyle;

$left_menu = "myaccount.widget.MyAccountLeftMenu";
$this->assign('left_menu', $left_menu);

$this->assign('template_file', 'system_view.tpl.html');
{/php}
{include file=$template_file}