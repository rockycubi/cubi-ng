<?php /* Smarty version 2.6.10, created on 2014-06-10 13:55:13
         compiled from C:%5Cxampp%5Chtdocs%5Ccubing%5Ccubi%5Cthemes/default/template/user_view.tpl.html */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $this->_tpl_vars['title']; ?>
</title>
<?php echo $this->_tpl_vars['style_sheets']; ?>

<?php echo $this->_tpl_vars['scripts']; ?>

<link rel="stylesheet" href="<?php echo $this->_tpl_vars['css_url']; ?>
/general.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['css_url']; ?>
/login.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['css_url']; ?>
/openbiz.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->_tpl_vars['app_url']; ?>
/languages/<?php echo $this->_tpl_vars['lang_name']; ?>
/localization.css" type="text/css" />
<script src="<?php echo $this->_tpl_vars['js_url']; ?>
/angular/angular.js"></script>
</head>

<body ng-app="cubiViewApp">

<script>
var cubiViewApp = angular.module('cubiViewApp',[]);
</script>

<div align="center">
<?php if(preg_match('/iPad/si',$_SERVER['HTTP_USER_AGENT']) ){  ?>
<div id="wrap_header_padding" style="height:80px;"></div>
<?php } ?>
	<div id="wrap">
		<div id="login_box">
				<div id="forms" >
					<div style="height:25px;"></div>
					<?php $_from = $this->_tpl_vars['forms']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['form']):
?>
					   <div><?php echo $this->_tpl_vars['form']; ?>
</div>
					<?php endforeach; endif; unset($_from); ?>
				</div>
		</div>
	</div>
</div>

</body>
</html>