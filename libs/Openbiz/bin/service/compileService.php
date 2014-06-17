<?php 
/*

usage sample:
include_once 'bin/app_init.php';
$svcobj = BizSystem::getObject('service.compileService');
$svcobj->compile("document.do.DocumentDO",'Obj');

 */
class compileService
{
	protected $m_ObjTemplateData ;
	protected $m_XmlTemplateData ;
	
    function __construct(&$xmlArr)
    {
        $this->readMetadata($xmlArr);
    }	

    protected function readMetadata(&$xmlArr)
    {
        $this->m_ObjTemplateData = $xmlArr["PLUGINSERVICE"]["OBJLOADERTEMPLATE"]["VALUE"];        
        $this->m_XmlTemplateData = $xmlArr["PLUGINSERVICE"]["XMLLOADERTEMPLATE"]["VALUE"];
    }
	
    public function compile($objName,$type)
    {
    	switch(strtoupper($type))
    	{
    		case 'XML':
    			$template = $this->m_XmlTemplateData;
    			break;
    		default:
    		case 'OBJ':
    			$template = $this->m_ObjTemplateData;
    			break;
    	}
    	$this->_doCompile($objName, $template);
    }
    
	private function _doCompile($objName,$template)
	{
		$xmlArr = $this->getMetadataArray($objName);
		$xmlArrStr = serialize($xmlArr);
		$objNameNew = str_replace(".","_",$objName);
		$xmlArrCode = $this->encode($xmlArrStr,$objNameNew);		

 		$output = str_replace("{\$className}",$objNameNew,$template);		
		$output = str_replace("{\$metadata}",$xmlArrCode,$output);
		
		$xmlFile = BizSystem::GetXmlFileWithPath ($objName);
		$xmlCmpFile = $xmlFile.".php";
		return file_put_contents($xmlCmpFile, $output);		
	}
	
	public function encode($str,$key){
		return base64_encode(base64_encode($str));
	}
	
	public function decode($str,$key){
		return base64_decode(base64_decode($str));
	}
	
	/*
    public function encode($str,$key) {
        $td = mcrypt_module_open('twofish', '', 'ecb', '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $ks = mcrypt_enc_get_key_size($td);
        $keystr = substr(md5($key), 0, $ks);
        mcrypt_generic_init($td, $keystr, $iv);
        $encrypted = mcrypt_generic($td, $str);
        mcrypt_module_close($td);
        $hexdata = bin2hex($encrypted);
        return $hexdata;
    }
   
    public function decode($str,$key) {
        $td = mcrypt_module_open('twofish', '', 'ecb', '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        $ks = mcrypt_enc_get_key_size($td);
        $keystr = substr(md5($key), 0, $ks);
        mcrypt_generic_init($td, $keystr, $iv);
        $encrypted = pack( "H*", $str);
        $decrypted = mdecrypt_generic($td, $encrypted);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $decrypted;
    }
    */
	
	private function getMetadataArray($objName)
	{
	    
       $xmlFile = BizSystem::GetXmlFileWithPath ($objName);
       if (!$xmlFile)
       {
            $dotPos = strrpos($objName, ".");
            $package = $dotPos>0 ? substr($objName, 0, $dotPos) : null;
            $class = $dotPos>0 ? substr($objName, $dotPos+1) : $objName;
       }
       else
       {
            $xmlArr = BizSystem::getXmlArray($xmlFile);
       }
        
        if ($xmlArr)
        {
            $keys = array_keys($xmlArr);
            $root = $keys[0];

            // add by mr_a_ton , atrubut name must match with object name
            $dotPos = strrpos($objName, ".");
            $shortObjectName  = $dotPos > 0 ? substr($objName, $dotPos+1) : $objName;
            if ($xmlArr[$root]["ATTRIBUTES"]["NAME"]=="")
            {
                $xmlArr[$root]["ATTRIBUTES"]["NAME"]=$shortObjectName;
            }
            else
            {
                if ($shortObjectName != $xmlArr[$root]["ATTRIBUTES"]["NAME"] )
                {
                    trigger_error("Metadata file parsing error for object $objName. Name attribut [".$xmlArr[$root]["ATTRIBUTES"]["NAME"]."] not same with object name. Please double check your metadata xml file again.", E_USER_ERROR);
                }
            }

            //$package = $xmlArr[$root]["ATTRIBUTES"]["PACKAGE"];
            $class = $xmlArr[$root]["ATTRIBUTES"]["CLASS"];
            // if class has package name as prefix, change the package to the prefix
            $dotPos = strrpos($class, ".");
            $classPrefix = $dotPos>0 ? substr($class, 0, $dotPos) : null;
            $classPackage = $classPrefix ? $classPrefix : null;
            if ($classPrefix) $class = substr($class, $dotPos+1);
            // set object package
            $dotPos = strrpos($objName, ".");
            $package = $dotPos>0 ? substr($objName, 0, $dotPos) : null;
            if (!$classPackage) $classPackage = $package;
            $xmlArr[$root]["ATTRIBUTES"]["PACKAGE"] = $package;
            return $xmlArr;
        }	
		return ;
       
	}
}
?>