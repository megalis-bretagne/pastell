<?php

class SendFileToBrowser {

    public function send($filepath,$filename = ""){
        if (! $filename) {
            $filename = basename($filepath);
        }
        $this->sendHeader($filename,mime_content_type($filepath));
        readfile($filepath);
    }

    public function sendData($data_to_send,$filename,$content_type){
		$this->sendHeader($filename,$content_type);
		echo $data_to_send;
	}

	private function sendHeader($filename,$content_type){
		header_wrapper("Content-type: $content_type");
		header_wrapper("Content-disposition: attachment; filename=\"".urlencode($filename)."\"");
		header_wrapper("Expires: 0");
		header_wrapper("Cache-Control: must-revalidate, post-check=0,pre-check=0");
		header_wrapper("Pragma: public");
	}

}