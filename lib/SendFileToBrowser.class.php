<?php

class SendFileToBrowser {

    public function send($filepath,$filename = ""){
        if (! $filename) {
            $filename = basename($filepath);
        }

        header_wrapper("Content-type: ".mime_content_type($filepath));
        header_wrapper("Content-disposition: attachment; filename=\"".urlencode($filename)."\"");
        header_wrapper("Expires: 0");
        header_wrapper("Cache-Control: must-revalidate, post-check=0,pre-check=0");
        header_wrapper("Pragma: public");

        readfile($filepath);
    }

}