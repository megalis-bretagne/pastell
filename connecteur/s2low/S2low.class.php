<?php

class S2low extends TdtConnecteur
{
    public const URL_TEST = "/api/test-connexion.php";
    public const URL_GET_NOUNCE = "/api/get-nounce.php";
    public const URL_CLASSIFICATION = "/modules/actes/actes_classification_fetch.php";
    public const URL_POST_ACTES =  "/modules/actes/actes_transac_create.php";
    public const URL_STATUS = "/modules/actes/actes_transac_get_status.php";
    public const URL_ANNULATION = "/modules/actes/actes_transac_cancel.php";
    public const URL_BORDEREAU = "/modules/actes/actes_create_pdf.php";
    public const URL_DEMANDE_CLASSIFICATION = "/modules/actes/actes_classification_request.php";
    public const URL_POST_HELIOS = "/modules/helios/api/helios_importer_fichier.php";
    public const URL_STATUS_HELIOS =  "/modules/helios/api/helios_transac_get_status.php";
    public const URL_HELIOS_RETOUR = "/modules/helios/helios_download_acquit.php";
    public const URL_LIST_LOGIN = "/admin/users/api-list-login.php";
    public const URL_ACTES_REPONSE_PREFECTURE =  "/modules/actes/actes_transac_get_document.php";
    public const URL_ACTES_REPONSE_PREFECTURE_LISTE = "/modules/actes/api/list_document_prefecture.php";
    public const URL_ACTES_REPONSE_PREFECTURE_MARK_AS_READ = "/modules/actes/api/document_prefecture_mark_as_read.php";
    public const URL_POST_REPONSE_PREFECTURE = "/modules/actes/actes_transac_reponse_create.php";
    public const URL_ACTES_TAMPONNE = "/modules/actes/actes_transac_get_tampon.php";
    public const URL_POST_CONFIRM = "/modules/actes/actes_transac_post_confirm_api.php";
    public const URL_POST_CONFIRM_MULTI = "/modules/actes/actes_transac_post_confirm_api_multi.php";

    public const URL_HELIOS_PES_RETOUR_LISTE = "/modules/helios/api/helios_get_list.php";
    public const URL_HELIOS_PES_RETOUR_UPDATE = "/modules/helios/api/helios_change_status.php";
    public const URL_HELIOS_PES_RETOUR_GET = "/modules/helios/api/helios_get_retour.php";

    public const URL_GET_FILE_LIST = "/modules/actes/actes_transac_get_files_list.php";
    public const URL_DOWNLOAD_FILE = "/modules/actes/actes_download_file.php";

    public const FLUX_PES_RETOUR = "helios-pes-retour";
    public const FLUX_REPONSE_PREFECTURE = "actes-reponse-prefecture";

    private $reponseFile;

    private $objectInstancier;
    private $curlWrapperFactory;

    /** @var  CurlWrapper */
    protected $curlWrapper;

    protected $ensureLogin;
    protected $en_attente;
    protected $authentication_for_teletransmisson;
    protected $forward_x509_certificate;
    protected $forward_x509_certificate_pem;

    protected $special_header_added;

    protected $tedetisURL;

    protected $classificationFile;
    protected $isActivate;


    /** @var  DonneesFormulaire */
    private $collectiviteProperties;

    public function __construct(ObjectInstancier $objectInstancier)
    {
        $this->objectInstancier = $objectInstancier; //C'est vraiment très MAL !
        $this->curlWrapperFactory = $this->objectInstancier->getInstance(CurlWrapperFactory::class);
    }


    public function setConnecteurConfig(DonneesFormulaire $collectiviteProperties)
    {
        $this->collectiviteProperties = $collectiviteProperties;

        $this->curlWrapper = $this->curlWrapperFactory->getInstance();
        $this->special_header_added = false;
        $this->curlWrapper->setServerCertificate($collectiviteProperties->getFilePath('server_certificate'));
        $this->curlWrapper->dontVerifySSLCACert();
        $this->curlWrapper->setClientCertificate(
            $collectiviteProperties->getFilePath('user_certificat_pem'),
            $collectiviteProperties->getFilePath('user_key_pem'),
            $collectiviteProperties->get('user_certificat_password')
        );


        if ($collectiviteProperties->get("user_login")) {
            $this->curlWrapper->httpAuthentication($collectiviteProperties->get("user_login"), $collectiviteProperties->get("user_password"));
            $this->ensureLogin = true;
        }
        $this->isActivate = $collectiviteProperties->get('activate');
        $this->tedetisURL = $collectiviteProperties->get('url');
        $this->classificationFile = $collectiviteProperties->getFilePath('classification_file');
        $this->en_attente = $collectiviteProperties->get('envoi_en_attente');
        $this->authentication_for_teletransmisson = $collectiviteProperties->get('authentication_for_teletransmisson');
        $this->forward_x509_certificate = $collectiviteProperties->get('forward_x509_certificate');
        $this->forward_x509_certificate_pem = $collectiviteProperties->getFileContent('forward_x509_certificate_pem');
    }


