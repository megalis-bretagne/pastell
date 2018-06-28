<?php

class FreeSpace {

	public function getFreeSpace($filesystem){
		$disk_free_space = disk_free_space($filesystem);
		$disk_total_space = disk_total_space($filesystem);
		$disk_use_percent = sprintf(
			"%0.2f",
			($disk_total_space - $disk_free_space) / $disk_total_space * 100
		);
		return [
			'disk_use_space' => $this->human_filesize($disk_total_space-$disk_free_space),
			'disk_total_space' => $this->human_filesize($disk_total_space),
			'disk_use_percent' =>  $disk_use_percent . " %",
			'disk_use_too_big' => $disk_use_percent > 90
		];
	}

	//thanks http://jeffreysambells.com/2012/10/25/human-readable-filesize-php
	private function human_filesize($bytes, $decimals = 2) {
		$size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
		$factor = intval(floor((strlen($bytes) - 1) / 3));
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . " ".@$size[$factor];
	}

}