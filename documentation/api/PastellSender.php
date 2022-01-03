<?php

require_once __DIR__ . "/PastellSenderException.php";

/**
 * Class PastellSender
 * Cette classe permet de communiquer avec Pastell
 */
class PastellSender
{
    private $pastell_url;
    private $login;
    private $password;
    private $id_e;

    /**
     * PastellSender constructor.
     * @param $pastell_url string URL du Pastell (http://pastell.tld/)
     * @param $login
     * @param $password
     * @param $id_e int Identifiant de l'entité Pastell
     */
    public function __construct($pastell_url, $login, $password, $id_e)
    {
        $this->pastell_url = $pastell_url;
        $this->login = $login;
        $this->password = $password;
        $this->id_e = $id_e;
    }

    /**
     * @param string $api_function
     * @param array $post_fields
     * @param string $http_verb
     * @param bool $decode_json
     * @return bool|mixed|string
     * @throws PastellSenderException
     */
    private function postAPI(string $api_function, array $post_fields = [], $http_verb = "GET", $decode_json = true)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt(
            $curl,
            CURLOPT_URL,
            sprintf(
                "%s/api/v2/%s",
                $this->pastell_url,
                $api_function
            )
        );

        /**
         * Attention, en production il convient de valider le certificat reçu
         */
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt(
            $curl,
            CURLOPT_USERPWD,
            sprintf(
                "%s:%s",
                $this->login,
                $this->password
            )
        );
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $http_verb);
        if ($post_fields || $http_verb != 'GET') {
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($http_verb == 'POST') {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $post_fields);
            } else {
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_fields));
            }
        }

        $output = curl_exec($curl);

        $error_message = curl_error($curl);
        if ($error_message) {
            throw new PastellSenderException("Erreur HTTP : $error_message");
        }

        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (! in_array($http_code, [200,201])) {
            $json_content = json_decode($output, true);
            if (! $json_content || empty($json_content['error-message'])) {
                throw new PastellSenderException("Code HTTP $http_code retourné par Pastell");
            }

            throw new PastellSenderException(
                "Erreur retourné par Pastell : {$json_content['error-message']}\nCode HTTP : $http_code"
            );
        }

        curl_close($curl);
        if (! $decode_json) {
            return $output;
        }
        return json_decode($output, true);
    }

    /**
     * @param string $type_dossier
     * @return mixed
     * @throws PastellSenderException
     */
    public function createDocument(string $type_dossier)
    {
        $result = $this->postAPI(sprintf(
            "/Entite/%s/document?type=%s",
            $this->id_e,
            $type_dossier
        ), [], "POST");
        if (empty($result['id_d'])) {
            throw new PastellSenderException("Impossible de trouver l'identifiant du document");
        }
        return $result['id_d'];
    }

    /**
     * @param string $id_d
     * @param array $metadata
     * @return mixed
     * @throws PastellSenderException
     */
    public function modifDocument(string $id_d, array $metadata)
    {
        return $this->postAPI(
            sprintf(
                "/Entite/%s/document/%s",
                $this->id_e,
                $id_d
            ),
            $metadata,
            "PATCH"
        );
    }

    /**
     * @param string $id_d
     * @param string $field
     * @param array $metadata
     * @return bool|mixed|string
     * @throws PastellSenderException
     */
    public function modifExternalData(string $id_d, string $field, array $metadata)
    {
        return $this->postAPI(
            sprintf(
                "/Entite/%s/document/%s/externalData/%s",
                $this->id_e,
                $id_d,
                $field
            ),
            $metadata,
            "PATCH"
        );
    }

    /**
     * @param string $id_d
     * @param $element_id
     * @param $filepath
     * @param int $file_index
     * @return mixed
     * @throws PastellSenderException
     */
    public function sendFile(string $id_d, $element_id, $filepath, $file_index = 0)
    {
        return $this->postAPI(
            "/Entite/{$this->id_e}/document/$id_d/file/$element_id/$file_index",
            [
                'file_name' => basename($filepath),
                'file_content' => curl_file_create($filepath)
            ],
            "POST"
        );
    }

    /**
     * @param string $id_d
     * @param $action_id
     * @return mixed
     * @throws PastellSenderException
     */
    public function actionOnDossier(string $id_d, $action_id)
    {
        return $this->postAPI("/Entite/{$this->id_e}/document/$id_d/action/$action_id", [], "POST");
    }

    /**
     * @param $id_d
     * @return mixed
     * @throws PastellSenderException
     */
    public function getInfo($id_d)
    {
        return $this->postAPI("/Entite/{$this->id_e}/document/$id_d");
    }

    /**
     * @param $id_d
     * @param $field_name
     * @param $field_index
     * @return mixed
     * @throws PastellSenderException
     */
    public function getFileContent($id_d, $field_name, $field_index)
    {
        return $this->postAPI(
            "/Entite/{$this->id_e}/document/$id_d/file/$field_name/$field_index",
            [],
            "GET",
            false
        );
    }
}
