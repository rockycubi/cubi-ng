<div class="container">


	<div class="step_4">
		<div style="padding-left:330px;padding-top:65px;width:500px;">
		
			<table style="margin-bottom:10px;">
				<tr>
					<td style="padding-right:20px;">
						<img border="0" src="images/icon_finished.png" />
					</td>
					<td>
						<h2><?php echo STR_INSTALLATION_COMPLETED;?></h2>
					    <p>
					    <?php echo STR_INSTALLATION_COMPLETED_DESC;?>
					    </p>
					</td>
				</tr>
			</table>
			
		    <table style="margin-bottom:15px;">
				<tr>
					<td style="padding-right:80px;">
						<h4 ><?php echo STR_DEFAULT_LOGIN_INFO;?></h4>
					    <p style="padding-bottom:0px;padding-top:5px;">    
					     <?php echo STR_USERNAME;?>: <strong style="color:#ff0000;">admin</strong><br />
					     <?php echo STR_PASSWORD;?>: <strong style="color:#ff0000;">admin</strong><br />
					    </p>  
					</td>
					<td>
						<a href="../index.php/user/login" class='btn_highlight' style="text-align:center"><?php echo STR_READY_GO;?><span><?php echo STR_LOGIN_TO_OPENBIZ;?></span></a>
					</td>
				</tr>
			</table>
		    
		      
		    <h4 ><?php echo STR_REFERENCE_DOCUMENT;?></h4>
		    
		    <ul class="list">
		    <li><a href="http://www.openbiz.me/" target="_blank">Openbiz Cubi International Website</a></li>
		    <li><a href="http://www.openbiz.cn/" target="_blank">Openbiz Cubi Chinese Website</a></li>
		    <li><a href="http://www.openbiz.web.id/" target="_blank">Openbiz Cubi Indonesia Website</a></li>
		    <li><a href="http://code.google.com/p/openbiz-cubi/" target="_blank">Openbiz Cubi Google Project</a></li>
		    <li><a href="http://code.google.com/p/openbiz-cubi/wiki/CubiCoreConcepts" target="_blank">Openbiz Cubi Reference Guide</a></li>
		    
		    </ul>
		
		
		</div>
	</div>

</div>
<script>//setTimeout("location.href='../index.php/user/login/do'",10000)</script>
<?php
$lockfile =  (dirname(dirname(dirname(__FILE__))).'/files/install.lock');
file_put_contents($lockfile, '1');
?>