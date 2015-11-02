<?php

class APCWrapper implements MemoryCache {

	public function store($id,$content,$time = 0){
		apc_store($id,$content,$time);
	}

	public function fetch($id){
		return apc_fetch($id);
	}

	public function delete($id){
		apc_delete($id);
	}

}