    /**
     * @return bool
     * @throws S2lowException
     */
    protected function ensureLogin()
    {
        if ($this->ensureLogin) {
            return true;
        }

        $output = $this->curlWrapper->get($this->tedetisURL . self::URL_LIST_LOGIN);


        if ($this->curlWrapper->getLastError()) {
            throw new S2lowException($this->curlWrapper->getLastError());
        }

        if ($output) {
            $this->ensureLogin = true;
            return true;
        }
        throw new S2lowException("La connexion S²low nécessite un login/mot de passe ");
    }

    /**
     * @param $url
     * @param bool $utf_8_encode
     * @return bool|mixed|string
     * @throws S2lowException
     */
    private function exec($url, $utf_8_encode = true)
    {
        $this->setForwardx509CertificateHeader();
        $this->ensureLogin();
        $output = $this->curlWrapper->get($this->tedetisURL . $url);
        $error = $this->curlWrapper->getLastError();

        if (! $output && $error) {
            throw new S2lowException($error);
        }
        if ($utf_8_encode) {
            $output = utf8_encode($output);
        }
        return $output;
    }

    /**
     * @throws S2lowException
     */
    private function setForwardx509CertificateHeader()
    {
        if (! $this->forward_x509_certificate) {
            return;
        }

        if (empty($this->forward_x509_certificate_pem)) {
            throw new S2lowException("Certificat d'identification absent");
        }

        $certicat_identification_der = $this->pem2der($this->forward_x509_certificate_pem);
        $certicat_identification = base64_encode($certicat_identification_der);
        if (! $this->special_header_added) {
            $this->curlWrapper->addHeader("org.s2low.forward-x509-identification", $certicat_identification);
            $this->special_header_added = true;
        }
    }

    /**
     * @throws S2lowException
     */
    public function verifyForwardCertificate()
    {
        if (! $this->forward_x509_certificate) {
            return;
        }
        if (empty($this->forward_x509_certificate_pem)) {
            throw new S2lowException("Certificat d'identification absent");
        }
        if (empty($_SERVER['SSL_CLIENT_CERT'])) {
            throw new S2lowException("L'utilisation du mode tranmission de certificat à S2low nécessite que Pastell puisse récupérer l'argument serveur ssl_client_cert");
        }
        openssl_x509_export(openssl_x509_read($_SERVER['SSL_CLIENT_CERT']), $forward_x509_certificate);
        $this->forward_x509_certificate = $forward_x509_certificate;
    }

    private function pem2der($pem_data)
    {
        $begin = "CERTIFICATE-----";
        $end   = "-----END";
        $pem_data = substr($pem_data, strpos($pem_data, $begin) + strlen($begin));
        $pem_data = substr($pem_data, 0, strpos($pem_data, $end));
        $der = base64_decode($pem_data);
        return $der;
    }

    public function getLogicielName()
    {
        return "S²low";
    }

    /**
     * @throws S2lowException
     */
    public function testConnexion()
    {
        $result = $this->exec(self::URL_TEST);
        if (! preg_match("/^OK/", $result)) {
            throw new S2lowException("Erreur lors de la tentative de connexion, S²low a répondu : $result");
        }
    }

    /**
     * @return bool|mixed|string
     * @throws S2lowException
     */
    public function getClassification()
    {
        $result = $this->exec(self::URL_CLASSIFICATION . "?api=1", false);
        if (!$result) {
            throw new S2lowException($this->curlWrapper->getLastError());
        }
        if (preg_match("/^KO/", $result)) {
            throw new S2lowException("S²low a répondu : " . utf8_encode($result));
        }
        return $result;
    }

    /**
     * @return string
     * @throws S2lowException
     */
    public function demandeClassification()
    {
        $result = $this->exec(self::URL_DEMANDE_CLASSIFICATION . "?api=1");
        if (preg_match("/^KO/", $result)) {
            throw new S2lowException("S²low a répondu : " . $result);
        }
        return "S²low a répondu : " . $result;
    }

    /**
     * @param $id_transaction
     * @return string
     * @throws S2lowException
     */
    public function annulationActes($id_transaction)
    {
        $this->curlWrapper->addPostData('api', 1);
        $this->curlWrapper->addPostData('id', $id_transaction);
        $result = $this->exec(self::URL_ANNULATION);
        if (! $result) {
            throw new S2lowException("Erreur lors de la connexion a S²low (" . $this->tedetisURL . ")");
        }

        if (! preg_match("/^OK/", $result)) {
            throw new S2lowException("Erreur lors de la transmission, S²low a répondu : $result");
        }
        $ligne = explode("\n", $result);
        $id_transaction = trim($ligne[1]);
        return $id_transaction;
    }

    /**
     * @return bool
     * @throws S2lowException
     */
    public function verifClassif()
    {

        if (! is_file($this->classificationFile)) {
            throw new S2lowException("Il n'y a pas de fichier de classification Actes");
        }

        $usingClassif = file_get_contents($this->classificationFile);
        $theClassif = $this->getClassification();

        if ($usingClassif != $theClassif) {
            throw new S2lowException("La classification utilisée n'est plus à jour");
        }
        return true;
    }

