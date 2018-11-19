<?php

class UtilTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @dataProvider number_format_fr_provider
	 */
	public function test_number_format_fr($number_to_test,$expected_result){
		$this->assertEquals($expected_result,number_format_fr($number_to_test));
	}

	public function number_format_fr_provider(){
		return [
			[0,"0"],
			[42,"42"],
			[24768980,"24 768 980"]
		];
	}

}