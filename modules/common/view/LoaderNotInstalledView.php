<?php 
class LoaderNotInstalledView extends EasyView
{
	public function render()
	{
		if(!extension_loaded('ionCube Loader'))
		{
			$result = parent::render();
			return $result;
		}else{
			header("Location: ".APP_INDEX);
			exit;
		}
	}
	
}
?>