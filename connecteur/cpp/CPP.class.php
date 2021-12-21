<?php

require_once __DIR__ . "/lib/UTF8Encoder.class.php";
require_once __DIR__ . "/lib/CPPWrapperConfig.class.php";
require_once __DIR__ . "/lib/CPPWrapper.class.php";
require_once __DIR__ . "/lib/CPPWrapperFactory.class.php";

/**
 * Class CPP
 *
 * La classe CPP fait le lien entre Pastell et Chorus
 *
 */

class CPP extends PortailFactureConnecteur
{
    private const DEPOSE_DEPUIS_NB_JOURS = 30;

    private $no_change_statut_chorus;
    private $no_recup_facture;

    private $depose_depuis_nb_jours;

    /** @var DonneesFormulaire $globalConfig */
    private $globalConfig;

    /** @var  DonneesFormulaire $connecteurConfig */
    private $connecteurConfig;

    /** @var  CPPWrapper */
    private $cppWrapper;

    /** @var CPPWrapperFactory */
    private $cppWrapperFactory;

    /**
     * CPP constructor.
     * @param ObjectInstancier $objectInstancier
     */
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
        $this->no_change_statut_chorus = $donneesFormulaire->get('no_change_statut_chorus');
        $this->no_recup_facture = $donneesFormulaire->get("no_recup_facture");
        $cppWrapperConfig = new CPPWrapperConfig();
        $cppWrapperConfig->url = $this->getFromLocalOrGlobalConfig('url');
        $cppWrapperConfig->user_login = $donneesFormulaire->get('user_login');
        $cppWrapperConfig->user_password = $donneesFormulaire->get('user_password');
        $cppWrapperConfig->certificat_pem = $this->getFileFromLocalOrGlobalConfig('certificat_pem');
        $cppWrapperConfig->certificat_prikey_pem = $this->getFileFromLocalOrGlobalConfig('certificat_prikey_pem');
        $cppWrapperConfig->certificate_chain = $this->getFileFromLocalOrGlobalConfig('certificate_chain');
        $cppWrapperConfig->certificat_password = $this->getFromLocalOrGlobalConfig('certificat_password');

        $cppWrapperConfig->user_role = $donneesFormulaire->get('user_role');

        $cppWrapperConfig->url_piste_get_token = $this->getFromLocalOrGlobalConfig('url_piste_get_token');
        $cppWrapperConfig->client_id = $this->getFromLocalOrGlobalConfig('client_id');
        $cppWrapperConfig->client_secret = $this->getFromLocalOrGlobalConfig('client_secret');
        $cppWrapperConfig->url_piste_api = $this->getFromLocalOrGlobalConfig('url_piste_api');
        $cppWrapperConfig->cpro_account = base64_encode($donneesFormulaire->get('user_login') . ":" . $donneesFormulaire->get('user_password'));

        $cppWrapperConfig->proxy = $this->getFromLocalOrGlobalConfig('proxy');

        $cppWrapperConfig->user_role = $donneesFormulaire->get('user_role');
        $cppWrapperConfig->identifiant_structure_cpp = $donneesFormulaire->get('identifiant_structure_cpp');
        $cppWrapperConfig->service_destinataire = $donneesFormulaire->get('service_destinataire');
        $this->depose_depuis_nb_jours = $this->getDeposeDepuisNbJours($donneesFormulaire);

        $this->cppWrapper = $this->cppWrapperFactory->newInstance();
        $this->cppWrapper->setCppWrapperConfig($cppWrapperConfig);
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
     * @return mixed
     */
    public function getNoChangeStatutChorus()
    {
        return $this->no_change_statut_chorus;
    }

