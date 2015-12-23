<?php 

class ZenXML implements ArrayAccess {
	
	private $tag_name;
	private $child;
	private $cdata;
	private $attributs;
	
	private $isMultivalued;
	private $multipleValue;

	/*
		Normalement, il faut bien �chapper les caract�res, mais pour des raisons de compatibilit� ascendante,
		ZenXML n'�chappe pas les charact�res des cha�nes CDATA !
	*/
	private $escape_cdata;

	public function __construct($tag_name,$cdata = "",$escape_cdata=false){
		$this->tag_name = $tag_name;
		$this->cdata = $cdata;
		$this->child = array();
		$this->attributs = array();
		$this->multipleValue = false;
		$this->escape_cdata = $escape_cdata;
	}
	
	public function set($tag_name,$cdata = false){
		if (is_object($cdata)){
			$this->child[$tag_name] = $cdata;
		} else {
			$this->child[$tag_name] = new ZenXML($tag_name,$cdata);
		}
	}
	
	public function __set($tag_name,$cdata){
		$this->set($tag_name,$cdata);
	}
	
	public function get($tag_name){
		if (empty($this->child[$tag_name])){
			$this->child[$tag_name] = new ZenXML($tag_name);
		}
		return $this->child[$tag_name];
	}
	
	public function __get($tag_name){
		return $this->get($tag_name);
	}
	
	private function getCDATA($data,$escape_special_char = true){
		$data = utf8_encode($data);
		if (! $escape_special_char) {
			return $data;
		}
		return htmlspecialchars($data,ENT_QUOTES,"UTF-8");
	}

	public function getAttrData($data){
		$data = utf8_encode($data);
		return str_replace('"', '&quot;', $data);
	}
		
	private function getAttr(){
		$attr = "";
		foreach ($this->attributs as $name => $value){
			$value = $this->getAttrData($value);
			$attr.=" $name=\"$value\"";
		}
		return $attr;
	}
	
	public function asXML(){
		$xml = "";
		if ($this->isMultivalued){
			
			foreach($this->multipleValue as $node){
				$xml .= $node->asXML();
			}
			return $xml;
		}
		$attr = $this->getAttr();
		$xml = "<{$this->tag_name}$attr>";
		if ($this->cdata) {
			$xml .=  $this->getCDATA($this->cdata,$this->escape_cdata);
		}
		foreach($this->child as $child){
			$xml .= $child->asXML();
		}
		
		$xml .= "</{$this->tag_name}>\n";
		return $xml;
	}
	
	public function offsetExists($offset){
		return isset($this->attributs[$offset]);
	}
	
	public function offsetGet($offset){
		
		if (is_int($offset)){
			if (empty($this->multipleValue[$offset])){
				$this->offsetSet($offset,"");
			}
			return $this->multipleValue[$offset];
		}
		return $this->attributs[$offset];
	}
	
	public function offsetSet($offset,$value){
		if ($offset === null){
			if ( ! $this->multipleValue){
				$offset = 0;
			} else {
				$offset = count($this->multipleValue) + 1;
			}
			
		}
		if (is_int($offset)){
			
			$this->isMultivalued = true;
			if (is_object($value)){
				$node = $value;
			} else {
				$node = new ZenXML($this->tag_name,$value);
			}
			$this->multipleValue[$offset] = $node;
			return $node;
		} else {
			$this->attributs[$offset] = $value;
		}
	}
	
	public function offsetUnset($offset){
		unset($this->attributs[$offset]);
	}
}