    /**
     * @param Fichier $fichierHelios
     * @return SimpleXMLElement
     * @throws S2lowException
     */
    public function sendHelios(Fichier $fichierHelios)
    {
        $this->verifyForwardCertificate();
        $file_path = $fichierHelios->filepath;
        $file_name = $fichierHelios->filename;
        $file_name = $this->getHeliosEnveloppeFileName($file_name);
        $this->curlWrapper->addPostFile('enveloppe', $file_path, $file_name);
        $result = $this->exec(self::URL_POST_HELIOS);

        $simpleXMLWrapper = new SimpleXMLWrapper();
        try {
            $xml = $simpleXMLWrapper->loadString($result);
        } catch (SimpleXMLWrapperException $e) {
            throw new S2lowException("La réponse de S²low n'a pas pu être analysée : " . get_hecho($result));
        }

        if ($xml->{'resultat'} != "OK") {
            throw new S2lowException("Erreur lors de l'envoi du PES : " . $xml->{'message'});
        }
        return $xml->{'id'};
    }

    /**
     *
     * @deprecated PA 3.0 use sendHelios() instead
     * @param DonneesFormulaire $donneesFormulaire
     * @return bool
     * @throws S2lowException
     */
    public function postHelios(DonneesFormulaire $donneesFormulaire)
    {
        $this->verifyForwardCertificate();
        $file_path = $donneesFormulaire->getFilePath('fichier_pes_signe');
        $file_name = $donneesFormulaire->get('fichier_pes_signe');
        $file_name = $this->getHeliosEnveloppeFileName($file_name[0]);
        $this->curlWrapper->addPostFile('enveloppe', $file_path, $file_name);
        $result = $this->exec(self::URL_POST_HELIOS);

        $simpleXMLWrapper = new SimpleXMLWrapper();
        try {
            $xml = $simpleXMLWrapper->loadString($result);
        } catch (Exception $e) {
            throw new S2lowException("La réponse de S²low n'a pas pu être analysée : " . get_hecho($result));
        }

        if ($xml->{'resultat'} == "OK") {
            $donneesFormulaire->setData('tedetis_transaction_id', $xml->{'id'});
            return true;
        }
        throw new S2lowException("Erreur lors de l'envoi du PES : " . $xml->{'message'});
    }

    public function getHeliosEnveloppeFileName(?string $name): string
    {
        return preg_replace("#[^a-zA-Z0-9._\- ]#", "_", $name);
    }


    private function getIsEnAttente()
    {
        if ($this->en_attente) {
            return 1;
        }
        if ($this->authentication_for_teletransmisson) {
            return 1;
        }
        return 0;
    }

    /**
     * @param TdtActes $tdtActes
     * @return bool
     * @throws S2lowException
     */
    public function sendActes(TdtActes $tdtActes)
    {
        $this->verifyForwardCertificate();

        $this->verifClassif();

        $this->curlWrapper->addPostData('api', 1);
        $this->curlWrapper->addPostData('nature_code', $tdtActes->acte_nature);

        $this->curlWrapper->addPostData('number', $tdtActes->numero_de_lacte);
        $this->curlWrapper->addPostData('subject', utf8_decode($tdtActes->objet));

        $this->curlWrapper->addPostData('decision_date', date("Y-m-d", strtotime($tdtActes->date_de_lacte)));
        $this->curlWrapper->addPostData('en_attente', $this->getIsEnAttente());

        $this->curlWrapper->addPostData('document_papier', $tdtActes->document_papier ? 1 : 0);

        if ($tdtActes->type_acte) {
            $this->curlWrapper->addPostData('type_acte', $tdtActes->type_acte);
        }
        if ($tdtActes->type_pj) {
            foreach (json_decode($tdtActes->type_pj) as $type_pj) {
                $this->curlWrapper->addPostData('type_pj[]', $type_pj);
            }
        }

        $file_path = $tdtActes->arrete->filepath;
        $file_name = $tdtActes->arrete->filename;

        $file_name = $this->getFilenameTransformation($file_name);
        $this->curlWrapper->addPostFile('acte_pdf_file', $file_path, $file_name);

        if ($tdtActes->autre_document_attache) {
            foreach ($tdtActes->autre_document_attache as $i => $annexe_file) {
                $file_name = $this->getFilenameTransformation($annexe_file->filename);
                $file_path = $annexe_file->filepath;
                $this->curlWrapper->addPostFile('acte_attachments[]', $file_path, $file_name) ;
            }
        }

        $classification  = $tdtActes->classification;
        $c1 = explode(" ", $classification);
        $dataClassif = explode(".", $c1[0]);

        foreach ($dataClassif as $i => $elementClassif) {
            $this->curlWrapper->addPostData('classif' . ( $i + 1), $elementClassif);
        }

        $result = $this->exec(self::URL_POST_ACTES);
        if (! $result) {
            throw new S2lowException("Erreur lors de la connexion à S²low (" . $this->tedetisURL . ")");
        }

        if (! preg_match("/^OK/", $result)) {
            throw new S2lowException("Erreur lors de la transmission, S²low a répondu : $result");
        }

        $ligne = explode("\n", $result);
        return trim($ligne[1]);
    }