    /**
     * @return mixed
     */
    public function getNoRecupFacture()
    {
        return $this->no_recup_facture;
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

    /** @deprecated V3.1.0 - utiliser authentification PISTE
     * @return bool
     */
    public function getIsRaccordementCertificat(): bool
    {
        return $this->cppWrapper->getIsRaccordementCertificat();
    }

    /**
     * @param string $idFournisseur
     * @param string $periodeDateHeureEtatCourantDu
     * @return array|mixed
     * @throws Exception
     */
    public function rechercheFactureParRecipiendaire($idFournisseur = "", $periodeDateHeureEtatCourantDu = ""): array
    {
        return $this->cppWrapper->rechercheFactureParRecipiendaire($idFournisseur, $periodeDateHeureEtatCourantDu);
    }

    /**
     * @param string $periodeDateHeureEtatCourantDu
     * @return array|mixed
     * @throws Exception
     */
    public function rechercheFactureTravaux($periodeDateHeureEtatCourantDu = ""): array
    {
        return $this->cppWrapper->rechercheFactureTravaux($periodeDateHeureEtatCourantDu);
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
        return $this->cppWrapper->traiterFactureRecue($idFacture, $idNouveauStatut, $motif, $numeroMandat);
    }

    /**
     * @return array|mixed
     * @throws Exception
     */
    public function listeStructure()
    {
        return $this->cppWrapper->recupererStructuresActivesPourDestinataire();
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

    /**
     * @return array|mixed
     * @throws Exception
     */
    public function getListeService()
    {
        return $this->cppWrapper->getListeService();
    }

    /**
     * @param $filename
     * @param $filecontent
     * @param string $syntaxe_flux
     * @return array|mixed
     * @throws Exception
     */
    public function deposerXML($filename, $filecontent, $syntaxe_flux = 'IN_DP_E1_UBL_INVOICE')
    {
        $data = array(
            'fichierFlux' => base64_encode($filecontent),
            'nomFichier' => $filename,
            'syntaxeFlux' => $syntaxe_flux,
        );
        return $this->call(CPPWrapper::DEPOSER_FLUX, $data);
    }

    /**
     * @param $filename
     * @param $filecontent
     * @return array|mixed
     * @throws Exception
     */
    public function deposerPDF($filename, $filecontent)
    {
        $data = array(
            'fichierFacture' => base64_encode($filecontent),
            'nomFichier' => $filename,
            'formatDepot' => 'PDF_NON_SIGNE',
        );
        return $this->call(CPPWrapper::DEPOSER_PDF, $data);
    }

    /**
     * @param DonneesFormulaire $donneesFormulaire
     * @return array|mixed
     * @throws Exception
     */
    public function soumettreFacture(DonneesFormulaire $donneesFormulaire)
    {
        $data = array(
            'modeDepot' => "DEPOT_PDF_API",
            'numeroFactureSaisi' => $donneesFormulaire->get('numero_facture'),
            'dateFacture' => $donneesFormulaire->get('date_facture'),
            'destinataire' => array(
                'codeDestinataire' => $donneesFormulaire->get('code_destinataire'),
            ),
            'fournisseur' => array(
                "idFournisseur" => intval($donneesFormulaire->get('id_cpp_fournisseur')),
            ),
            'cadreDeFacturation' => array(
                'codeCadreFacturation' => $donneesFormulaire->get('cadre_facturation')
            ),
            'references' => array(
                'deviseFacture' => $donneesFormulaire->get('code_devise_facture'),
                'typeFacture' => $donneesFormulaire->get('type_facture'),
                'typeTva' => $donneesFormulaire->get('type_tva'),
                'modePaiement' => 'VIREMENT',
            ),
            'montantTotal' => array(
                'montantHtTotal' => floatval($donneesFormulaire->get('montant_ht_total')),
                'montantTVA' => floatval($donneesFormulaire->get('montant_tva')),
                'montantTtcTotal' => floatval($donneesFormulaire->get('montant_ttc_avant_remise_global_ttc')),
                'montantAPayer' => floatval($donneesFormulaire->get('montant_a_payer')),
            ),
            'pieceJointePrincipale' => array(
                array(
                    'pieceJointePrincipaleDesignation' => 'ma facture',
                    'pieceJointePrincipaleId' => intval($donneesFormulaire->get('piece_jointe_id'))
                )
            ),
        );

        if ($donneesFormulaire->get('code_service_executant')) {
            $data['destinataire']['codeServiceExecutant'] = $donneesFormulaire->get('code_service_executant');
        }
        if ($donneesFormulaire->get('id_cpp_service_fournisseur')) {
            $data['fournisseur']['idServiceFournisseur'] = $donneesFormulaire->get('id_cpp_service_fournisseur');
        }
        if ($donneesFormulaire->get('numero_bon_commande')) {
            $data['references']['numeroBonCommande'] = $donneesFormulaire->get('numero_bon_commande');
        }
        return $this->call(CPPWrapper::SOUMETTRE_FACTURE, $data);
    }

    /**
     * @param $numero_flux_depot
     * @return mixed
     * @throws Exception
     */
    public function getInfoByNumeroFluxDepot($numero_flux_depot)
    {
        return $this->cppWrapper->getInfoByNumeroFluxDepot($numero_flux_depot);
    }

    /**
     * @param $numero_flux_depot
     * @return array|mixed
     * @throws Exception
     */
    public function consulterCompteRenduImport($numero_flux_depot)
    {
        return $this->cppWrapper->consulterCompteRenduImport($numero_flux_depot);
    }

    /**
     * @param int $supplierCppId
     * @param string $invoiceNumber
     * @return int
     * @throws Exception
     */
    public function getInvoicePerSupplier($supplierCppId, $invoiceNumber)
    {
        return $this->cppWrapper->getCppInvoiceId($supplierCppId, $invoiceNumber);
    }
}
