<?php /* Smarty version 2.6.10, created on 2014-06-07 22:08:42
         compiled from system_right_detailform.tpl.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'substr', 'system_right_detailform.tpl.html', 65, false),array('modifier', 'count', 'system_right_detailform.tpl.html', 82, false),)), $this); ?>
<form id="<?php echo $this->_tpl_vars['form']['name']; ?>
" name="<?php echo $this->_tpl_vars['form']['name']; ?>
" ng-controller="CFormController" ng-init="init('<?php echo $this->_tpl_vars['form']['name']; ?>
','<?php echo $this->_tpl_vars['form']['dataService']; ?>
','<?php echo $this->_tpl_vars['form']['queryString']; ?>
')">

<div style="padding-left:25px;padding-right:20px;">
	<table><tr><td>
		<?php if ($this->_tpl_vars['form']['icon'] != ''): ?>
		<div class="form_icon"><img  src="<?php echo $this->_tpl_vars['form']['icon']; ?>
" border="0" /></div>
		<?php endif; ?>
	
		<div style="float:left; width:600px;">
			<h2>
			<?php echo $this->_tpl_vars['form']['title']; ?>

			</h2> 
			<?php if ($this->_tpl_vars['form']['description']): ?>
			<p class="input_row" style="line-height:20px;padding-bottom:10px;">		
			<span><?php echo $this->_tpl_vars['form']['description']; ?>
</span>
			</p>
			<?php else: ?>
			<div style="height:15px;"></div>
			<?php endif; ?>
		</div>
	</td></tr></table>
	
	<div class="detail_form_panel_padding">
	<?php $_from = $this->_tpl_vars['dataPanel']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['itemName'] => $this->_tpl_vars['item']):
?>
		<?php if ($this->_tpl_vars['item']['type'] == 'CKEditor' || $this->_tpl_vars['item']['type'] == 'FormElement' || $this->_tpl_vars['item']['type'] == 'RichText' || $this->_tpl_vars['item']['type'] == 'Textarea' || $this->_tpl_vars['item']['type'] == 'RawData' || $this->_tpl_vars['item']['type'] == 'HTMLPreview' || $this->_tpl_vars['item']['type'] == 'LabelImage' || $this->_tpl_vars['item']['type'] == 'IDCardReader'): ?>
			<table  id="<?php echo $this->_tpl_vars['itemName']; ?>
_container" class="input_row">
			<tr>
			<td style="width:80px;">	
				<label style="text-align:left"><?php echo $this->_tpl_vars['item']['label']; ?>
</label>
			</td>
			<td>
				<?php if ($this->_tpl_vars['errors'][$this->_tpl_vars['itemName']]): ?>
				<span class="input_error_msg" style="width:240px;"><?php echo $this->_tpl_vars['errors'][$this->_tpl_vars['itemName']]; ?>
</span>
				<?php elseif ($this->_tpl_vars['item']['description']): ?>
				<span class="input_desc" style="width:240px;"><?php echo $this->_tpl_vars['item']['description']; ?>
</span>			
				<?php endif; ?>
			</td>
			</tr>
			<tr><td colspan="2" align="center" >
				<span class="label_textarea" style="width:655px;"><?php echo $this->_tpl_vars['item']['element']; ?>
</span>
							
			</td></tr>
			</table>		
		<?php else: ?>
			<?php if ($this->_tpl_vars['item']['type'] == 'Hidden'): ?>
			<table  id="<?php echo $this->_tpl_vars['itemName']; ?>
_container" class="input_row" style="display:none">
			<?php else: ?>
			<table  id="<?php echo $this->_tpl_vars['itemName']; ?>
_container" class="input_row">
			<?php endif; ?>					
			<tr>
			<td valign="top">			
				<label style="text-align:left;"><?php echo $this->_tpl_vars['item']['label']; ?>
</label>			
			</td>
			<td>
				<p ng-class="<?php echo '{'; ?>
 'has-error' : errors.<?php echo $this->_tpl_vars['item']['field']; ?>
 <?php echo '}'; ?>
">
			<?php if ($this->_tpl_vars['item']['type'] == 'Checkbox'): ?>
				<span class="label_text" ><?php echo $this->_tpl_vars['item']['element']; ?>
 <?php echo $this->_tpl_vars['item']['description']; ?>
</span>
            <?php elseif (((is_array($_tmp=$this->_tpl_vars['item']['type'])) ? $this->_run_mod_handler('substr', true, $_tmp, 0, 5) : substr($_tmp, 0, 5)) == 'Label'): ?>
                <span><?php echo $this->_tpl_vars['item']['element']; ?>
</span>    
			<?php else: ?>
				<span class="label_text" style="<?php if ($this->_tpl_vars['item']['width']): ?>width:<?php echo $this->_tpl_vars['item']['width']+15; ?>
px;<?php endif; ?>"><?php echo $this->_tpl_vars['item']['element']; ?>
</span>
				<?php if ($this->_tpl_vars['item']['description']): ?>
				<span class="input_desc" style="width:240px;"><?php echo $this->_tpl_vars['item']['description']; ?>
</span>			
				<?php endif; ?>
			<?php endif; ?>
				<div class="input_error_msg" ng-show="errors.<?php echo $this->_tpl_vars['item']['field']; ?>
"><?php echo '{{'; ?>
 errors.<?php echo $this->_tpl_vars['item']['field']; ?>
 <?php echo '}}'; ?>
</div>
				</p>
			</td>
			</tr>
			</table>
		<?php endif; ?>
	<?php endforeach; endif; unset($_from); ?>
		
		<div style="height:10px;"></div>
		<?php if (count($this->_tpl_vars['actionPanel']) > 0): ?>
		<p class="input_row">
			
			<?php $_from = $this->_tpl_vars['actionPanel']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['elem']):
?>
				<?php echo $this->_tpl_vars['elem']['element']; ?>

			<?php endforeach; endif; unset($_from); ?>
		</p>
		<?php endif; ?>

	    <div id='errorsDiv' class='innerError errorBox' ng-show="errorMsg">
	        <div><?php echo '{{'; ?>
errorMsg<?php echo '}}'; ?>
</div>
	    </div>
		
		<div id='noticeDiv' class='noticeBox' ng-show="noticeMsg">
	        <div><?php echo '{{'; ?>
noticeMsg<?php echo '}}'; ?>
</div>
	    </div>

	</div>
	
</div>

</form>