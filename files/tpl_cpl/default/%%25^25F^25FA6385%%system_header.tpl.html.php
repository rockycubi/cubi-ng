<?php /* Smarty version 2.6.10, created on 2014-06-05 22:00:46
         compiled from system_header.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 't', 'system_header.tpl.html', 31, false),)), $this); ?>
<?php 
// get the breadcrumb list
$menuSvc = Bizsystem::GetObject('menu.lib.MenuService');
$breadCrumb = $menuSvc->getBreadCrumb();
$this->assign('breadCrumb', $breadCrumb);

$header_background_image = $this->_tpl_vars['header_background_image'];
$custom_header_background_image = APP_HOME.'/images/cubi_top_header.png';
if(file_exists($custom_header_background_image))
{
	$header_background_image_url = APP_URL.'/images/cubi_top_header.png';
	$this->assign('header_background_image_url', $header_background_image_url);
}
elseif($header_background_image)
{
	$header_background_image_url = RESOURCE_URL.$header_background_image;
	$this->assign('header_background_image_url', $header_background_image_url);
}
 ?>
<script>
var breadCrumb = <?php  echo json_encode($breadCrumb);  ?>;
</script>
<div id="header_bg">
	<div id="header" <?php if ($this->_tpl_vars['header_background_image_url'] != ''): ?> style="background-image:url(<?php echo $this->_tpl_vars['header_background_image_url']; ?>
);" <?php endif; ?>>
		<div id="header_left"></div>
		<div id="header_right">
			<div id="user_actions">
				<div style="height:18px;"></div>
				<!-- user actions start -->
				<ul>
					<li><a class="icon_home" href="<?php echo $this->_tpl_vars['app_index']; ?>
/system/apps" target="_self"><?php $this->_tag_stack[] = array('t', array()); smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat=true);while ($_block_repeat) { ob_start(); ?>Home<?php $_block_content = ob_get_contents(); ob_end_clean(); echo smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat=false); }  array_pop($this->_tag_stack); ?></a></li>
					<li><a class="icon_setting" href="<?php echo $this->_tpl_vars['app_index']; ?>
/system/general_default" target="_self"><?php $this->_tag_stack[] = array('t', array()); smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat=true);while ($_block_repeat) { ob_start(); ?>Administration<?php $_block_content = ob_get_contents(); ob_end_clean(); echo smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat=false); }  array_pop($this->_tag_stack); ?></a></li>
					<li><a class="icon_myaccount" href="<?php echo $this->_tpl_vars['app_index']; ?>
/myaccount/my_profile" target="_self"><?php $this->_tag_stack[] = array('t', array()); smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat=true);while ($_block_repeat) { ob_start(); ?>My Account<?php $_block_content = ob_get_contents(); ob_end_clean(); echo smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat=false); }  array_pop($this->_tag_stack); ?></a></li>
					<!--<li><a class="icon_help" href="http://www.openbiz.me/web/product_cubi" target="_blank" ><?php $this->_tag_stack[] = array('t', array()); smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat=true);while ($_block_repeat) { ob_start(); ?>Help<?php $_block_content = ob_get_contents(); ob_end_clean(); echo smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat=false); }  array_pop($this->_tag_stack); ?></a></li>-->
					<li><a class="icon_logout" href="<?php echo $this->_tpl_vars['app_index']; ?>
/user/logout" target="_self"><?php $this->_tag_stack[] = array('t', array()); smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat=true);while ($_block_repeat) { ob_start(); ?>Logout<?php $_block_content = ob_get_contents(); ob_end_clean(); echo smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat=false); }  array_pop($this->_tag_stack); ?></a></li>												
				</ul>
				<!-- user actions end -->					
			</div>
		</div>
	</div>	
</div>	