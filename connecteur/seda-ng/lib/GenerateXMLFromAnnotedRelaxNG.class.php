<?php
require_once(__DIR__."/GenerateXMLFromRelaxNg.class.php");


class GenerateXMLFromAnnotedRelaxNG extends GenerateXMLFromRelaxNg {

	protected function walkChildren(SimpleXMLElement $element, DOMNode $domNodeResult) {
		parent::walkChildren($element, $domNodeResult);
		foreach($element->children(RelaxNgImportAgapeAnnotation::PASTELL_ANNOTATION_NS) as $child){
			$this->walk($child,$domNodeResult);
		}
	}

	protected function getAnnotationNode(SimpleXMLElement $element,DOMNode $domNodeResult){
		$element_name = (string) $element->getName();
		$element_value = (string) $element;
		$domNode = $this->domDocument->createElementNS(
			RelaxNgImportAgapeAnnotation::PASTELL_ANNOTATION_NS,
			$element_name,
			$element_value
		);
		$domNodeResult->appendChild($domNode);
	}


}