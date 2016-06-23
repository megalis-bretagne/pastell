<?php
class MockFile {
    
	static private $data;
	
	private $position;
    	
    function stream_open($path, $mode, $options, &$opened_path) {
        $this->position = 0;
    	return true;
    }

    function stream_read($count){
    	$ret = mb_substr(self::$data,$this->position,$count);
    	$this->position += mb_strlen($ret);
    	return $ret;	
    }

    function stream_write($data) {
    	self::$data .= $data; 
		return mb_strlen($data);
    }
    
 	function stream_eof() {
        return $this->position >= mb_strlen(self::$data);
    }
    
    function stream_stat(){
    	//see http://www.php.net/manual/fr/function.stat.php
    	return array(7 => mb_strlen(self::$data));
    }
    
    function url_stat($path, $flags){
 		return $this->stream_stat();   	
    }
}