<?php


class GenerateBordereauSEDA {

	public function generate($bordereau_with_annotation_xml, AnnotationWrapper $annotationWrapper){

		$dom = new DOMDocument();
		$dom->preserveWhiteSpace = false;
		$dom->formatOutput = true;
		$dom->loadXML($bordereau_with_annotation_xml,LIBXML_NSCLEAN);

		$xpath = new DOMXPath($dom);
		$xpath->registerNamespace("pastell", RelaxNgImportAgapeAnnotation::PASTELL_ANNOTATION_NS);

		//STAGE 1 : IF
		$annotation_list = $xpath->query("//pastell:annotation");
		$nodeToRemove = array();
		foreach($annotation_list as $annotation){
			if ($annotationWrapper->getCommand($annotation->nodeValue) == 'if'){
				if (! $annotationWrapper->testIf($annotation->nodeValue)){
					$nodeToRemove[] = $annotation->parentNode;
				} else {
					$annotation->nodeValue = preg_replace("#{{pastell:if:[^}]*}}#","",$annotation->nodeValue);
				}
			}
		}

		/** @var DOMElement $node */
		foreach($nodeToRemove as $node){
			$node->parentNode->removeChild($node);
		}

		//STAGE 2 : REPEAT
		$annotation_list = $xpath->query("//pastell:annotation");
		$nodeToRemove = array();
		$nodeToClone = array();

		foreach($annotation_list as $annotation){
			$nb_repeat = $annotationWrapper->getNbRepeat($annotation->nodeValue);

			if ($nb_repeat === false){
				continue;
			}
			$nodeToClone[] = array($annotation->parentNode,$nb_repeat);
		}
		/** @var DOMElement $node */
		foreach($nodeToRemove as $node){
			$node->parentNode->removeChild($node);
		}

		foreach($nodeToClone as list($node,$nb_repeat)){
			if ($nb_repeat == 0){
				$node->parentNode->removeChild($node);
			}
			for($i=1; $i<$nb_repeat; $i++) {
				$clone = $node->cloneNode(true);
				//$clone = $this->cloneNode($node,$dom);
				$node->parentNode->insertBefore($clone,$node);
			}
		}

		//STAGE 3 : REPLACE
		$annotation_list = $xpath->query("//pastell:annotation");
		$nodeToRemove = array();
		$nodeToReplace = array();
		/** @var DOMElement $annotation */
		foreach($annotation_list as $annotation){

			$annotationReturn = $annotationWrapper->wrap((string) $annotation->nodeValue);

			if ($annotationReturn->type == AnnotationReturn::STRING){
				//Pour une raison que j'ignore, il semble qu'il faille mettre les attributes avant la valeur du node (???)
				foreach ($annotationReturn->node_attributes as $attributeName => $attributeValue){
					$annotation->parentNode->setAttribute($attributeName,$attributeValue);
				}
				$parent = $annotation->parentNode;
				$parent->nodeValue = '';
				$parent->appendChild($dom->createTextNode($annotationReturn->string)) ;

			} elseif ($annotationReturn->type == AnnotationReturn::XML_REPLACE){
				$nodeToReplace[] = array($annotation->parentNode,$annotationReturn->string);
			} elseif ($annotationReturn->type == AnnotationReturn::EMPTY_RETURN) {
				$nodeToRemove[] = $annotation;
			} elseif ($annotationReturn->type == AnnotationReturn::ATTACHMENT_INFO){
				$annotation->parentNode->attributes->getNamedItem('filename')->nodeValue = $annotationReturn->string;
				if ($annotationReturn->data['content-type']) {
					$annotation->parentNode->attributes->getNamedItem('mimeCode')->nodeValue = $annotationReturn->data['content-type'];
				}
				$nodeToRemove[] = $annotation;
			} else {
				throw new Exception("annotationReturn inconnu");
			}
		}
		/** @var DOMElement $node */
		foreach($nodeToRemove as $node){
			if ($node->parentNode) {
				$node->parentNode->removeChild($node);
			}
		}
		foreach($nodeToReplace as $node_info){
			$fragment = $dom->createDocumentFragment();
			$fragment->appendXML($node_info[1]);
			$node = $node_info[0];
			$node->parentNode->replaceChild($fragment,$node);
		}


		//STAGE 4 remove empty node
		$nodeToRemove = array();
		$xpath = new DOMXPath($dom);
		$attribute_list = $xpath->query('//*/@*');

		foreach($attribute_list as $node ) {
			if ($node->nodeValue == ""){
				$nodeToRemove[] = $node;
			}
		}

		foreach($nodeToRemove as $node){
			if ($node->parentNode) {
				/** @var DomAttr $node */
				$node->parentNode->removeAttributeNode($node);
			}
		}

		//STEP 5 nettoyage des noeuds vide
		$xmlCleaningEmptyNode = new XMLCleaningEmptyNode();
		$xmlCleaningEmptyNode->clean($dom);

		$dom->documentElement->setAttribute("xmlns:default",RelaxNgImportAgapeAnnotation::PASTELL_ANNOTATION_NS);
		$xml = $dom->saveXML();

		//OMG : j'ai pas trouvé le moyen de faire ça en XML...
		//http://stackoverflow.com/questions/12920877/how-to-remove-attribute-with-namespace-e-g-xmlnsxsi-using-domelement
		$xml = preg_replace("#xmlns:default=\"http://pastell.adullact-projet.coop/seda-ng/annotation\"#","",$xml);

		return $xml;
	}
}