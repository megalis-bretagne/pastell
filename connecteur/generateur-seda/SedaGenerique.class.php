<?php

declare(strict_types=1);

use Pastell\Seda\Message\SedaMessageBuilder;

class SedaGenerique extends SEDAConnecteur
{
    private const SEDA_GENERATOR_VERSION_PATH = '/version';
    private const SEDA_GENERATOR_GENERATE_PATH = '/generate';
    private const SEDA_GENERATOR_GENERATE_PATH_WITH_TEMPLATE = '/generateWithTemplate';

    private const SEDA_GENERATOR_URL_ID = 'seda_generator_url';
    private const SEDA_GENERATOR_GLOBAL_TYPE = 'Generateur SEDA';

    private DonneesFormulaire $connecteurConfig;

    public function __construct(
        private readonly CurlWrapperFactory $curlWrapperFactory,
        private readonly ConnecteurFactory $connecteurFactory,
        private readonly TmpFolder $tmpFolder,
        private readonly SedaMessageBuilder $sedaMessageBuilder,
    ) {
    }

    public function setConnecteurConfig(DonneesFormulaire $connecteurConfig): void
    {
        $this->connecteurConfig = $connecteurConfig;
    }

    private function getSedaGeneratorURL(): string
    {
        $url_from_entity_connector = $this->connecteurConfig->get(self::SEDA_GENERATOR_URL_ID);
        if ($url_from_entity_connector) {
            return $url_from_entity_connector;
        }
        $globalConnectorConfig = $this->connecteurFactory->getGlobalConnecteurConfig(self::SEDA_GENERATOR_GLOBAL_TYPE);
        if (!$globalConnectorConfig) {
            return '';
        }
        return $globalConnectorConfig->get(self::SEDA_GENERATOR_URL_ID);
    }

    /**
     * @throws UnrecoverableException
     */
    public function testConnexion(): string
    {
        if (!$this->getSedaGeneratorURL()) {
            throw new UnrecoverableException(
                "L'URL du générateur n'a pas été trouvé. Avez-vous pensé à créer un connecteur global Generateur SEDA et à l'associer ?"
            );
        }
        $curlWrapper = $this->curlWrapperFactory->getInstance();
        $result = $curlWrapper->get($this->getURLEndpoint(self::SEDA_GENERATOR_VERSION_PATH));
        if ($curlWrapper->getLastHttpCode() != 200) {
            throw new UnrecoverableException(
                'SedaGenerator did not return a 200 response. ' . $curlWrapper->getFullMessage()
            );
        }
        return $result;
    }