    /**
     * @deprecated PA 3.0 use sendActes() instead
     * @param DonneesFormulaire $donneesFormulaire
     * @return bool
     * @throws S2lowException
     * @throws UnrecoverableException
     */
    public function postActes(DonneesFormulaire $donneesFormulaire)
    {

        $this->verifyForwardCertificate();

        $this->verifClassif();

        $this->curlWrapper->addPostData('api', 1);
        $this->curlWrapper->addPostData('nature_code', $donneesFormulaire->get('acte_nature'));

        $this->curlWrapper->addPostData('number', $donneesFormulaire->get('numero_de_lacte'));
        $this->curlWrapper->addPostData('subject', utf8_decode($donneesFormulaire->get('objet')));

        $this->curlWrapper->addPostData('decision_date', date("Y-m-d", strtotime($donneesFormulaire->get('date_de_lacte'))));
        $this->curlWrapper->addPostData('en_attente', $this->getIsEnAttente());

        $this->curlWrapper->addPostData('document_papier', $donneesFormulaire->get('document_papier') ? 1 : 0);

        $type_default = $this->getDefaultTypology($donneesFormulaire->get('acte_nature'), $this->classificationFile);


        if ($donneesFormulaire->get('type_acte')) {
            $this->curlWrapper->addPostData('type_acte', $donneesFormulaire->get('type_acte'));
        } else {
            $this->curlWrapper->addPostData('type_acte', $type_default);
        }
        if ($donneesFormulaire->get('type_pj')) {
            foreach (json_decode($donneesFormulaire->get('type_pj')) as $type_pj) {
                $this->curlWrapper->addPostData('type_pj[]', $type_pj);
            }
        } elseif ($donneesFormulaire->get('autre_document_attache')) {
            foreach ($donneesFormulaire->get('autre_document_attache') as $file) {
                $this->curlWrapper->addPostData('type_pj[]', $type_default);
            }
        }
        if ($donneesFormulaire->get('is_pades')) {
            $file_path = $donneesFormulaire->getFilePath('signature');
            $file_name = $donneesFormulaire->get('signature');
        } else {
            $file_path = $donneesFormulaire->getFilePath('arrete');
            $file_name = $donneesFormulaire->get('arrete');
        }
        $file_name = $this->getFilenameTransformation($file_name[0]);
        $this->curlWrapper->addPostFile('acte_pdf_file', $file_path, $file_name);

        if ($donneesFormulaire->get('autre_document_attache')) {
            foreach ($donneesFormulaire->get('autre_document_attache') as $i => $file_name) {
                $file_name = $this->getFilenameTransformation($file_name);
                $file_path = $donneesFormulaire->getFilePath('autre_document_attache', $i);
                $this->curlWrapper->addPostFile('acte_attachments[]', $file_path, $file_name) ;
            }
        }

        $classification  = $donneesFormulaire->get('classification');
        $c1 = explode(" ", $classification);
        $dataClassif = explode(".", $c1[0]);

        foreach ($dataClassif as $i => $elementClassif) {
            $this->curlWrapper->addPostData('classif' . ( $i + 1), $elementClassif);
        }

        $result = $this->exec(self::URL_POST_ACTES);
        if (! $result) {
            throw new S2lowException("Erreur lors de la connexion à S²low (" . $this->tedetisURL . ")");
        }

        if (! preg_match("/^OK/", $result)) {
            throw new S2lowException("Erreur lors de la transmission, S²low a répondu : $result");
        }

        $ligne = explode("\n", $result);
        $id_transaction = trim($ligne[1]);
        $donneesFormulaire->setData('tedetis_transaction_id', $id_transaction);

        return true;
    }

    /**
     * @param $id_transaction
     * @return string
     * @throws S2lowException
     */
    public function getStatusHelios($id_transaction)
    {
        $result = $this->exec(self::URL_STATUS_HELIOS . "?transaction=$id_transaction", false);
        $simpleXMLWrapper = new SimpleXMLWrapper();
        try {
            $xml = $simpleXMLWrapper->loadString($result);
        } catch (SimpleXMLWrapperException $e) {
            throw new S2lowException(
                "La réponse de S²low n'a pas pu être analysée (problème d'authentification ?)"
            );
        }

        if ($xml->{'resultat'} == "KO") {
            throw new S2lowException($xml->{'message'});
        }
        $this->reponseFile = $result;
        return strval($xml->{'status'});
    }


    /**
     * @param $id_transaction
     * @return bool|mixed|string
     * @throws S2lowException
     */
    public function getStatus($id_transaction)
    {
        $result = $this->exec(self::URL_STATUS . "?transaction=$id_transaction");

        $ligne = explode("\n", $result);

        if (trim($ligne[0]) != 'OK') {
            throw new S2lowException(trim($ligne[1]));
        }

        $result = trim($ligne[1]);
        if ($result == 4) {
            array_shift($ligne);
            array_shift($ligne);
            $this->setArActes(utf8_decode(implode("\n", $ligne)));
        }

        if ($result == -1) {
            array_shift($ligne);
            array_shift($ligne);
            $this->lastError = utf8_decode(implode("\n", $ligne));
        }
        return $result;
    }

