<?php

class JSONoutput
{
    public function sendJson(array $result, $pretty_print = true)
    {
        header_wrapper("Content-type: application/json; charset=utf-8");
        $result_json =  $this->getJson($result, $pretty_print);

        if ($result_json === false) {
            $result_error['status'] = 'error';
            $result_error['error-message'] = "Impossible d'encoder le résultat en JSON [code " . json_last_error() . "]: "
                . json_last_error_msg();
            $result_json =  $this->getJson($result_error, $pretty_print);
        }
        echo $result_json;
    }

    public function getJson(array $array, $pretty_print = true)
    {
        return json_encode($array, $pretty_print ? JSON_PRETTY_PRINT : 0);
    }
}
