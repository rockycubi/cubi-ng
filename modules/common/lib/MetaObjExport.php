<?php
class MetaObjExport
{
	protected $m_Object;
	protected $m_Doc;
	protected $m_XmlFile;
	protected $m_ObjType;
	protected $m_RelXmlFile;
	protected $comments = "<!--\n #object_type# Object '#object_name#', file path #object_file#. Please change the 'myproj' to your own module name. \n-->\n";
	protected $firstAttrs = array('Name','Class','Description','Title');
	protected $skipAttrs = array('Package','Percent','TotalPages','OrigFunction','FormName','HTMLAttr','BizObjName','Index','DATAFORMAT');
	protected $convertAttrs = array('DataObjName'=>'BizDataObj','MainTable'=>'Table','Range'=>'PageSize');
	
	public function __construct($object)
	{
		$this->m_Object = $object;
	}
	
	public function GetDocDocument()
    {
        if ($this->m_Doc) 
            return $this->m_Doc;
        $this->m_XmlFile = MODULE_PATH."/".str_replace(".","/",$this->m_Object->m_Name).".xml";
		$this->m_RelXmlFile = "cubi/modules/".str_replace(".","/",$this->m_Object->m_Name).".xml";

        //if (!file_exists($this->m_XmlFile)) 
        //   return null;
        $doc = new DomDocument();
        //$ok = $doc->load($this->m_XmlFile);
        //if (!$ok)
        //    return null;
        $this->m_Doc = $doc;
        //$rootElem = $doc->documentElement;
        return $doc;
    }
	
	public function MetaObj2XML()
	{
		if (is_a($this->m_Object, "EasyForm")) {
			$this->m_ObjType = "Form";
			return $this->Form2XML();
		}
		else if (is_a($this->m_Object, "EasyView")) {
			$this->m_ObjType = "View";
			return $this->View2XML();
		}
		else if (is_a($this->m_Object, "BizDataObj")) {
			$this->m_ObjType = "DataObject";
			return $this->DataObj2XML();
		}
	}
	
	public function DataObj2XML()
	{
		$doc = $this->GetDocDocument();
		$docElem = $this->DataObj2XMLElement($this->m_Object);
		$doc->appendChild($docElem);
		$xmlStr = xmlpp($doc->saveXML());
		return $xmlStr;
	}
	
	protected function DataObj2XMLElement($obj, $clz='')
	{
		$doc = $this->GetDocDocument();
		
		$className = get_class($obj);
		$elemName = $className;	// element use class name by default
		$vars = get_object_vars($obj);
		if ($className == "TableJoin") {
			$elemName = "Join";
		}
		if ($className == "BizRecord") {
			$vars = $obj;
			$elemName = "BizFieldList";
		}
		if ($className == "MetaIterator") {
			$vars = $obj;
		}
		if (is_subclass_of($obj, "BizDataObj")) {
			$elemName = "BizDataObj";
		}
		if ($clz!='') {
			$elemName = $clz;
		}
		
		// create an element
        $elem = $doc->createElement($elemName);
		// set input attributes
		$attrList = array();
        foreach ($vars as $name => $value)
        {
			if (is_object($value)) {
				$clz = "";
				if (get_class($value) == "MetaIterator") {
					$clz = str_replace('m_','',$name);
				}
				//echo "get child element of $name, $clz\n";
				$chldElem = $this->DataObj2XMLElement($value, $clz);
				$elem->appendChild($chldElem);
				//echo "-- get child element of $name\n";
			}
			else if (is_array($value)) {
				continue;
			}
			else if ($value!="") {
				//echo "set attr ($name, $value)\n";
				$attrName = str_replace('m_','',$name);
				$attrList[$attrName] = $value;
				if ($elemName == "BizDataObj" && $attrName == "Name") $attrList[$attrName] = $this->getShortName($value);
			}
        }
		$this->setElemAttrs($attrList, $elem);
        return $elem;
	}
	
	public function Form2XML()
	{
		//print_r($this->m_Object);
		$doc = $this->GetDocDocument();
		$docElem = $this->FormObj2XMLElement($this->m_Object);
		$doc->appendChild($docElem);
		//$xmlStr = str_replace(array('#object_type#','#object_name#','#object_file#'),array($this->m_ObjType,$this->m_Object->m_Name,$this->m_RelXmlFile),$this->comments);
		$xmlStr = xmlpp($doc->saveXML());
		return $xmlStr;
	}
	