    /**
     * @return bool
     * @throws Exception
     * @throws S2lowException
     */
    // Pour test:
    // http://simulateurhelios.formations.adullact.org/index.php/Accueil/index/
    // L'ADULLACT, SIRET : 96848903944889
    public function getPESRetourListe()
    {
        //get PES Retour non lu

        $pes = array();
        $this->verifyForwardCertificate();
        $result = $this->exec(self::URL_HELIOS_PES_RETOUR_LISTE);
        $xml = @ simplexml_load_string($result);
        if (! $xml) {
            throw new S2lowException("La réponse de S²low n'a pas pu être analysée : (" . $result . ")");
        }
        if (!empty($xml->pes_retour)) {
            foreach ($xml->pes_retour as $pes_retour) {
                $pes['id'] = strval($pes_retour->{'id'});
                $pes['nom'] = strval($pes_retour->{'nom'});
                $pes['date'] = strval($pes_retour->{'date'});
                $this->getPESRetour($pes);
            }
            return true;
        }
        throw new S2lowException("S2low ne retourne pas de PES Retour");
    }

    /**
     * @param array $pes
     * @return bool|string
     * @throws Exception
     * @throws S2lowException
     */
    public function getPESRetour($pes = array())
    {
        // création document flux helios PES Retour non lu

        $connecteur_info = $this->getConnecteurInfo();
        $id_e = $connecteur_info['id_e'];

        $fic_pes = $this->exec(self::URL_HELIOS_PES_RETOUR_GET . "?id=" . $pes['id'], false);

        /** @var DocumentTypeFactory $documentTypeFactory */
        $documentTypeFactory = $this->objectInstancier->getInstance(DocumentTypeFactory::class);
        if (! $documentTypeFactory->isTypePresent(self::FLUX_PES_RETOUR)) {
            throw new Exception("Le type " . self::FLUX_PES_RETOUR . " n'existe pas sur cette plateforme Pastell");
        }

        $documentCreationService = $this->objectInstancier->getInstance(DocumentCreationService::class);
        $new_id_d = $documentCreationService->createDocumentWithoutAuthorizationChecking($id_e, self::FLUX_PES_RETOUR);

        /** @var DonneesFormulaire $donneesFormulaire */
        $donneesFormulaire = $this->objectInstancier->getInstance(DonneesFormulaireFactory::class)->get($new_id_d);

        $nom_pes = $pes['nom'];
        if (substr($nom_pes, -4) !== ".xml") {
            return "$nom_pes n'est pas un fichier xml";
        }
        $donneesFormulaire->setData("objet", substr($nom_pes, 0, -4));
        $donneesFormulaire->setData("date_tdt", $pes['date']);
        $donneesFormulaire->setData("id_retour", $pes['id']);
        $donneesFormulaire->setData("envoi_ged", true);

        $titre_fieldname = $donneesFormulaire->getFormulaire()->getTitreField();
        $titre = $donneesFormulaire->get($titre_fieldname);
        $this->objectInstancier->getInstance(DocumentSQL::class)->setTitre($new_id_d, $titre);

        $donneesFormulaire->addFileFromData("fichier_pes", $nom_pes, $fic_pes);

        $actionCreatorSQL = $this->objectInstancier->getInstance(ActionCreatorSQL::class);
        $actionCreatorSQL->addAction($id_e, 0, Action::CREATION, "Importation du PES Retour avec succès", $new_id_d);

        $this->objectInstancier->getInstance(NotificationMail::class)->notify($id_e, $new_id_d, Action::CREATION, self::FLUX_PES_RETOUR, 'Importation du PES Retour avec succès');

        //passage à l'etat lu
        $this->exec(self::URL_HELIOS_PES_RETOUR_UPDATE . "?id=" . $pes['id']);

        return true;
    }

    /**
     * @param DonneesFormulaire $donneesFormulaire
     * @return bool
     * @throws Exception
     * @throws S2lowException
     */
    public function getPESRetourLu(DonneesFormulaire $donneesFormulaire)
    {
        // helios_get_retour de PES Retour lu
        $id_retour = $donneesFormulaire->get('id_retour');
        $nom_pes = $donneesFormulaire->get('objet') . ".xml";
        $fic_pes = $this->exec(self::URL_HELIOS_PES_RETOUR_GET . "?id=$id_retour", false);
        $donneesFormulaire->addFileFromData("fichier_pes", $nom_pes, $fic_pes);
        return true;
    }

    public function getLastReponseFile()
    {
        return $this->reponseFile;
    }

    /**
     * @param $id_transaction
     * @return bool|string
     * @throws S2lowException
     */
    public function getDateAR($id_transaction)
    {
        $result = $this->exec(self::URL_STATUS . "?transaction=$id_transaction");
        return (substr($result, strpos($result, 'actes:DateReception') + 21, 10));
    }

    /**
     * @param $id_transaction
     * @return bool|mixed|string
     * @throws S2lowException
     */
    public function getBordereau($id_transaction)
    {
        $result = $this->exec(self::URL_BORDEREAU . "?trans_id=$id_transaction", false);
        return $result;
    }


