<?php /* Smarty version 2.6.10, created on 2014-06-10 21:59:13
         compiled from C:%5Cxampp%5Chtdocs%5Ccubing%5Ccubi%5Cmodules/common/template/accessdenied.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 't', 'C:\\xampp\\htdocs\\cubing\\cubi\\modules/common/template/accessdenied.tpl.html', 6, false),)), $this); ?>
<form id="<?php echo $this->_tpl_vars['form']['name']; ?>
" name="<?php echo $this->_tpl_vars['form']['name']; ?>
">
<div class="register-block access-denied" >
	
	<div class="form-title"><h1><?php echo $this->_tpl_vars['form']['title']; ?>
</h1></div>
	<p class="input_row form-desc-4" >
<?php $this->_tag_stack[] = array('t', array()); smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat=true);while ($_block_repeat) { ob_start(); ?>
Your access has been denied. 
Your currently user role does not have permission to access the requested page. 

Please sign in as the user with proper permissions.
<?php $_block_content = ob_get_contents(); ob_end_clean(); echo smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat=false); }  array_pop($this->_tag_stack); ?>
		</p>
		<div style="height:10px;"></div>
		<p class="input_row">
			
			<?php $_from = $this->_tpl_vars['actionPanel']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['elem']):
?>
				<?php echo $this->_tpl_vars['elem']['element']; ?>

			<?php endforeach; endif; unset($_from); ?>
		</p>
</div>
</form>