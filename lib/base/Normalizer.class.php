<?php


class Normalizer {
	
	public static function normalize($mot) {
		$name = trim($mot);
		$name = strtolower($name);
		$name = strtr($name," ��������������������������","_aaaaaceeeeiiiinooooouuuuyy");
		$name = preg_replace('/[^\w_]/',"",$name);
		return $name;
	}
	
	
}