    /**
     * Fonction compatible S2low v2 et S2low < v2
     * @see TdtConnecteur::getActeTamponne()
     */
    /**
     * @param $id_transaction
     * @return bool|mixed|string
     * @throws S2lowException
     */
    public function getActeTamponne($id_transaction)
    {
        $file_list = $this->getActeTamponneS2lowV2FileList($id_transaction);
        if (! $file_list) {
            //S2low v<2
            $result = $this->exec(
                self::URL_ACTES_TAMPONNE . "?transaction=$id_transaction",
                false
            );
            return $result;
        }
        //S2low v2
        return $this->getActeTamponneS2lowV2($file_list);
    }

    private function getActeTamponneS2lowV2FileList($id_transaction)
    {
        try {
            $file_list = $this->exec(self::URL_GET_FILE_LIST . "?transaction=$id_transaction");
        } catch (Exception $e) {
            return false;
        }
        if (!$file_list) {
            return false;
        }
        $file_list = json_decode($file_list, true);
        if (!$file_list) {
            return false;
        }
        return $file_list;
    }

    /**
     * @param $file_list
     * @return bool|mixed|string
     * @throws S2lowException
     */
    private function getActeTamponneS2lowV2($file_list)
    {
        if ($file_list[1]['mimetype'] != 'application/pdf') {
            return false;
        }

        $result = $this->exec(
            self::URL_DOWNLOAD_FILE . "?file={$file_list[1]['id']}&tampon=true",
            false
        );
        return $result;
    }

