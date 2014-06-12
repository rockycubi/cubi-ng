<div class="container">
	<div class="step_1">	    	    
		<div style="padding-left:260px;padding-top:45px;width:600px;">
			<h2><?php echo STR_SYSTEM_CHECK?></h2>
	        <p>	        
	        <?php echo STR_SYSTEM_CHECK_DESC;?>
	        </p>
	        
	        <table class="form_table" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:20px;width:600px;">
	        <tr>
	            <th><?php echo STR_ITEM;?></th>
	            <th><?php echo STR_VALUE;?></th>
	            <th style="padding-left:5px;padding-right:5px;"><?php echo STR_STATUS;?></th>
	        </tr>
	        <?php
	        $status = getSystemStatus();
	        $i=0;
			$hasError = false;
	        foreach ($status as $s) {
	            if(fmod($i,2)){
	                $default_style="even";
	            }else{
	                $default_style="odd";
	            }
	            
	            if (strpos($s['status'],'OK') === 0) {
	                $flag_icon="flag_y.gif";        
	            }else{
	                $flag_icon="flag_n.gif";
					$hasError = true;
	            }
	             $i++;
	        ?>
	        <tr
	            class="<?php echo $default_style;?>"
	            onmouseover="if(this.className!='selected')this.className='hover'" 
	            onmouseout="if(this.className!='selected')this.className='<?php echo $default_style;?>'" 
	        >
	            <td><?php echo $s['item'];?></td>
	            <td><?php echo $s['value'];?></td>
	            <td><img src="../themes/default/images/<?php echo $flag_icon;?>" /></td>
	        </tr>
	        <?php
	        }
	        ?>
	        </table>
	        
	        <a href="index.php?step=0" class="btn_back"><?php echo STR_BACK;?></a>
	        <?php 
			if (!$hasError){
			?>
	    	    <a href="index.php?step=2" class="btn_next"><?php echo STR_NEXT;?></a>
	        <?php
			}else{
			?>
		        <a href="index.php?step=1" class="btn_next"><?php echo STR_RETRY;?></a>
	        <?php
			}
			?>
	
		</div>
		<div id="error_message" class="popup_dialog" onclick="this.style.display='none';"></div>
	</div>
</div>



