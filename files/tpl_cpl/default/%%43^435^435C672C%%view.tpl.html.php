<?php /* Smarty version 2.6.10, created on 2014-06-05 11:27:32
         compiled from C:%5Cxampp%5Chtdocs%5Ccubing%5Ccubi%5Cmodules/myaccount/template/view.tpl.html */ ?>
<?php 
$js_url = $this->_tpl_vars['js_url'];
$theme_js_url = $this->_tpl_vars['theme_js_url'];
$css_url = $this->_tpl_vars['css_url'];

$includedScripts = "
<script src=\"$js_url/angular/angular.js\"></script>
<script src=\"$js_url/angular/angular-animate.js\"></script>
<script src=\"$js_url/angular/angular-resource.js\"></script>
<script src=\"$js_url/angular/angular-route.js\"></script>
<script src=\"$js_url/openbiz.js\"></script>
<script src=\"$js_url/jquery/jquery-1.6.2.min.js\"></script>
<script src=\"$js_url/jquery/jquery-ui-1.8.16.custom.min.js\"></script>
";
$this->_tpl_vars['scripts'] = $includedScripts;

$appendStyle = "
<link rel=\"stylesheet\" href=\"$js_url/jquery/css/ui-lightness/jquery-ui-1.8.16.custom.css\" type=\"text/css\" />
<link rel=\"stylesheet\" href=\"$js_url/jquery/css/ui-lightness/jquery-openbiz.css\" type=\"text/css\" />
<link rel=\"stylesheet\" href=\"$css_url/openbiz.css\" type=\"text/css\" />
<link rel=\"stylesheet\" href=\"$css_url/general.css\" type=\"text/css\" />
<link rel=\"stylesheet\" href=\"$css_url/system_backend.css\" type=\"text/css\" />
<link rel=\"stylesheet\" href=\"$css_url/system_menu_icons.css\" type=\"text/css\" />
<link rel=\"stylesheet\" href=\"$css_url/system_dashboard_icons.css\" type=\"text/css\" />
<style>
.detail_form_panel_padding { padding-left:10px;}
</style>
";
$this->_tpl_vars['style_sheets'] = $appendStyle;

$left_menu = "myaccount.widget.MyAccountLeftMenu";
$this->assign('left_menu', $left_menu);
  $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'system_view.tpl.html', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>