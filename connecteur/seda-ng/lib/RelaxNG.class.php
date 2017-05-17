<?php

class RelaxNG extends XMLFile {

	const RELAX_NG_NS = "http://relaxng.org/ns/structure/1.0";
	const RELAX_NG_PREFIX = "rng";

	protected function getFromFunction($data, $function) {
		$relax_ng =  parent::getFromFunction($data, $function);
		$relax_ng->registerXPathNamespace(self::RELAX_NG_PREFIX,self::RELAX_NG_NS);
		return $relax_ng;
	}

}