<?php

class AsalaeREST extends SAEConnecteur
{
    private $curlWrapperFactory;
    /** @var \Monolog\Logger */
    private $logger;

    private $url;
    private $login;
    private $password;
    private $originatingAgency;
    private $chunk_size_in_bytes;

    /** @var  DonneesFormulaire */
    private $connecteur_config;

    public function __construct(CurlWrapperFactory $curlWrapperFactory, \Monolog\Logger $logger)
    {
        $this->curlWrapperFactory = $curlWrapperFactory;
        $this->logger = $logger;
    }

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        $this->url = $donneesFormulaire->get('url');
        $this->login = $donneesFormulaire->get('login');
        $this->password = $donneesFormulaire->get('password');
        $this->originatingAgency = $donneesFormulaire->get('originating_agency');
        $this->chunk_size_in_bytes = intval($donneesFormulaire->get('chunk_size_in_bytes'));
        $this->connecteur_config = $donneesFormulaire;
    }

    /**
     * @param $bordereauSEDA
     * @param $archivePath
     * @param string $file_type
     * @param string $archive_file_name
     * @return bool
     * @throws Exception
     */
    public function sendArchive($bordereauSEDA, $archivePath, $file_type = "TARGZ", $archive_file_name = "archive.tar.gz")
    {

        $tmpFile = new TmpFile();
        $bordereau_file = $tmpFile->create();
        file_put_contents($bordereau_file, $bordereauSEDA);

        try {
            if ($this->chunk_size_in_bytes && filesize($archivePath) > $this->chunk_size_in_bytes) {
                $this->sendArchiveByChunk($bordereau_file, $archivePath);
            } else {
                $this->callSedaMessage($bordereau_file, $archivePath);
            }
        } finally {
            $tmpFile->delete($bordereau_file);
        }

        return true;
    }


    /**
     * @param $seda_message_path
     * @param $attachments_path
     * @param bool $send_chunked_attachments
     * @return bool|mixed
     * @throws Exception
     */
    public function callSedaMessage($seda_message_path, $attachments_path, $send_chunked_attachments = false)
    {
        $curlWrapper = $this->curlWrapperFactory->getInstance();
        $curlWrapper->addPostFile('seda_message', $seda_message_path, 'bordereau.xml');
        if ($attachments_path) {
            $curlWrapper->addPostFile('attachments', $attachments_path, basename($attachments_path));
        }
        if ($send_chunked_attachments) {
            $curlWrapper->addPostData('send_chunked_attachments', 'true');
        }
        return $this->getWS('/sedaMessages', "application/json", $curlWrapper);
    }


    /**
     * @param $seda_message_path
     * @param $attachments_path
     * @return bool
     * @throws Exception
     */
    private function sendArchiveByChunk($seda_message_path, $attachments_path)
    {

        $this->logger->debug("Sending seda message by chunk");
        //call seda message
        $seda_message_result = $this->callSedaMessage($seda_message_path, false, true);

        if (empty($seda_message_result['chunk_session_identifier']) || empty($seda_message_result['chunk_security_identifier'])) {
            throw new Exception("Cette version d'as@lae ne permet pas l'envoi d'archive par morceaux");
        }

        $this->logger->debug("Results of /sedaMessage call : ", $seda_message_result);

        $splitFile = new SplitFile($this->logger);
        $chunk_part_list = $splitFile->split($attachments_path, $this->chunk_size_in_bytes, "archive_part");

        foreach ($chunk_part_list as $chunk_index => $chunk_part) {
            $this->sedaAttachmentsChunkFiles(
                $seda_message_result['chunk_session_identifier'],
                $seda_message_result['chunk_security_identifier'],
                basename($attachments_path),
                count($chunk_part_list),
                $chunk_index,
                dirname($attachments_path) . "/" . $chunk_part
            );
        }
        return true;
    }


    /**
     * @param $session_identifier
     * @param $security_identifier
     * @param $filename
     * @param $number_of_chunks
     * @param $chunk_index
     * @param $chunk_file_path
     * @return bool|mixed
     * @throws Exception
     */
    private function sedaAttachmentsChunkFiles($session_identifier, $security_identifier, $filename, $number_of_chunks, $chunk_index, $chunk_file_path)
    {
        $curlWrapper = $this->curlWrapperFactory->getInstance();
        $curlWrapper->addPostFile('chunk_content', $chunk_file_path);

        $post_data = [
            'session_identifier' => $session_identifier,
            'security_identifier' => $security_identifier,
            'number_of_files' => 1,
            'file_index' => 1,
            'file_name' => $filename,
            'compression_algorithm' => 'TARGZ',
            'number_of_chunks' => $number_of_chunks,
            'chunk_index' => $chunk_index + 1,
        //  'chunk_content' => @$chunk_file_path
        ];


        foreach ($post_data as $name => $value) {
            $curlWrapper->addPostData($name, $value);
        }

        $this->logger->debug("Data send with sedaAttachmentsChunkFiles", $post_data);

        return $this->getWS('/sedaAttachmentsChunkFiles', "application/json", $curlWrapper);
    }

    public function getErrorString($number)
    {
        return "Erreur non identifié";
    }

    public function getAck(string $transfert_id, string $originating_agency_id): string
    {
        if (! $transfert_id) {
            throw new UnrecoverableException("L'identifiant du transfert n'a pas été trouvé");
        }
        return $this->getWS(
            "/sedaMessages/sequence:ArchiveTransfer/message:Acknowledgement/originOrganizationIdentification:$originating_agency_id/originMessageIdentifier:"
            . urlencode($transfert_id),
            "application/xml"
        );
    }

    public function getAtr(string $transfert_id, string $originating_agency_id): string
    {
        if (! $transfert_id) {
            throw new UnrecoverableException("L'identifiant du transfert n'a pas été trouvé");
        }

        return $this->getWS(
            sprintf(
                "/sedaMessages/sequence:ArchiveTransfer/message:ArchiveTransferReply/originOrganizationIdentification:%s/originMessageIdentifier:%s",
                $originating_agency_id,
                urlencode($transfert_id)
            ),
            "application/xml"
        );
    }

    public function getURL($cote)
    {
        if (empty($this->url)) {
            return $cote;
        }
        $tab = parse_url($this->url);
        return "{$tab['scheme']}://{$tab['host']}/archives/viewByArchiveIdentifier/$cote";
    }

    /**
     * @param $url
     * @param string $accept
     * @return bool|mixed
     * @throws Exception
     */
    private function getWS($url, $accept = "application/json", CurlWrapper $curlWrapper = null)
    {
        if (! $curlWrapper) {
            $curlWrapper = $this->curlWrapperFactory->getInstance();
        }
        $curlWrapper->httpAuthentication($this->login, hash("sha256", $this->password));

        //see : http://stackoverflow.com/a/19250636
        $curlWrapper->addHeader("Expect", "");
        $curlWrapper->addHeader("Accept", $accept);

        $curlWrapper->dontVerifySSLCACert();
        $result = $curlWrapper->get($this->url . $url);
        if (! $result) {
            throw new Exception($curlWrapper->getLastError());
        }
        $http_code = $curlWrapper->getHTTPCode();
        if ($http_code != 200) {
            throw new Exception("$result - code d'erreur HTTP : $http_code");
        }
        $old_result = $result;
        if ($accept == "application/json") {
            $result = json_decode($result, true);
        }
        if (! $result) {
            throw new Exception(
                "Le serveur As@lae n'a pas renvoyé une réponse compréhensible - problème de configuration ? : $old_result"
            );
        }

        return $result;
    }

    /**
     * @return bool|mixed
     * @throws Exception
     */
    public function getVersion()
    {
        return $this->getWS('/versions');
    }

    /**
     * @return bool|mixed
     * @throws Exception
     */
    public function ping()
    {
        return $this->getWS('/ping');
    }
}