    /**
     * @param $transaction_id
     * @return bool|mixed|string
     * @throws RecoverableException
     */
    public function getFichierRetour($transaction_id)
    {
        $result = $this->exec(self::URL_HELIOS_RETOUR . "?id=$transaction_id");
        $simpleXMLWrapper = new SimpleXMLWrapper();
        try {
            $simpleXMLWrapper->loadString($result);
        } catch (SimpleXMLWrapperException $e) {
            throw new RecoverableException(
                "Impossible d'analyser le fichier PES Acquit : " . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
        return $result;
    }

    /**
     * @return int|void
     * @throws NotFoundException
     * @throws S2lowException
     * @throws UnrecoverableException
     */
    public function getListDocumentPrefecture()
    {
        $data = array();
        $this->verifyForwardCertificate();
        $result = $this->exec(self::URL_ACTES_REPONSE_PREFECTURE_LISTE);

        if ($result) {
            foreach (json_decode($result, true) as $key => $value) {
                /** TODO: bypass bug in s2low < 4.1.1, to be removed */
                if (
                    $value['type'] == TdtConnecteur::COURRIER_SIMPLE
                    && $value['last_status_id'] != TdtConnecteur::STATUS_ACTES_MESSAGE_PREF_RECU_PAS_D_AR
                ) {
                    continue;
                }
                $data[$key] = $value;
            }
            foreach ($data as $reponse) {
                $this->getDocumentPrefecture($reponse);
            }
            return count($data);
        }

        throw new S2lowException("S2low ne retourne pas de Réponse de la préfecture");
    }

    /**
     * @param array $reponse
     * @return bool
     * @throws NotFoundException
     * @throws S2lowException
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function getDocumentPrefecture($reponse = array())
    {
        // création document flux actes réponse préfecture non lu

        $connecteur_info = $this->getConnecteurInfo();
        $id_e = $connecteur_info['id_e'];

        /** @var DocumentTypeFactory $documentTypeFactory */
        $documentTypeFactory = $this->objectInstancier->getInstance(DocumentTypeFactory::class);
        if (! $documentTypeFactory->isTypePresent(self::FLUX_REPONSE_PREFECTURE)) {
            throw new Exception("Le type " . self::FLUX_REPONSE_PREFECTURE . " n'existe pas sur cette plateforme Pastell");
        }

        $documentCreationService = $this->objectInstancier->getInstance(DocumentCreationService::class);
        $new_id_d = $documentCreationService->createDocumentWithoutAuthorizationChecking($id_e, self::FLUX_REPONSE_PREFECTURE);

        /** @var DonneesFormulaire $donneesFormulaire */
        $donneesFormulaire = $this->objectInstancier->getInstance(DonneesFormulaireFactory::class)->get($new_id_d);

        $type = $this->getLibelleType($reponse['type']);
        $nature_int = $this->getIntNatureActe(substr($reponse['unique_id'], -2));

        $donneesFormulaire->setData('acte_nature', $nature_int);
        $donneesFormulaire->setData('numero_de_lacte', $reponse['number']);
        $donneesFormulaire->setData('type_reponse', $reponse['type']);
        $donneesFormulaire->setData('related_transaction_id', $reponse['related_transaction_id']);
        $donneesFormulaire->setData('transaction_id', $reponse['id']);
        $donneesFormulaire->setData('last_status_id', $reponse['last_status_id']);

        $acteDocumentId = $this->objectInstancier
            ->getInstance(DocumentIndexSQL::class)
            ->getByFieldValue('acte_unique_id', $reponse['unique_id']);
        if ($acteDocumentId) {
            $acteDocument = $this->objectInstancier->getInstance(DonneesFormulaireFactory::class)->get($acteDocumentId);
            $acteEntite = $this->objectInstancier->getInstance(DocumentEntite::class)->getEntite($acteDocumentId);
            $url = sprintf("/Document/detail?id_d=%s&id_e=%s", $acteDocumentId, $acteEntite[0]['id_e']);
            $donneesFormulaire->setData('url_acte', SITE_BASE . $url);

            $linksToDocuments = [];
            if ($acteDocument->get('reponse_prefecture_file')) {
                $file_content = $acteDocument->getFileContent('reponse_prefecture_file');
                $linksToDocuments = json_decode($file_content, true);
            }
            $linksToDocuments[$reponse['type']] = sprintf("/Document/detail?id_d=%s&id_e=%s", $new_id_d, $id_e);
            $acteDocument->addFileFromData(
                'reponse_prefecture_file',
                'reponse_prefecture.json',
                json_encode($linksToDocuments)
            );
            $acteDocument->setData('has_reponse_prefecture', true);
        }

        $file_content = $this->getReponsePrefecture($reponse['id']);

        $donneesFormulaire->setData("has_{$type}", true);
        $donneesFormulaire->setData('date', date('Y-m-d H:i:m'));
        $donneesFormulaire->addFileFromData('reponse_prefecture', "{$type}.tar.gz", $file_content);

        $file_path = $donneesFormulaire->getFilePath('reponse_prefecture');

        $tmpFolder = $this->objectInstancier->getInstance(TmpFolder::class);
        $tmp_folder = $tmpFolder->create();

        $temp_file_path = $tmp_folder . "/fichier.tar.gz";
        copy($file_path, $temp_file_path);

        $result_folder = $tmp_folder . "/result/";
        mkdir($result_folder);

        $command = "tar -zxvf $temp_file_path --directory $result_folder 2>&1";
        exec($command, $output, $return_var);

        $file_list = scandir($result_folder);
        $num_file = 0;
        foreach ($file_list as $file_result) {
            $file_result_path = $result_folder . "/" . $file_result;
            if (is_file($file_result_path)) {
                $donneesFormulaire->addFileFromCopy('reponse_prefecture_unzip', $file_result, $file_result_path, $num_file++);
            }
        }
        $tmpFolder->delete($tmp_folder);

        $titre_fieldname = $donneesFormulaire->getFormulaire()->getTitreField();
        $titre = $donneesFormulaire->get($titre_fieldname);
        $this->objectInstancier->getInstance(DocumentSQL::class)->setTitre($new_id_d, $titre);

        $actionCreatorSQL = $this->objectInstancier->getInstance(ActionCreatorSQL::class);

        if ($reponse['type'] == TdtConnecteur::DEFERE_TRIBUNAL_ADMINISTRATIF) {
            $actionCreatorSQL->addAction($id_e, 0, 'termine', 'Ce type de réponse de la préfecture ne prévoit pas de retour', $new_id_d);
        } else {
            $actionCreatorSQL->addAction($id_e, 0, 'attente-reponse-prefecture', "Attente d'une réponse", $new_id_d);
        }

        $this->exec(self::URL_ACTES_REPONSE_PREFECTURE_MARK_AS_READ . "?transaction_id=" . $reponse['id']);
        return true;
    }

    /**
     * @param $transaction_id
     * @return array
     * @throws S2lowException
     */
    public function getListReponsePrefecture($transaction_id)
    {
        $result = array();
        $all_reponse = $this->exec(self::URL_ACTES_REPONSE_PREFECTURE . "?id=$transaction_id");
        $all_reponse = trim($all_reponse);
        if (!$all_reponse) {
            return $result;
        }
        foreach (explode("\n", $all_reponse) as $line) {
            list($type,$status,$id) = explode("-", $line);
            $result[] = array('type' => $type,'status' => $status,'id' => $id);
        }
        return $result;
    }

    /**
     * @param $transaction_id
     * @return bool|mixed|string
     * @throws S2lowException
     */
    public function getReponsePrefecture($transaction_id)
    {

        return $this->exec(self::URL_ACTES_REPONSE_PREFECTURE . "?id=$transaction_id", false);
    }

    /**
     * @param DonneesFormulaire $donneesFormulaire
     * @throws S2lowException
     * @throws UnrecoverableException
     */
    public function sendResponse(DonneesFormulaire $donneesFormulaire)
    {
        $this->sendReponseType($donneesFormulaire->get('type_reponse'), $donneesFormulaire);
    }

    private function getLibelleType($id_type)
    {
        $txt_message = [
            TdtConnecteur::COURRIER_SIMPLE => 'courrier_simple',
            TdtConnecteur::DEMANDE_PIECE_COMPLEMENTAIRE => 'demande_piece_complementaire',
            TdtConnecteur::LETTRE_OBSERVATION => 'lettre_observation',
            TdtConnecteur::DEFERE_TRIBUNAL_ADMINISTRATIF => 'defere_tribunal_administratif'
        ];
        return $txt_message[$id_type];
    }

    /**
     * @param $id_type
     * @param DonneesFormulaire $donneesFormulaire
     * @return bool
     * @throws S2lowException
     * @throws UnrecoverableException
     */
    private function sendReponseType($id_type, DonneesFormulaire $donneesFormulaire)
    {

        $libelle = $this->getLibelleType($id_type);

        $nature_reponse = $donneesFormulaire->get('refus_reponse') ? 3 : 4;
        $file_name = $donneesFormulaire->getFileName('reponse');
        $file_path = $donneesFormulaire->getFilePath('reponse');

        $type_actes_element = 'type_acte_' . $libelle;
        $type_pj_element  = 'type_pj_' . $libelle;

        $type_default = $this->getDefaultTypology($donneesFormulaire->get('acte_nature'), $this->classificationFile);

        if ($donneesFormulaire->get($type_actes_element)) {
            $this->curlWrapper->addPostData('type_acte', $donneesFormulaire->get($type_actes_element));
        } else {
            $this->curlWrapper->addPostData('type_acte', $type_default);
        }

        $id = $donneesFormulaire->get('transaction_id');

        $this->curlWrapper->addPostData('id', $id);
        $this->curlWrapper->addPostData('api', 1);
        $this->curlWrapper->addPostData('type_envoie', $nature_reponse);
        $this->curlWrapper->addPostFile('acte_pdf_file', $file_path, $file_name);

        if (
            ($id_type == 3)
            && $nature_reponse === 4
            && $donneesFormulaire->get('reponse_pj_demande_piece_complementaire')
        ) {
            foreach ($donneesFormulaire->get('reponse_pj_demande_piece_complementaire') as $i => $file_name) {
                $file_path = $donneesFormulaire->getFilePath('reponse_pj_demande_piece_complementaire', $i);
                $this->curlWrapper->addPostFile('acte_attachments[]', $file_path, $file_name) ;

                if ($donneesFormulaire->get($type_pj_element)) {
                    $type_pj = json_decode($donneesFormulaire->get($type_pj_element))[$i];
                    $this->curlWrapper->addPostData('type_pj[]', $type_pj);
                } else {
                    $this->curlWrapper->addPostData('type_pj[]', $type_default);
                }
            }
        }

        $result = $this->exec(self::URL_POST_REPONSE_PREFECTURE);
        if (! preg_match("/^OK/", $result)) {
            throw new S2lowException("Erreur lors de la transmission, S²low a répondu : $result");
        }

        $ligne = explode("\n", $result);
        $id_transaction = trim($ligne[1]);
        $donneesFormulaire->setData('response_transaction_id', $id_transaction);
        return true;
    }

    public function getRedirectURLForTeletransimission()
    {
        return $this->tedetisURL . self::URL_POST_CONFIRM;
    }

    public function getRedirectURLForTeletransimissionMulti()
    {
        return $this->tedetisURL . self::URL_POST_CONFIRM_MULTI;
    }

    /**
     * @param $transaction_id
     * @return array
     * @throws S2lowException
     */
    //Cette fonction fonctionne sur une branche de S2low 1.5 ou 2.0
    //Elle ne lance pas d'exception (la branche 1.5 ne connait pas cette fonction).
    //Lorsque la version 1.5 de S2low n'existera plus, il conviendra de modifier la fonction
    //pour qu'elle déclenche de véritables erreurs en cas de problème.
    public function getAnnexesTamponnees($transaction_id)
    {
        try {
            $file_list = $this->exec(self::URL_GET_FILE_LIST . "?transaction=$transaction_id");
        } catch (Exception $e) {
            return array();
        }
        if (!$file_list) {
            return array();
        }
        $file_list = json_decode($file_list, true);
        if (!$file_list) {
            return array();
        }

        if (count($file_list) <= 2) {
            return array();
        }
        array_shift($file_list);
        array_shift($file_list);
        $result = array();
        foreach ($file_list as $file) {
            $filename = $file['posted_filename'];
            if ($file['mimetype'] != 'application/pdf') {
                $result[] = [];
                continue;
            }
            $content = $this->exec(
                self::URL_DOWNLOAD_FILE . "?file={$file['id']}&tampon=true",
                false
            );
            $result[] = [
                'filename' => $filename,
                'content' => $content
            ];
        }
        return $result;
    }

    /**
     * @return bool|string
     * @throws S2lowException
     */
    public function getNounce()
    {
        if (! $this->collectiviteProperties->get('user_login')) {
            return false;
        }
        try {
            $result = $this->exec(self::URL_GET_NOUNCE);
            $result = json_decode($result, true);
        } catch (Exception $e) {
            return false;
        }
        $result['login'] = $this->collectiviteProperties->get('user_login');
        $result['hash'] = hash("sha256", "{$this->collectiviteProperties->get('user_password')}:{$result['nounce']}");

        $url_param = "nounce={$result['nounce']}&login={$result['login']}&hash={$result['hash']}";

        return $url_param;
    }

    public function getURLTestNounce()
    {
        $url_param = $this->getNounce();
        return $this->collectiviteProperties->get('url') . self::URL_TEST . "?$url_param";
    }

    /**
     * @param string $filename
     * @return string
     */
    public function getFilenameTransformation(string $filename): string
    {
        return preg_replace("#[^a-zA-Z0-9._ ]#", "_", $filename);
    }
}

//class S2lowException extends TdTException {}
