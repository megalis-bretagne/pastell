<?php

require_once __DIR__ . "/../cpp/lib/UTF8Encoder.class.php";
require_once __DIR__ . "/../cpp/lib/CPPWrapperConfig.class.php";
require_once __DIR__ . "/../cpp/lib/CPPWrapper.class.php";
require_once __DIR__ . "/../cpp/lib/CPPWrapperFactory.class.php";

class ChorusParCsv extends PortailFactureConnecteur
{
    private const DEPOSE_DEPUIS_NB_JOURS = 30;

    private $depose_depuis_nb_jours;

    private $user_login;
    private $user_password;

    /** @var DonneesFormulaire $globalConfig */
    private $globalConfig;

    /** @var  DonneesFormulaire $connecteurConfig */
    private $connecteurConfig;

    /** @var  CPPWrapper */
    private $cppWrapper;

    /** @var CPPWrapperFactory */
    private $cppWrapperFactory;

    public function __construct(ObjectInstancier $objectInstancier)
    {
        $this->cppWrapperFactory = $objectInstancier->getInstance(CPPWrapperFactory::class);
        parent::__construct($objectInstancier);
    }

    /**
     * @param DonneesFormulaire $donneesFormulaire
     * @throws CPPException
     */
    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        $this->connecteurConfig = $donneesFormulaire;
        $this->setConfigFromGlobalConnecteur();
        $cppWrapperConfig = new CPPWrapperConfig();
        $cppWrapperConfig->url = $this->getFromLocalOrGlobalConfig('url');
        $cppWrapperConfig->user_login = $donneesFormulaire->get('user_login');
        $cppWrapperConfig->user_password = $donneesFormulaire->get('user_password');
        $cppWrapperConfig->certificat_pem = $this->getFileFromLocalOrGlobalConfig('certificat_pem');
        $cppWrapperConfig->certificat_prikey_pem = $this->getFileFromLocalOrGlobalConfig('certificat_prikey_pem');
        $cppWrapperConfig->certificate_chain = $this->getFileFromLocalOrGlobalConfig('certificate_chain');
        $cppWrapperConfig->certificat_password = $this->getFromLocalOrGlobalConfig('certificat_password');

        $cppWrapperConfig->url_piste_get_token = $this->getFromLocalOrGlobalConfig('url_piste_get_token');
        $cppWrapperConfig->client_id = $this->getFromLocalOrGlobalConfig('client_id');
        $cppWrapperConfig->client_secret = $this->getFromLocalOrGlobalConfig('client_secret');
        $cppWrapperConfig->url_piste_api = $this->getFromLocalOrGlobalConfig('url_piste_api');
        $cppWrapperConfig->cpro_account = base64_encode($donneesFormulaire->get('user_login') . ":" . $donneesFormulaire->get('user_password'));

        $cppWrapperConfig->identifiant_structure_cpp = $donneesFormulaire->get('identifiant_structure_cpp');

        $this->user_login = $donneesFormulaire->get('user_login');

        $this->depose_depuis_nb_jours = $this->getDeposeDepuisNbJours($donneesFormulaire);

