<div class="container">

	<div id="step_0_bg" class="step_0_bg"></div>	
	<div id="step_0" class="step_0">      
		<img border="0" src="images/cubi_logo.png" style="padding-bottom:10px;"/> 		
        <div style="margin-left:auto;margin-right:auto;width:184px;">
        <a class="btn_highlight" 
        	onmouseover="btn_start_hover()"
            onmouseout="btn_start_out()" 
        	href="index.php?step=1"><?php echo STR_BTN_START_NOW?><span><?php echo STR_BTN_INSTALL_CUBI?></span></a>
        </div>
	</div>
	<script type="text/javascript">
	if(!Prototype.Browser.IE){
		$('step_0_bg').setOpacity(0.5);
	}
	function btn_start_hover()
	{
		if(!Prototype.Browser.IE){
			$('step_0_bg').fade({from: 0.5, to: 1});
		}
	}
	function btn_start_out()
	{
		if(!Prototype.Browser.IE){
			$('step_0_bg').fade({from: 1, to: 0.5});
		}
	}
	</script>
</div>
