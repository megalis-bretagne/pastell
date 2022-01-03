<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'DocapostParapheurSoapClientException.php';

class DocapostParapheurSoapClient
{
    private $client;

    public function __construct(SoapClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $subscriberNumber
     * @param string $circuit
     * @param string $filename
     * @param string $documentContent
     * @return mixed
     * @throws DocapostParapheurSoapClientException
     * @throws Exception
     */
    public function upload(string $subscriberNumber, string $circuit, string $filename, string $documentContent)
    {
        $data = [
            'subscriberId' => $subscriberNumber,
            'circuitId' => $circuit,
            'label' => '',
            'comment' => '',
            'dataFileVO' => [
                'filename' => $filename,
                'dataHandler' => $documentContent
            ]
        ];
        try {
            $result = $this->client->upload($data);
        } catch (Exception $e) {
            $message = $e->getMessage();

            if (preg_match('/un fichier PES avec le même nomfic a deja ete envoye/', $message)) {
                $message = "Doublon | " . $message;
            }

            throw new Exception($message);
        }

        if (empty($result->return)) {
            throw new DocapostParapheurSoapClientException(
                "Le parapheur n'a pas retourné d'identifiant de document : " . json_encode($result)
            );
        }
        return $result->return;
    }

    /**
     * @param string $documentId
     * @return mixed
     * @throws DocapostParapheurSoapClientException
     */
    public function download(string $documentId)
    {
        $document = $this->client->download(['documentId' => $documentId]);
        if (!$document->return->content) {
            throw new DocapostParapheurSoapClientException("Le document n'a pas pu être téléchargé");
        }
        return $document->return->content;
    }

    /**
     * @param string $documentId
     * @return mixed
     * @throws DocapostParapheurSoapClientException
     */
    public function history(string $documentId)
    {
        $result = $this->client->history(['documentId' => $documentId]);
        if (empty($result->return)) {
            throw new DocapostParapheurSoapClientException("L'historique du document n'a pas été trouvé");
        }

        return $result->return;
    }

    public function listRemainingAcknowledgements(string $subscriberNumber)
    {
        return $this->client->listRemainingAcknowledgements(['siren' => $subscriberNumber]);
    }

    /**
     * @param string $documentId
     * @return mixed
     * @throws DocapostParapheurSoapClientException
     */
    public function downloadAcknowledgement(string $documentId)
    {
        $result = $this->client->downloadAcknowledgement(['documentId' => $documentId]);
        if (empty($result->return->content)) {
            throw new DocapostParapheurSoapClientException("Le PES Acquit n'a pas pu être téléchargé");
        }

        return $result->return->content;
    }

    /**
     * @param string $documentId
     * @return mixed
     * @throws Exception
     */
    public function delete(string $documentId)
    {
        return $this->client->delete(['documentId' => $documentId]);
    }
}