        $this->cppWrapper = $this->cppWrapperFactory->newInstance();
        $this->cppWrapper->setCppWrapperConfig($cppWrapperConfig);
    }

    /**
     * @return mixed
     */
    public function getUserLogin()
    {
        return $this->user_login;
    }

    /**
     * @param DonneesFormulaire $donneesFormulaire
     * @return array|int|string
     */
    public function getDeposeDepuisNbJours(DonneesFormulaire $donneesFormulaire)
    {
        $depose_depuis_nb_jours = $donneesFormulaire->get('depose_depuis_nb_jours');
        if (($depose_depuis_nb_jours) && (is_numeric($depose_depuis_nb_jours))) {
            return $depose_depuis_nb_jours;
        }
        $donneesFormulaire->setData('depose_depuis_nb_jours', self::DEPOSE_DEPUIS_NB_JOURS);
        return self::DEPOSE_DEPUIS_NB_JOURS;
    }

    /**
     * @return false|string
     */
    public function getDateDepuisLe()
    {
        $time_debut = time() - ($this->depose_depuis_nb_jours * 86400);
        return date('Y-m-d', $time_debut);
    }

    /**
     * @param $element_name
     * @return array|bool|string
     */
    protected function getFromLocalOrGlobalConfig($element_name)
    {
        $value = $this->connecteurConfig->get($element_name);

        if ($value) {
            return $value;
        }
        if ($this->globalConfig) {
            return $this->globalConfig->get($element_name);
        }
        return false;
    }

    /**
     * @param $element_name
     * @return bool|string
     */
    private function getFileFromLocalOrGlobalConfig($element_name)
    {
        $value = $this->connecteurConfig->getFilePath($element_name);
        if ($value && file_exists($value)) {
            return $value;
        }
        if ($this->globalConfig) {
            return $this->globalConfig->getFilePath($element_name);
        }
        return false;
    }

    private function setConfigFromGlobalConnecteur()
    {
        /** @var ConnecteurFactory $connecteurFactory */
        $connecteurFactory = $this->objectInstancier->getInstance(ConnecteurFactory::class);
        $this->globalConfig = $connecteurFactory->getGlobalConnecteurConfig('PortailFacture');
    }

    /**
     * @param $fonction_cpp
     * @param array $data
     * @return array|mixed
     * @throws Exception
     */
    public function call($fonction_cpp, array $data)
    {
        return $this->cppWrapper->call($fonction_cpp, $data);
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function testConnexion()
    {
        return $this->cppWrapper->testConnexion();
    }

    /**
     * @param string $idFournisseur
     * @param string $periodeDateHeureEtatCourantDu
     * @param string $periodeDateHeureEtatCourantAu
     * @return array|mixed
     * @throws Exception
     */
    public function rechercheFactureParRecipiendaire(
        string $idFournisseur = "",
        string $periodeDateHeureEtatCourantDu = "",
        string $periodeDateHeureEtatCourantAu = ""
    ) {
        return $this->cppWrapper->rechercheFactureParRecipiendaire(
            $idFournisseur,
            $periodeDateHeureEtatCourantDu,
            $periodeDateHeureEtatCourantAu
        );
    }

    /**
     * @param string $periodeDateHeureEtatCourantDu
     * @param string $periodeDateHeureEtatCourantAu
     * @return array|mixed
     * @throws Exception
     */
    protected function rechercheFactureTravaux(
        string $periodeDateHeureEtatCourantDu = "",
        string $periodeDateHeureEtatCourantAu = ""
    ) {
        return $this->cppWrapper->rechercheFactureTravaux(
            $periodeDateHeureEtatCourantDu,
            $periodeDateHeureEtatCourantAu
        );
    }

    /**
     * @param $idFacture
     * @param int $nbResultatsMaximum
     * @return array|mixed
     * @throws Exception
     */
    protected function consulterHistoriqueFacture($idFacture, $nbResultatsMaximum = 50)
    {
        return $this->cppWrapper->consulterHistoriqueFacture($idFacture, $nbResultatsMaximum);
    }

    /**
     * @param $format
     * @param $idFacture
     * @return false|mixed|string
     * @throws Exception
     */
    protected function telechargerGroupeFacture($format, $idFacture)
    {
        return $this->cppWrapper->telechargerGroupeFacture($format, $idFacture);
    }

    /**
     * @param $idFacture
     * @param $idNouveauStatut
     * @param string $motif
     * @param string $numeroMandat
     * @return array|mixed
     * @throws Exception
     */
    protected function traiterFactureRecue($idFacture, $idNouveauStatut, $motif = "", $numeroMandat = "")
    {
        return $this->cppWrapper->traiterFactureRecue($idFacture, $idNouveauStatut, $motif);
    }

    /**
     * @param $identifiant_structure
     * @param string $restreindre_structures
     * @return bool|mixed
     * @throws Exception
     */
    public function getIdentifiantStructureCPPByIdentifiantStructure($identifiant_structure, $restreindre_structures = "")
    {
        return $this->cppWrapper->getIdentifiantStructureCPPByIdentifiantStructure($identifiant_structure, $restreindre_structures);
    }
}
