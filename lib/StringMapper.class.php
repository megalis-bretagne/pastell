<?php

class StringMapper {

	private $strings_map = [];

	public function setMapping(array $strings_map){
		$this->strings_map = $strings_map;
	}

	public function get($string){
		return $this->strings_map[$string]??$string;
	}

}