    public static function getPastellToSeda(): array
    {
        return [
            'version' => [
                'seda' => 'version',
                'libelle' => 'Version du SEDA',
                'value' => ['1.0', '2.1'],
            ],
            'archival_agency_identifier' => [
                'seda' => 'ArchivalAgency.Identifier',
                'libelle' => "Identifiant du service d'archive",
                'commentaire' => 'ArchivalAgency - Identifier',
            ],
            'archival_agency_name' => [
                'seda' => 'ArchivalAgency.Name',
                'libelle' => "Nom du service d'archive",
                'commentaire' => 'ArchivalAgency - Name',
            ],
            'transferring_agency_identifier' => [
                'seda' => 'TransferringAgency.Identifier',
                'libelle' => 'Identifiant du service versant',
                'commentaire' => 'TransferringAgency - Identifier',
            ],
            'transferring_agency_name' => [
                'seda' => 'TransferringAgency.Name',
                'libelle' => 'Nom du service versant',
                'commentaire' => 'TransferringAgency - Name',
            ],
            'originating_agency_identifier' => [
                'seda' => 'OriginatingAgency.Identifier',
                'libelle' => 'Identifiant du service producteur',
                'commentaire' => 'OriginatingAgency - Identifier',
            ],
            'originating_agency_name' => [
                'seda' => 'OriginatingAgency.Name',
                'libelle' => 'Nom du service producteur',
                'commentaire' => 'OriginatingAgency - Name',
            ],
            'commentaire' => [
                'seda' => 'Comment',
                'libelle' => 'Commentaire',
                'commentaire' => 'Comment',
            ],
            'titre' => [
                'seda' => 'Title',
                'libelle' => 'Titre',
                'commentaire' => 'Archive - Name (seda 1.0) / ArchiveUnit - Title (seda 2.1)',
            ],
            'archival_agreement' => [
                'seda' => 'ArchivalAgreement',
                'libelle' => 'Accord de versement',
                'commentaire' => 'ArchivalAgreement',
            ],
            'ArchivalProfile' => [
                'seda' => 'ArchivalProfile',
                'libelle' => "Profil d'archivage",
                'commentaire' => 'ArchivalProfile',
            ],
            'Language' => [
                'seda' => 'Language',
                'libelle' => 'Langue du contenu',
                'commentaire' => 'Language (forme attendue: fra (seda 1.0) / fr (seda 2.1))',
            ],
            'DescriptionLanguage' => [
                'seda' => 'DescriptionLanguage',
                'libelle' => 'Langue de la description',
                'commentaire' => 'DescriptionLanguage (forme attendue: fra (seda 1.0) / fr (seda 2.1))',
            ],
            'ServiceLevel' => [
                'seda' => 'ServiceLevel',
                'libelle' => 'Niveau de service demandé',
            ],
            'DescriptionLevel' => [
                'seda' => 'DescriptionLevel',
                'libelle' => 'Niveau de description',
                'commentaire' => 'DescriptionLevel (attendue : class, collection, file, fonds, item, recordgrp, series, subfonds, subgrp, subseries)',
            ],
            'archiveunits_title' => [
                'seda' => 'ArchiveUnits.Title',
                'libelle' => "Description de l'unité d'archive principale",
                'commentaire' => 'Archive - Description (seda 1.0) / ArchiveUnit - Description (seda 2.1)',
            ],
            'StartDate' => [
                'seda' => 'StartDate',
                'libelle' => 'Date de début',
                'commentaire' => 'OldestDate (seda 1.0)/ StartDate (seda 2.1) (forme attendue Y-m-d)',
            ],
            'EndDate' => [
                'seda' => 'EndDate',
                'libelle' => 'Date de fin',
                'commentaire' => 'LatestDate (seda 1.0)/ EndDate (seda 2.1)  (forme attendue Y-m-d)',
            ],
            'CustodialHistory' => [
                'seda' => 'CustodialHistory',
                'libelle' => 'Historique de conservation',
                'commentaire' => 'Archive - CustodialHistoryItem (seda 1.0)/ ArchiveUnit - CustodialHistoryItem (seda 2.1)',
            ],
            'AccessRule_Rule' => [
                'seda' => 'AccessRule.Rule',
                'libelle' => "Règle de restriction d'accès",
                'commentaire' => 'Archive - AccessRestrictionRule - Code (seda 1.0)/ AccessRule - Rule (seda 2.1) (forme attendue : de AR038 à AR062)',
            ],
            'AccessRule_StartDate' => [
                'seda' => 'AccessRule.StartDate',
                'libelle' => "Date de départ de la règle de restriction d'accès",
                'commentaire' => 'AccessRestrictionRule - StartDate (seda 1.0) / AccessRule - StartDate (forme attentue Y-m-d)',
            ],
            'AppraisalRule_Rule' => [
                'seda' => 'AppraisalRule.Rule',
                'libelle' => "Sort final - Durée d'utilité administrative",
                'commentaire' => 'AppraisalRule - Duration (seda 1.0) / AppraisalRule - Rule (seda 2.1) (forme attendue encoder en xsd:duration, voir http://www.datypic.com/sc/xsd/t-xsd_duration.html)',
            ],
            'AppraisalRule_StartDate' => [
                'seda' => 'AppraisalRule.StartDate',
                'libelle' => 'Sort final - Date de départ du calcul (Y-m-d)',
                'commentaire' => 'AppraisalRule - StartDate',
            ],
            'AppraisalRule_FinalAction' => [
                'seda' => 'AppraisalRule.FinalAction',
                'libelle' => 'Sort final',
                'commentaire' => 'AppraisalRule - Code (seda 1.0) / AppraisalRule - FinalAction (seda 2.1) (forme attendue: Conserver OU Détruire)',
            ],
        ];
    }

