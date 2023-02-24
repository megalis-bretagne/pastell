<?php

class SendFileToBrowser
{
    public const CONTENT_DISPOSITION_ATTACHMENT = "attachment";
    public const CONTENT_DISPOSITION_INLINE = "inline";

    public function send($filepath, $filename = "")
    {
        if (!$filename) {
            $filename = basename($filepath);
        }
        $this->sendHeader($filename, mime_content_type($filepath));
        readfile($filepath);
    }

    public function sendData(
        $data_to_send,
        $filename,
        $content_type,
        $content_disposition = self::CONTENT_DISPOSITION_ATTACHMENT
    ) {
        $this->sendHeader($filename, $content_type, $content_disposition);
        echo $data_to_send;
    }

    private function sendHeader($filename, $content_type, $content_disposition = self::CONTENT_DISPOSITION_ATTACHMENT)
    {
        $encodedFileName = rawurlencode($filename);
        $contentDisposition = sprintf(
            "Content-disposition: $content_disposition; filename*=UTF-8''%s; filename=%s",
            $encodedFileName,
            $encodedFileName
        );
        header_wrapper("Content-type: $content_type");
        header_wrapper($contentDisposition);
        header_wrapper("Expires: 0");
        header_wrapper("Cache-Control: must-revalidate, post-check=0,pre-check=0");
        header_wrapper("Pragma: public");
    }
}
