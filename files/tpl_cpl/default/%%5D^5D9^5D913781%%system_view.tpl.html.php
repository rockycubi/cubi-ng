<?php /* Smarty version 2.6.10, created on 2014-06-05 22:00:18
         compiled from system_view.tpl.html */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $this->_tpl_vars['title']; ?>
</title>
<meta name="description" content="<?php echo $this->_tpl_vars['description']; ?>
"/>
<meta name="keywords" content="<?php echo $this->_tpl_vars['keywords']; ?>
"/>
<?php echo $this->_tpl_vars['style_sheets']; ?>

<?php echo $this->_tpl_vars['scripts']; ?>

</head>

<body ng-app="cubiViewApp">

<script>
var APP_INDEX = '<?php echo $this->_tpl_vars['app_index']; ?>
';
var APPMENU_URL = '<?php echo $this->_tpl_vars['appmenu_url']; ?>
';
var APPMENUS;
<?php echo '
var cubiViewApp = angular.module(\'cubiViewApp\',[\'ngResource\',\'ngRoute\',\'Openbiz.services\']);
cubiViewApp.config([\'$routeProvider\', \'$locationProvider\',
	function($routeProvider, $locationProvider) {
	  $routeProvider
		.when(APP_INDEX+\'/f/:module/:form/:id\', {
		  templateUrl: function(params){ return APP_INDEX+\'/f/\'+params.module+\'/\'+params.form+\'/\'+params.id+\'?partial=1\'; }
		})
		.when(APP_INDEX+\'/:module/:view/:id\', {
		  templateUrl: function(params){ return APP_INDEX+\'/\'+params.module+\'/\'+params.view+\'/\'+params.id+\'?partial=1\'; }
		})
		.when(APP_INDEX+\'/:module/:view\', {
		  templateUrl: function(params){ return APP_INDEX+\'/\'+params.module+\'/\'+params.view+\'/?partial=1\'; }
		});
		//.otherwise({ redirectTo: \'\' });;

	  // configure html5 to get links working on jsfiddle
	  $locationProvider.html5Mode(true);
	}]);
'; ?>

</script>

<div align="center" id="body_warp">
	<div id="header_warp">
	<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'system_header.tpl.html', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
	</div>
	<!--main warp-->
	<div id="main_warp">	
		<!--main-->
		<div id="main" >
			<table id="main_content" border="0" cellpadding="0" cellspacing="0">
				<tr><td><img src="<?php echo $this->_tpl_vars['image_url']; ?>
/spacer.gif" style="height:15px;" /></td></tr>
				<tr>
					<td valign="top" style="width:18px;"><img src="<?php echo $this->_tpl_vars['image_url']; ?>
/spacer.gif" style="width:18px;" /></td>
					<td valign="top" id="left_panel">
						<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'system_left_panel.tpl.html', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
					</td>
					<td valign="top" id="right_panel">
						<!-- right block start -->
						<div ng-view></div>
						<!-- right block end -->
					</td>
				</tr>
			  </table>		  
			</div>
			<!--main-->
		
		</div>		
		<!--main wrap end-->
		<!--footer-->
		<div  id="footer_warp">			
		<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'system_footer.tpl.html', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
		</div>
		<!-- footer end -->
	
	</div>
</div>


<?php if(preg_match('/iPad/si',$_SERVER['HTTP_USER_AGENT']) || preg_match('/iPhone/si',$_SERVER['HTTP_USER_AGENT'])){  ?>
<script src="<?php echo $this->_tpl_vars['js_url']; ?>
/ios_webapp.js"></script>
<?php } ?>
</body>
</html>