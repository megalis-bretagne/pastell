<?php

$file_path = __DIR__ . "/vide.pdf";

$id_e = 2;
$id_d = 'YB2EbLK';

/*$post_data = array(
    'field'=>'fichier1',
    'file_name'=>'vide.pdf',
    'file_content'=> file_get_contents($file_path),
    'id_d'=>'C65p6sz',
    'id_e'=>'2',
);*/

$curl = curl_init();
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_URL, "https://192.168.1.11:8443/api/v2/entite/$id_e/document/$id_d/file/fichier1/0");
curl_setopt($curl, CURLOPT_USERPWD, "admin:admin");
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, [
    'file_name' => 'vide.pdf',
    'file_content' => curl_file_create($file_path)
]);
curl_setopt($curl, CURLOPT_VERBOSE, 1);
echo "begin";

$output = curl_exec($curl);

if ($err = curl_error($curl)) {
    echo "Error : " . $err;
}

echo $output . "\n";

/**
 * @param $api_function
 * @param $field
 * @param $filename
 * @param $filepath
 * @return mixed
 * @throws UnrecoverableException
 */
/*private function postFile($api_function,$field,$filename,$filepath){
    $curlWrapper = $this->curlWrapperFactory->getInstance();

    $curlWrapper->httpAuthentication($this->connecteurConfig->get('pastell_login'),$this->connecteurConfig->get('pastell_password'));

    $curlWrapper->addPostData('file_name',$filename);
    $curlWrapper->addPostData('file_content',curl_file_create($filepath));

    $curl_output = $curlWrapper->get($this->connecteurConfig->get('pastell_url')."/api/v2/$api_function");

    $result = json_decode($curl_output,true);
    if (! $result){
        throw new UnrecoverableException("Impossible de comprendre le message de Pastell");
    }
    return $result;

}*/