	protected function FormObj2XMLElement($obj, $clz='')
	{
		$doc = $this->GetDocDocument();
		
		$className = get_class($obj);
		$elemName = $className;	// element use class name by default
		$vars = get_object_vars($obj);
		if ($className == "Panel") {
			$vars = $obj;
		}
		if (is_subclass_of($obj, "Element")) {
			$elemName = "Element";
		}
		if (is_subclass_of($obj, "EasyForm")) {
			$elemName = "EasyForm";
		}
		if ($clz!='') {
			$elemName = $clz;
		}
		
		// create an element
        $elem = $doc->createElement($elemName);
		// set input attributes
		$attrList = array();
        foreach ($vars as $name => $value)
        {
            if (is_object($value)) {
				$clz = "";
				if (get_class($value) == "Panel") {
					$clz = str_replace('m_','',$name);
				}
				if ($name == "m_EventHandlers") {
					foreach ($value as $k1 => $v1) {
						//echo "get child element of $name, $clz\n";
						$chldElem = $this->FormObj2XMLElement($v1, $clz);
						$elem->appendChild($chldElem);
						//echo "-- get child element of $name\n";
					}
				} else {
					//echo "get child element of $name, $clz\n";
					$chldElem = $this->FormObj2XMLElement($value, $clz);
					$elem->appendChild($chldElem);
					//echo "-- get child element of $name\n";
				}
			}
			else if (is_array($value)) {
				continue;
			}
			else if ($value!="") {
				//echo "set attr ($name, $value)\n";
				if ($name == "m_Function") {
					if (preg_match("/\.([a-zA-Z1-9_]+\(.+)/",$value,$matches)) {
						$value = $matches[1];
					}
				}
				$attrName = str_replace('m_','',$name);
				$attrList[$attrName] = $value;
				if ($elemName == "EasyForm" && $attrName == "Name") $attrList[$attrName] = $this->getShortName($value);
				if ($elemName == "EasyForm" && $attrName == "TemplateFile") $attrList[$attrName] = $this->getShortName($value);
			}
        }
		$this->setElemAttrs($attrList, $elem);
        return $elem;
	}
	
	protected function setElemAttrs($attrList, $elem)
	{
		// set attributes with order Name, Class, Description, Title, ...
		foreach ($this->firstAttrs as $attrName) {
			if (isset($attrList[$attrName])) {
				$elem->setAttribute($attrName, $attrList[$attrName]);
			}
		}
		foreach ($attrList as $k=>$v) {
			if (in_array($k, $this->firstAttrs)) continue;
			if (in_array($k, $this->skipAttrs)) continue;
			if (isset($this->convertAttrs[$k])) $k = $this->convertAttrs[$k];
			$elem->setAttribute($k, $v);
		}
	}
	
	protected function getShortName($value)
	{
		if (strpos($value,'.')>0) {
			$parts = explode('.',$value);
			$value = $parts[count($parts)-1];
		}
		return $value;
	}
	
	public function View2XML()
	{
		$doc = $this->GetDocDocument();
		$docElem = $this->ViewObj2XMLElement($this->m_Object);
		$doc->appendChild($docElem);
		//$xmlStr = str_replace(array('#object_type#','#object_name#','#object_file#'),array($this->m_ObjType,$this->m_Object->m_Name,$this->m_RelXmlFile),$this->comments);
		$xmlStr = xmlpp($doc->saveXML());
		return $xmlStr;
	}
	
	protected function ViewObj2XMLElement($obj)
	{
		$doc = $this->GetDocDocument();
		
		$className = get_class($obj);
		$vars = get_object_vars($obj);
		$elemName = $className;	// element use class name by default
		if ($className == "MetaIterator") {
			$vars = $obj;
			$elemName = "FormReferences";
		}
		else if ($className == "FormReference") {
			$elemName = "Reference";
		}
		if (is_subclass_of($obj, "EasyView")) {
			$elemName = "EasyView";
		}
		
		// create an element
        $elem = $doc->createElement($elemName);
		// set input attributes
		$attrList = array();
        foreach ($vars as $name => $value)
        {
            if (is_object($value)) {
				//echo "get child element of $name\n";
				$chldElem = $this->ViewObj2XMLElement($value);
				$elem->appendChild($chldElem);
				//echo "-- get child element of $name\n";
			}
			else if ($value!="") {
				//echo "set attr ($name, $value)\n";
				$attrName = str_replace('m_','',$name);
				$attrList[$attrName] = $value;
				if ($elemName == "EasyView" && $attrName == "Name") $attrList[$attrName] = $this->getShortName($value);
			}
        }
		$this->setElemAttrs($attrList, $elem);
        return $elem;
	}
}

/** Prettifies an XML string into a human-readable and indented work of art 
 *  @param string $xml The XML as a string 
 *  @param boolean $html_output True if the output should be escaped (for use in HTML) 
 *  http://gdatatips.blogspot.com/2008/11/xml-php-pretty-printer.html, Apache 2.0 License.
 */  
function xmlpp($xml, $html_output=false) {  
    $xml_obj = new SimpleXMLElement($xml);  
    $level = 4;  
    $indent = 0; // current indentation level  
    $pretty = array();  
      
    // get an array containing each XML element  
    $xml = explode("\n", preg_replace('/>\s*</', ">\n<", $xml_obj->asXML()));  
  
    // shift off opening XML tag if present  
    if (count($xml) && preg_match('/^<\?\s*xml/', $xml[0])) {  
      $pretty[] = array_shift($xml);  
    }  
  
    foreach ($xml as $el) {  
      if (preg_match('/^<([\w])+[^>\/]*>$/U', $el)) {  
          // opening tag, increase indent  
          $pretty[] = str_repeat(' ', $indent) . $el;  
          $indent += $level;  
      } else {  
        if (preg_match('/^<\/.+>$/', $el)) {              
          $indent -= $level;  // closing tag, decrease indent  
        }  
        if ($indent < 0) {  
          $indent += $level;  
        }  
        $pretty[] = str_repeat(' ', $indent) . $el;  
      }  
    }     
    $xml = implode("\n", $pretty);     
    return ($html_output) ? htmlentities($xml) : $xml;  
} 
?>