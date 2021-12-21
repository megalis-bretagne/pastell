<?php

class CurlFunctions
{
    public function curl_init()
    {
        return curl_init();
    }

    public function curl_close($curl_handle)
    {
        curl_close($curl_handle);
    }

    public function curl_setopt($curl_handle, $propertie, $value)
    {
        return curl_setopt($curl_handle, $propertie, $value);
    }

    public function curl_exec($curl_handle)
    {
        return curl_exec($curl_handle);
    }

    public function curl_error($curl_handle)
    {
        return curl_error($curl_handle);
    }

    public function curl_getinfo($curl_handle, $option = null)
    {
        return curl_getinfo($curl_handle, $option);
    }
}
