<?php /* Smarty version 2.6.10, created on 2014-06-05 14:16:20
         compiled from C:%5Cxampp%5Chtdocs%5Ccubing%5Ccubi%5Cmodules/menu/template/system_dashboard.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 't', 'C:\\xampp\\htdocs\\cubing\\cubi\\modules/menu/template/system_dashboard.tpl.html', 4, false),)), $this); ?>
<form id="<?php echo $this->_tpl_vars['form']['name']; ?>
" name="<?php echo $this->_tpl_vars['form']['name']; ?>
">
<div style="padding-left:25px; padding-right:20px;" class="dashboard_panel">
	<div class="dashboard_bg_light_header">
		<div class="title"><?php $this->_tag_stack[] = array('t', array()); smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat=true);while ($_block_repeat) { ob_start(); ?>User & Group<?php $_block_content = ob_get_contents(); ob_end_clean(); echo smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat=false); }  array_pop($this->_tag_stack); ?></div>
		<div class="icons">
			<?php  if(BizSystem::allowUserAccess('User.Administer_Users')){  ?>
			<a href="<?php echo $this->_tpl_vars['app_index']; ?>
/system/user_list" class="icon_user">
				<img border="0" src="<?php echo $this->_tpl_vars['theme_url']; ?>
/images/spacer.gif" /><?php $this->_tag_stack[] = array('t', array()); smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat=true);while ($_block_repeat) { ob_start(); ?>User<?php $_block_content = ob_get_contents(); ob_end_clean(); echo smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat=false); }  array_pop($this->_tag_stack); ?></a>
			<?php  }  ?>
			
			<?php  if(BizSystem::allowUserAccess('Role.Administer_Roles')){  ?>
			<a href="<?php echo $this->_tpl_vars['app_index']; ?>
/system/role_list" class="icon_role">
				<img border="0" src="<?php echo $this->_tpl_vars['theme_url']; ?>
/images/spacer.gif"  /><?php $this->_tag_stack[] = array('t', array()); smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat=true);while ($_block_repeat) { ob_start(); ?>Role<?php $_block_content = ob_get_contents(); ob_end_clean(); echo smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat=false); }  array_pop($this->_tag_stack); ?></a>
			<?php  }  ?>
				
			<?php  if(BizSystem::allowUserAccess('Group.Administer_Groups')){  ?>
			<a href="<?php echo $this->_tpl_vars['app_index']; ?>
/system/group_list" class="icon_group">
				<img border="0" src="<?php echo $this->_tpl_vars['theme_url']; ?>
/images/spacer.gif" /><?php $this->_tag_stack[] = array('t', array()); smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat=true);while ($_block_repeat) { ob_start(); ?>Group<?php $_block_content = ob_get_contents(); ob_end_clean(); echo smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat=false); }  array_pop($this->_tag_stack); ?></a>
			<?php  }  ?>
		</div>
	</div>
	<div class="dashboard_bg_dark">
		<div class="title"><?php $this->_tag_stack[] = array('t', array()); smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat=true);while ($_block_repeat) { ob_start(); ?>System<?php $_block_content = ob_get_contents(); ob_end_clean(); echo smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat=false); }  array_pop($this->_tag_stack); ?></div>
		<div class="icons">
			<?php  if(BizSystem::allowUserAccess('Module.Administer_Modules')){  ?>
			<a href="<?php echo $this->_tpl_vars['app_index']; ?>
/system/module_list" class="icon_module">
				<img border="0" src="<?php echo $this->_tpl_vars['theme_url']; ?>
/images/spacer.gif" /><?php $this->_tag_stack[] = array('t', array()); smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat=true);while ($_block_repeat) { ob_start(); ?>Module<?php $_block_content = ob_get_contents(); ob_end_clean(); echo smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat=false); }  array_pop($this->_tag_stack); ?></a>
			<?php  }  ?>	
						
			<?php  if(BizSystem::allowUserAccess('Menu.Administer_Menu')){  ?>
			<a href="<?php echo $this->_tpl_vars['app_index']; ?>
/menu/menu_list" class="icon_menu_list">
				<img border="0" src="<?php echo $this->_tpl_vars['theme_url']; ?>
/images/spacer.gif" /><?php $this->_tag_stack[] = array('t', array()); smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat=true);while ($_block_repeat) { ob_start(); ?>Menu list<?php $_block_content = ob_get_contents(); ob_end_clean(); echo smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat=false); }  array_pop($this->_tag_stack); ?></a>
			<?php  }  ?>
		</div>
	</div>
</div>
</form>		