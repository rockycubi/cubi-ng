<?php /* Smarty version 2.6.10, created on 2014-06-04 14:45:18
         compiled from C:%5Cxampp%5Chtdocs%5Ccubing%5Ccubi%5Cthemes/default/template/system_right_panel.tpl.html */ ?>
	<!-- right block start -->
	<div class="content_block">
		<div class="header"></div>
		<div class="content">	
			<div>							
			<?php $_from = $this->_tpl_vars['forms']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['formname'] => $this->_tpl_vars['form']):
?>							    
		         <div>
		         	<?php if (! preg_match ( "/LeftWidget$/si" , $this->_tpl_vars['formname'] )): ?>
		         	<?php echo $this->_tpl_vars['form']; ?>

					<?php endif; ?>
		         </div>
		    <?php endforeach; endif; unset($_from); ?>		
			</div>
		</div>
		<div class="footer"></div>														
	</div>
	<!-- right block end -->