    private function getURLEndpoint(string $endpoint_path): string
    {
        return sprintf(
            '%s%s',
            rtrim($this->getSedaGeneratorURL(), '/'),
            $endpoint_path
        );
    }

    /**
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function getBordereau(FluxData $fluxData): string
    {
        if (!$this->getSedaGeneratorURL()) {
            throw new UnrecoverableException(
                "L'URL du générateur n'a pas été trouvé. Avez-vous pensé à créer un connecteur global Generateur SEDA et à l'associer ?"
            );
        }
        $curlWrapper = $this->curlWrapperFactory->getInstance();

        $dataFromBordereau = json_decode(
            $this->connecteurConfig->getFileContent('data'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $dataFromFiles = $this->connecteurConfig->getFileContent('files');

        $message = $this->getMessage($fluxData, $dataFromBordereau, $dataFromFiles);

        if ($this->connecteurConfig->get('template')) {
            $curlWrapper->addPostFile('template', $this->connecteurConfig->getFilePath('template'));
            $tmp_folder = $this->tmpFolder->create();
            try {
                file_put_contents($tmp_folder . '/data.json', json_encode($message));
                $curlWrapper->addPostFile('json_data', $tmp_folder . '/data.json');
                $url = $this->getURLEndpoint(self::SEDA_GENERATOR_GENERATE_PATH_WITH_TEMPLATE);
            } finally {
                $this->tmpFolder->delete($tmp_folder);
            }
        } else {
            $curlWrapper->setJsonPostData(
                $message,
                0
            );
            $url = $this->getURLEndpoint(self::SEDA_GENERATOR_GENERATE_PATH);
        }

        $result = $curlWrapper->get($url);
        if ($curlWrapper->getLastHttpCode() != 200) {
            throw new UnrecoverableException(
                'SedaGenerator did not return a 200 response. ' . $curlWrapper->getFullMessage()
            );
        }
        if (!$result) {
            throw new UnrecoverableException($curlWrapper->getLastError());
        }
        if (json_decode($result, true)) {
            $json = json_decode($result, true);
            if (isset($json['message'])) {
                throw new UnrecoverableException($json['message']);
            }
        }

        return $result;
    }

    public function validateBordereau(string $bordereau): bool
    {
        return true;
    }

    /**
     * @return LibXMLError[]
     */
    public function getLastValidationError(): array
    {
        return [];
    }

    /**
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function generateArchive(FluxData $fluxData, string $archive_path): void
    {
        $tmp_folder = $this->tmpFolder->create();
        try {
            $this->generateArchiveThrow($fluxData, $archive_path, $tmp_folder);
        } catch (Exception $e) {
            throw new UnrecoverableException($e);
        } finally {
            $this->tmpFolder->delete($tmp_folder);
        }
    }

    /**
     * @throws UnrecoverableException
     * @throws DonneesFormulaireException
     * @throws SimpleXMLWrapperException
     * @throws JsonException
     */
    public function getMessage(FluxData $fluxData, array $dataFromBordereau, string $dataFromFiles): array
    {
        $message = $this->sedaMessageBuilder
            ->setDonneesFormulaire($this->getDocDonneesFormulaire())
            ->setFluxData($fluxData)
            ->buildHeaders($dataFromBordereau)
            ->buildKeywords($dataFromBordereau['keywords'] ?? '')
            ->buildFiles($dataFromFiles)
            ->buildArchiveUnit($dataFromFiles)
            ->getMessage()
        ;

        return json_decode(
            json_encode($message, JSON_THROW_ON_ERROR),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}
