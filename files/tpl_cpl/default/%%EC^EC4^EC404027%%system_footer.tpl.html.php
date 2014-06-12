<?php /* Smarty version 2.6.10, created on 2014-06-04 14:45:18
         compiled from system_footer.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('block', 't', 'system_footer.tpl.html', 10, false),)), $this); ?>
<?php if(preg_match('/iPad/si',$_SERVER['HTTP_USER_AGENT']) || preg_match('/iPhone/si',$_SERVER['HTTP_USER_AGENT'])){  ?>
	<div id="footer" style="height:64px;">
<?php }else{ ?>
	<div id="footer">
<?php } ?>	
		<div style="float:left; width:460px; height:60px;"></div>
		<div style="float:left; width:380px; height:60px;">
		<div style="padding-top:18px;"></div>
		<p>
		<a href="javascript:"><?php $this->_tag_stack[] = array('t', array()); smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat=true);while ($_block_repeat) { ob_start(); ?>Privacy Statement<?php $_block_content = ob_get_contents(); ob_end_clean(); echo smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat=false); }  array_pop($this->_tag_stack); ?></a> | 
		<a href="javascript:"><?php $this->_tag_stack[] = array('t', array()); smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat=true);while ($_block_repeat) { ob_start(); ?>Terms of Use<?php $_block_content = ob_get_contents(); ob_end_clean(); echo smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat=false); }  array_pop($this->_tag_stack); ?></a> | 
		<a href="javascript:"><?php $this->_tag_stack[] = array('t', array()); smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat=true);while ($_block_repeat) { ob_start(); ?>Trademarks<?php $_block_content = ob_get_contents(); ob_end_clean(); echo smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat=false); }  array_pop($this->_tag_stack); ?></a> | 
		<a href="javascript:"><?php $this->_tag_stack[] = array('t', array()); smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat=true);while ($_block_repeat) { ob_start(); ?>Contact Us<?php $_block_content = ob_get_contents(); ob_end_clean(); echo smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat=false); }  array_pop($this->_tag_stack); ?></a><br />
		<span><?php $this->_tag_stack[] = array('t', array()); smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat=true);while ($_block_repeat) { ob_start(); ?>Copyright 2003-2010 Openbiz LLC All Rights Reserved.<?php $_block_content = ob_get_contents(); ob_end_clean(); echo smarty_block_t($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat=false); }  array_pop($this->_tag_stack); ?></span>
		</p>			
	</div>	