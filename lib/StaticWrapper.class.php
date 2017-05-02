<?php

class StaticWrapper implements MemoryCache {

    static $memory;

	public function store($id,$content,$time = 0){
        self::$memory[$id] = $content;
	}

	public function fetch($id){
		if (isset( self::$memory[$id])){
		    return  self::$memory[$id];
        }
        return false;
	}

	public function delete($id){
	    unset( self::$memory[$id]);
	}

}
