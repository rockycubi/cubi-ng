{php}
$js_url = $this->_tpl_vars['js_url'];
$theme_js_url = $this->_tpl_vars['theme_js_url'];
$css_url = $this->_tpl_vars['css_url'];

$includedScripts = BizSystem::clientProxy()->getAppendedScripts();
$includedScripts .= "
<script type=\"text/javascript\" src=\"$js_url/cookies.js\"></script>
<script type=\"text/javascript\" src=\"$js_url/general_ui.js\"></script>
<script src=\"$js_url/angular/angular.js\"></script>
<script src=\"$js_url/angular/angular-animate.js\"></script>
<script src=\"$js_url/angular/angular-resource.js\"></script>
<script src=\"$js_url/angular/angular-route.js\"></script>
";
$this->_tpl_vars['scripts'] = $includedScripts;

$appendStyle = BizSystem::clientProxy()->getAppendedStyles();
$appendStyle .= "\n"."
<link rel=\"stylesheet\" href=\"$css_url/general.css\" type=\"text/css\" />
<link rel=\"stylesheet\" href=\"$css_url/system_backend.css\" type=\"text/css\" />
<link rel=\"stylesheet\" href=\"$css_url/system_menu_icons.css\" type=\"text/css\" />
<style>
.detail_form_panel_padding
{
padding-left:10px;
}
</style>
";
$this->_tpl_vars['style_sheets'] = $appendStyle;

$this->assign('template_file', 'system_view.tpl.html');
{/php}
{include file=$template_file}
