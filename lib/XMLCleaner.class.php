<?php
class XMLCleaner {

	public function cleanXML($xml_string){
		$dom = new DOMDocument();
		$dom->loadXML($xml_string);
		$this->cleanDOM($dom);
		return $dom->saveXML();
	}
	
	public function cleanDOM(DomDocument $dom){
		$this->cleanElement($dom->documentElement);
	}
	
	private function cleanElement(DomElement $dom){
		$this->removeEmptyAttributes($dom);
		$this->removeEmptyChilds($dom);
	}
	
	private function removeEmptyAttributes(DomElement $dom){
		foreach($this->getAttributesList($dom) as $attr){
			if (trim($attr->nodeValue) === ''){
				$dom->removeAttribute($attr->nodeName);
			}
		}
	}
	
	private function removeEmptyChilds(DomElement $dom){
		foreach($this->getChildsNode($dom) as $child){
			if ($child->nodeType != XML_ELEMENT_NODE){
				continue;
			}
			$this->cleanElement($child);
			if ( ! $child->hasChildNodes() || trim($child->nodeValue) === '') {
				$dom->removeChild($child);
			}
		}
	}
	
	private function getAttributesList(DomElement $dom){
		$domAttributesList = array();
		foreach($dom->attributes as $attr){
			$domAttributesList[] = $attr;
		}
		return $domAttributesList;
	}
	
	private function getChildsNode(DomElement $dom){
		//Attention : $dom->childNodes est vidé si on supprime un des noeuds !
		$domNodeList = array();
		foreach($dom->childNodes as $child){
			$domNodeList[] = $child;
		}
		return $domNodeList;
	}
	
}