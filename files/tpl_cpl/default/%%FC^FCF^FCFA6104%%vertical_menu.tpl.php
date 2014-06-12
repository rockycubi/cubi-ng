<?php /* Smarty version 2.6.10, created on 2014-06-04 14:45:18
         compiled from C:%5Cxampp%5Chtdocs%5Ccubing%5Ccubi%5Cmodules/menu/template/vertical_menu.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 't', 'C:\\xampp\\htdocs\\cubing\\cubi\\modules/menu/template/vertical_menu.tpl', 4, false),)), $this); ?>
<div class="menu_title" >
<h2><?php echo $this->_tpl_vars['widget']['title']; ?>
</h2>
<p style="float:right;display:block;padding-top:4px;">
<span style="display: block;float: left;"><a class="menu_index_link" href="<?php echo $this->_tpl_vars['app_index']; ?>
/system/general_default"><?php $this->_tag_stack[] = array('t', array()); smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat=true);while ($_block_repeat) { ob_start(); ?>Index<?php $_block_content = ob_get_contents(); ob_end_clean(); echo smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat=false); }  array_pop($this->_tag_stack); ?></a></span> 
<a href="<?php echo $this->_tpl_vars['app_index']; ?>
/system/general_default"><img id="system_dashboard" class="btn_dashboard"  src="<?php echo $this->_tpl_vars['image_url']; ?>
/spacer.gif" border="0" /></a></p>
</div>
<div ng-controller="LeftMenuController" ng-init="init('<?php echo $this->_tpl_vars['form']['name']; ?>
','<?php echo $this->_tpl_vars['form']['dataService']; ?>
','<?php echo $this->_tpl_vars['form']['queryString']; ?>
')">
<ul class="toplevel <?php echo $this->_tpl_vars['widget']['css']; ?>
 left_menu">
	<li ng-repeat="node in treeNodes">
		<a ng-click="showSubmenu(node.m_Id)" ng-class="node.m_Current ? 'current':''">
			<img ng-class="node.m_IconCSSClass"/>
			<?php echo '{{'; ?>
node.m_Name<?php echo '}}'; ?>

		</a>	
		<ul class="secondlevel module" style="<?php echo '{{'; ?>
node.m_Current ? 'display:block':''<?php echo '}}'; ?>
">
			<li ng-repeat="subitem in node.m_ChildNodes">
				<a ng-class="subitem.m_Current ? 'current':''" href="<?php echo '{{'; ?>
subitem.m_URL<?php echo '}}'; ?>
">
				<?php echo '{{'; ?>
subitem.m_Name<?php echo '}}'; ?>
</a>
			</li>
		</ul>
	</li>
</ul>
</div>
<div class="v_spacer"></div>