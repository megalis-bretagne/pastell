<?php

use Pastell\Service\SimpleTwigRenderer;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;

class SedaGenerique extends SEDAConnecteur
{
    private const SEDA_GENERATOR_VERSION_PATH = "/version";
    private const SEDA_GENERATOR_GENERATE_PATH = "/generate";
    private const SEDA_GENERATOR_GENERATE_PATH_WITH_TEMPLATE = "/generateWithTemplate";

    private const SEDA_GENERATOR_URL_ID = 'seda_generator_url';
    private const SEDA_GENERATOR_GLOBAL_TYPE = 'Generateur SEDA';

    /** @var DonneesFormulaire */
    private $connecteurConfig;

    private $idGeneratorFunction = false;

    /** @var string */
    private $zipDirectory;

    public function __construct(
        private CurlWrapperFactory $curlWrapperFactory,
        private ConnecteurFactory $connecteurFactory,
        private TmpFolder $tmpFolder,
    ) {
        $this->setIdGeneratorFunction(function () {
            return "id_" . uuid_create(UUID_TYPE_RANDOM);
        });
    }

    public function __destruct()
    {
        if ($this->zipDirectory) {
            $this->tmpFolder->delete($this->zipDirectory);
        }
    }

    public function setConnecteurConfig(DonneesFormulaire $connecteurConfig): void
    {
        $this->connecteurConfig = $connecteurConfig;
    }

    public function setIdGeneratorFunction(callable $idGeneratorFunction): void
    {
        $this->idGeneratorFunction = $idGeneratorFunction;
    }

    private function getSedaGeneratorURL(): string
    {
        $url_from_entity_connector = $this->connecteurConfig->get(self::SEDA_GENERATOR_URL_ID);
        if ($url_from_entity_connector) {
            return $url_from_entity_connector;
        }
        $globalConnectorConfig = $this->connecteurFactory->getGlobalConnecteurConfig(self::SEDA_GENERATOR_GLOBAL_TYPE);
        if (! $globalConnectorConfig) {
            return '';
        }
        return $globalConnectorConfig->get(self::SEDA_GENERATOR_URL_ID);
    }

    /**
     * @return string
     * @throws UnrecoverableException
     */
    public function testConnexion(): string
    {
        if (! $this->getSedaGeneratorURL()) {
            throw new UnrecoverableException(
                "L'URL du générateur n'a pas été trouvé. Avez-vous pensé à créer un connecteur global Generateur SEDA et à l'associer ?"
            );
        }
        $curlWrapper = $this->curlWrapperFactory->getInstance();
        $result = $curlWrapper->get($this->getURLEndpoint(self::SEDA_GENERATOR_VERSION_PATH));
        if ($curlWrapper->getLastHttpCode() != 200) {
            throw new UnrecoverableException("SedaGenerator did not return a 200 response. " . $curlWrapper->getFullMessage());
        }
        return $result;
    }

    public static function getPastellToSeda(): array
    {
        return [
            'version' => [
                'seda' => 'version',
                'libelle' => 'Version du SEDA',
                'value' => ['1.0','2.1']
            ],
            'archival_agency_identifier' => [
                'seda' => 'ArchivalAgency.Identifier',
                'libelle' => "Identifiant du service d'archive",
                'commentaire' => "ArchivalAgency - Identifier"
            ],
            'archival_agency_name' => [
                'seda' => 'ArchivalAgency.Name',
                'libelle' => "Nom du service d'archive",
                'commentaire' => 'ArchivalAgency - Name'
            ],
            'transferring_agency_identifier' => [
                'seda' => 'TransferringAgency.Identifier',
                'libelle' => "Identifiant du service versant",
                'commentaire' => 'TransferringAgency - Identifier'
            ],
            'transferring_agency_name' => [
                'seda' => 'TransferringAgency.Name',
                'libelle' => "Nom du service versant",
                'commentaire' => 'TransferringAgency - Name'
            ],
            'originating_agency_identifier' => [
                'seda' => 'OriginatingAgency.Identifier',
                'libelle' => "Identifiant du service producteur",
                'commentaire' => 'OriginatingAgency - Identifier'
            ],
            'originating_agency_name' => [
                'seda' => 'OriginatingAgency.Name',
                'libelle' => "Nom du service producteur",
                'commentaire' => 'OriginatingAgency - Name'
            ],
            'commentaire' => [
                'seda' => 'Comment',
                'libelle' => 'Commentaire',
                'commentaire' => 'Comment'
            ],
            'titre' => [
                'seda' => 'Title',
                'libelle' => 'Titre',
                'commentaire' => "Archive - Name (seda 1.0) / ArchiveUnit - Title (seda 2.1)"
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
                'libelle' => "Langue du contenu",
                'commentaire' => "Language (forme attendue: fra (seda 1.0) / fr (seda 2.1))"
            ],
            'DescriptionLanguage' => [
                'seda' => 'DescriptionLanguage',
                'libelle' => "Langue de la description",
                'commentaire' => "DescriptionLanguage (forme attendue: fra (seda 1.0) / fr (seda 2.1))"
            ],
            'ServiceLevel' => [
                'seda' => 'ServiceLevel',
                'libelle' => "Niveau de service demandé"
            ],
            'DescriptionLevel' => [
                'seda' => 'DescriptionLevel',
                'libelle' => "Niveau de description",
                'commentaire' => "DescriptionLevel (attendue : class, collection, file, fonds, item, recordgrp, series, subfonds, subgrp, subseries)"
            ],
            'archiveunits_title' => [
                'seda' => "ArchiveUnits.Title",
                'libelle' => "Description de l'unité d'archive principale",
                'commentaire' => "Archive - Description (seda 1.0) / ArchiveUnit - Description (seda 2.1)"
            ],
            'StartDate' => [
                'seda' => 'StartDate',
                'libelle' => "Date de début",
                'commentaire' => "OldestDate (seda 1.0)/ StartDate (seda 2.1) (forme attendue Y-m-d)"
            ],
            'EndDate' => [
                'seda' => 'EndDate',
                'libelle' => "Date de fin",
                'commentaire' => "LatestDate (seda 1.0)/ EndDate (seda 2.1)  (forme attendue Y-m-d)"
            ],
            'CustodialHistory' => [
                'seda' => 'CustodialHistory',
                'libelle' => "Historique de conservation",
                'commentaire' => "Archive - CustodialHistoryItem (seda 1.0)/ ArchiveUnit - CustodialHistoryItem (seda 2.1)"
            ],
            'AccessRule_Rule' => [
                'seda' => 'AccessRule.Rule',
                'libelle' => "Règle de restriction d'accès",
                'commentaire' => "Archive - AccessRestrictionRule - Code (seda 1.0)/ AccessRule - Rule (seda 2.1) (forme attendue : de AR038 à AR062)"
            ],
            'AccessRule_StartDate' => [
                'seda' => 'AccessRule.StartDate',
                'libelle' => "Date de départ de la règle de restriction d'accès",
                'commentaire' => "AccessRestrictionRule - StartDate (seda 1.0) / AccessRule - StartDate (forme attentue Y-m-d)"
            ],
            'AppraisalRule_Rule' => [
                'seda' => 'AppraisalRule.Rule',
                'libelle' => "Sort final - Durée d'utilité administrative",
                'commentaire' => "AppraisalRule - Duration (seda 1.0) / AppraisalRule - Rule (seda 2.1) (forme attendue encoder en xsd:duration, voir http://www.datypic.com/sc/xsd/t-xsd_duration.html)"
            ],
            'AppraisalRule_StartDate' => [
                'seda' => 'AppraisalRule.StartDate',
                'libelle' => "Sort final - Date de départ du calcul (Y-m-d)",
                'commentaire' => "AppraisalRule - StartDate"
            ],
            'AppraisalRule_FinalAction' => [
                'seda' => 'AppraisalRule.FinalAction',
                'libelle' => "Sort final",
                'commentaire' => "AppraisalRule - Code (seda 1.0) / AppraisalRule - FinalAction (seda 2.1) (forme attendue: Conserver OU Détruire)"
            ]
        ];
    }

    /**
     * @param array $data_file_content
     * @return array
     * @throws LoaderError
     * @throws SyntaxError
     */
    private function getInputDataElement(array $data_file_content): array
    {
        $data = [];
        foreach (self::getPastellToSeda() as $pastell_id => $element_info) {
            if (empty($data_file_content[$pastell_id])) {
                continue;
            }
            $element_id_list = explode(".", $element_info['seda']);
            $the_data = &$data;
            foreach ($element_id_list as $i => $element_id) {
                if ($i < count($element_id_list) - 1) {
                    if (!isset($the_data[$element_id])) {
                        $the_data[$element_id] = [];
                    }
                    $the_data = &$the_data[$element_id];
                } else {
                    $the_data[$element_id] = $this->getStringWithMetatadaReplacement(
                        $data_file_content[$pastell_id]
                    );
                }
            }
        }
        return $data;
    }

    /**
     * @param string $keywords_data
     * @return array
     * @throws LoaderError
     * @throws SyntaxError
     */
    private function getInputDataKeywords(string $keywords_data): array
    {
        $result = [];
        $keywords_data = $this->getStringWithMetatadaReplacement($keywords_data);
        $keywords = explode("\n", $keywords_data);
        foreach ($keywords as $keyword_line) {
            $seda_keywords = [];
            $keyword_line = trim($keyword_line);
            if (! $keyword_line) {
                continue;
            }
            $keyword_properties = str_getcsv($keyword_line);
            $seda_keywords["KeywordContent"] =  $keyword_properties[0];
            if (! empty($keyword_properties[1])) {
                $seda_keywords["KeywordReference"] = $keyword_properties[1];
            }
            if (! empty($keyword_properties[2])) {
                $seda_keywords["KeywordType"] = $keyword_properties[2];
            }
            $result[] = $seda_keywords;
        }
        return $result;
    }

    /**
     * @param FluxData $fluxData
     * @return array
     * @throws DonneesFormulaireException
     * @throws LoaderError
     * @throws SimpleXMLWrapperException
     * @throws SyntaxError
     * @throws UnrecoverableException
     */
    private function getInputDataFiles(FluxData $fluxData): array
    {
        $sedaGeneriqueFilleFiles = new GenerateurSedaFillFiles($this->connecteurConfig->getFileContent('files'));
        $result = [];
        $result[] =  $this->getArchiveUnitDefinition($fluxData, $sedaGeneriqueFilleFiles);
        return $result;
    }


    /**
     * @param array $specificInfo
     * @return array
     * @throws LoaderError
     * @throws SyntaxError
     */
    private function getSedaInfoFromSpecificInfo(array $specificInfo): array
    {
        $seda_archive_units = [];
        if (! empty($specificInfo['Description'])) {
            $seda_archive_units['ContentDescription']['Description'] = $this->getStringWithMetatadaReplacement($specificInfo['Description']);
        }
        if (! empty($specificInfo['DescriptionLevel'])) {
            $seda_archive_units['ContentDescription']['DescriptionLevel'] = $this->getStringWithMetatadaReplacement($specificInfo['DescriptionLevel']);
        }
        if (! empty($specificInfo['Language'])) {
            $seda_archive_units['ContentDescription']['Language'] = $this->getStringWithMetatadaReplacement($specificInfo['Language']);
        }
        if (! empty($specificInfo['CustodialHistory'])) {
            $seda_archive_units['ContentDescription']['CustodialHistory'] = $this->getStringWithMetatadaReplacement($specificInfo['CustodialHistory']);
        }
        if (! empty($specificInfo['AccessRestrictionRule_AccessRule'])) {
            $seda_archive_units['AccessRestrictionRule']['AccessRule'] = $this->getStringWithMetatadaReplacement($specificInfo['AccessRestrictionRule_AccessRule']);
        }
        if (! empty($specificInfo['AccessRestrictionRule_StartDate'])) {
            $seda_archive_units['AccessRestrictionRule']['StartDate'] = $this->getStringWithMetatadaReplacement($specificInfo['AccessRestrictionRule_StartDate']);
        }
        if (
            empty($seda_archive_units['AccessRestrictionRule']['AccessRule']) &&
            empty($seda_archive_units['AccessRestrictionRule']['StartDate'])
        ) {
            unset($seda_archive_units['AccessRestrictionRule']);
        }
        if (! empty($specificInfo['ArchiveUnit_AppraisalRule_FinalAction'])) {
            $seda_archive_units['AppraisalRule']['FinalAction'] = $this->getStringWithMetatadaReplacement($specificInfo['ArchiveUnit_AppraisalRule_FinalAction']);
        }
        if (! empty($specificInfo['ArchiveUnit_AppraisalRule_Rule'])) {
            $seda_archive_units['AppraisalRule']['Rule'] = $this->getStringWithMetatadaReplacement($specificInfo['ArchiveUnit_AppraisalRule_Rule']);
        }
        if (! empty($specificInfo['ArchiveUnit_AppraisalRule_StartDate'])) {
            $seda_archive_units['AppraisalRule']['StartDate'] = $this->getStringWithMetatadaReplacement($specificInfo['ArchiveUnit_AppraisalRule_StartDate']);
        }
        if (
            empty($seda_archive_units['AppraisalRule']['FinalAction']) &&
            empty($seda_archive_units['AppraisalRule']['Rule']) &&
            empty($seda_archive_units['AppraisalRule']['StartDate'])
        ) {
            unset($seda_archive_units['AppraisalRule']);
        }
        if (! empty($specificInfo['Keywords'])) {
            $seda_archive_units['ContentDescription']['Keywords'] = $this->getInputDataKeywords($specificInfo['Keywords']);
        }
        return $seda_archive_units;
    }

    /**
     * @param GenerateurSedaFillFiles $sedaGeneriqueFilleFiles
     * @param string $node_id
     * @return array
     * @throws LoaderError
     * @throws SyntaxError
     * @throws UnrecoverableException
     */
    private function getSpecificInfo(GenerateurSedaFillFiles $sedaGeneriqueFilleFiles, string $node_id): array
    {
        $specificInfo = $this->getSpecificInfoDefinition($sedaGeneriqueFilleFiles, $node_id);
        if (! $specificInfo) {
            return $specificInfo;
        }
        $specificInfo = $this->getSedaInfoFromSpecificInfoWithLocalDescription($specificInfo, "", true);
        return $this->getSedaInfoFromSpecificInfo(
            $specificInfo
        );
    }

    /**
     * @param GenerateurSedaFillFiles $sedaGeneriqueFilleFiles
     * @param string $node_id
     * @return array
     * @throws UnrecoverableException
     */
    private function getSpecificInfoDefinition(GenerateurSedaFillFiles $sedaGeneriqueFilleFiles, string $node_id): array
    {
        $seda_archive_units = [];
        if (! $node_id) {
            return $seda_archive_units;
        }
        return $sedaGeneriqueFilleFiles->getArchiveUnitSpecificInfo($node_id);
    }



    /**
     * @param FluxData $fluxData
     * @param GenerateurSedaFillFiles $sedaGeneriqueFilleFiles
     * @param string $parent_id
     * @return array
     * @throws DonneesFormulaireException
     * @throws LoaderError
     * @throws SyntaxError
     * @throws UnrecoverableException
     */
    private function getArchiveUnitDefinition(FluxData $fluxData, GenerateurSedaFillFiles $sedaGeneriqueFilleFiles, string $parent_id = ""): array
    {
        $seda_archive_units = [];
        $seda_archive_units['Id'] = ($this->idGeneratorFunction)();
        if ($parent_id) {
            $seda_archive_units['Title'] = $this->getStringWithMetatadaReplacement($sedaGeneriqueFilleFiles->getDescription($parent_id));
        }

        $seda_archive_units = array_merge($seda_archive_units, $this->getSpecificInfo($sedaGeneriqueFilleFiles, $parent_id));

        foreach ($sedaGeneriqueFilleFiles->getFiles($parent_id) as $files) {
            $field = $this->getStringWithMetatadaReplacement(strval($files['field_expression']));
            if (preg_match("/#ZIP#/", $field)) {
                $archiveFromZip = $this->getArchiveUnitFromZip(
                    $fluxData,
                    strval($files['description']),
                    $field,
                    0,
                    $this->getSpecificInfoDefinition($sedaGeneriqueFilleFiles, $parent_id),
                    (!empty($files['do_not_put_mime_type']))
                );
                $seda_archive_units['ArchiveUnits'] = array_merge($seda_archive_units['ArchiveUnits'] ?? [], $archiveFromZip['ArchiveUnits'] ?? []);
                $seda_archive_units['Files'] = array_merge($seda_archive_units['Files'] ?? [], $archiveFromZip['Files'] ?? []);
                continue;
            }

            if (! is_array($this->getDocDonneesFormulaire()->get($field))) {
                continue;
            }
            foreach ($this->getDocDonneesFormulaire()->get($field) as $filenum => $filename) {
                $file_unit = [];
                $file_unit['Filename'] = $filename;
                $file_unit['MessageDigest'] = $this->getDocDonneesFormulaire()->getFileDigest($field, $filenum);
                $file_unit['Size'] = strval($this->getDocDonneesFormulaire()->getFileSize($field, $filenum));
                if (empty($files['do_not_put_mime_type'])) {
                    $file_unit['MimeType'] = $this->getDocDonneesFormulaire()->getContentType($field, $filenum);
                }
                $description = strval($files['description']);
                $description = preg_replace("/#FILE_NUM#/", $filenum, $description);
                $file_unit['Title'] = $this->getStringWithMetatadaReplacement($description);
                $seda_archive_units['Files'][($this->idGeneratorFunction)()] = $file_unit;
                $fluxData->setFileList(
                    $files['field_expression'],
                    $filename,
                    $this->getDocDonneesFormulaire()->getFilePath($field, $filenum)
                );
            }
        }
        foreach ($sedaGeneriqueFilleFiles->getArchiveUnit($parent_id) as $archiveUnit) {
            if (strval($archiveUnit['field_expression'])) {
                $field_expression_result = $this->getStringWithMetatadaReplacement(strval($archiveUnit['field_expression']));
                if (! $field_expression_result) {
                    continue;
                }
            }

            $seda_archive_units['ArchiveUnits'][($this->idGeneratorFunction)()] = $this->getArchiveUnitDefinition($fluxData, $sedaGeneriqueFilleFiles, $archiveUnit['id']);
        }
        return $seda_archive_units;
    }

    /**
     * @param FluxData $fluxData
     * @return array
     * @throws DonneesFormulaireException
     * @throws LoaderError
     * @throws SimpleXMLWrapperException
     * @throws SyntaxError
     * @throws UnrecoverableException
     */
    private function getInputData(FluxData $fluxData): array
    {
        $data_file_content = json_decode($this->connecteurConfig->getFileContent('data'), true);
        if (!$data_file_content) {
            $data_file_content = [];
        }
        $data = $this->getInputDataElement($data_file_content);
        $data['Keywords'] = $this->getInputDataKeywords($data_file_content['keywords'] ?? "");
        $inputDataFiles = $this->getInputDataFiles($fluxData);
        $data['ArchiveUnits'] = $inputDataFiles[0]['ArchiveUnits'] ?? [];
        $data['Files'] = $inputDataFiles[0]['Files'] ?? [];

        if (! empty($data_file_content['archiveunits_title'])) {
            $data['Description'] = $this->getStringWithMetatadaReplacement($data_file_content['archiveunits_title']);
        }

        $appraisailRuleFinalAction = [
            '1.0' => [
                'Conserver' => 'conserver',
                'Détruire' => 'detruire'
            ],
            '2.1' => [
                'Conserver' => 'Keep',
                'Détruire' => 'Destroy'
            ],
        ];
        if (! empty($data['AppraisalRule']['FinalAction'])) {
            $data['AppraisalRule']['FinalAction'] = $appraisailRuleFinalAction[$data['version'] ?? '2.1'][$data['AppraisalRule']['FinalAction']];
        }
        if (empty($data['StartDate'])) {
            unset($data['StartDate']);
        }
        if (empty($data['EndDate'])) {
            unset($data['EndDate']);
        }
        return $data;
    }

    /**
     * @param $expression
     * @return string
     * @throws LoaderError
     * @throws SyntaxError
     */
    private function getStringWithMetatadaReplacement($expression): string
    {
        $simpleTwigRenderer = new SimpleTwigRenderer();
        return $simpleTwigRenderer->render(
            $expression,
            $this->getDocDonneesFormulaire()
        );
    }

    private function getURLEndpoint(string $endpoint_path): string
    {
        return sprintf(
            "%s%s",
            rtrim($this->getSedaGeneratorURL(), "/"),
            $endpoint_path
        );
    }

    /**
     * @param FluxData $fluxData
     * @return bool|string
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function getBordereauNG(FluxData $fluxData): string
    {
        if (! $this->getSedaGeneratorURL()) {
            throw new UnrecoverableException("L'URL du générateur n'a pas été trouvé. Avez-vous pensé à créer un connecteur global Generateur SEDA et à l'associer ?");
        }
        $curlWrapper = $this->curlWrapperFactory->getInstance();

        if ($this->connecteurConfig->get('template')) {
            $curlWrapper->addPostFile('template', $this->connecteurConfig->getFilePath('template'));
            $data = $this->getInputData($fluxData);
            $tmp_folder = $this->tmpFolder->create();
            try {
                file_put_contents($tmp_folder . "/data.json", json_encode($data));
                $curlWrapper->addPostFile('json_data', $tmp_folder . "/data.json");
                $url = $this->getURLEndpoint(self::SEDA_GENERATOR_GENERATE_PATH_WITH_TEMPLATE);
            } finally {
                $this->tmpFolder->delete($tmp_folder);
            }
        } else {
            $curlWrapper->setJsonPostData(
                $this->getInputData($fluxData),
                0
            );
            $url = $this->getURLEndpoint(self::SEDA_GENERATOR_GENERATE_PATH);
        }

        $result = $curlWrapper->get($url);
        if ($curlWrapper->getLastHttpCode() != 200) {
            throw new UnrecoverableException("SedaGenerator did not return a 200 response. " . $curlWrapper->getFullMessage());
        }
        if (! $result) {
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
     * @param FluxData $fluxData
     * @param string $archive_path
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
     * @throws LoaderError
     * @throws SyntaxError
     * @throws UnrecoverableException
     * @throws Exception
     */
    private function getArchiveUnitFromZip(
        FluxData $fluxData,
        string $description,
        string $field_expression,
        int $filenum = 0,
        array $specific_info = [],
        bool $do_not_put_mime_type = false
    ): array {
        $field = preg_replace("/#ZIP#/", "", $field_expression);

        $zip_file_path = $this->getDocDonneesFormulaire()->getFilePath($field, $filenum);
        if (!$zip_file_path) {
            return [];
        }

        $this->zipDirectory = $this->tmpFolder->create();

        $zip = new ZipArchive();
        $handle = $zip->open($zip_file_path);
        if (!$handle) {
            throw new UnrecoverableException("Impossible d'ouvrir le fichier zip");
        }
        $zip->extractTo($this->zipDirectory);
        $zip->close();
        return $this->getArchiveUnitFromFolder(
            $fluxData,
            $description,
            $this->zipDirectory,
            $field,
            $this->zipDirectory,
            $specific_info,
            $do_not_put_mime_type
        );
    }

    /**
     * @param FluxData $fluxData
     * @param string $description
     * @param string $folder
     * @param string $field
     * @param string $root_folder
     * @param bool $do_not_put_mime_type
     * @param array $specific_info
     * @return array
     * @throws LoaderError
     * @throws SyntaxError
     */
    private function getArchiveUnitFromFolder(FluxData $fluxData, string $description, string $folder, string $field, string $root_folder, array $specific_info, bool $do_not_put_mime_type = false): array
    {

        $local_description = $this->getLocalDescription(
            $description,
            $this->getRelativePath($root_folder, $folder),
            true
        );

        $result['Id'] = ($this->idGeneratorFunction)();
        $result['Title'] = $this->getStringWithMetatadaReplacement($local_description);

        $result = array_merge(
            $result,
            $this->getSedaInfoFromSpecificInfo(
                $this->getSedaInfoFromSpecificInfoWithLocalDescription(
                    $specific_info,
                    $this->getRelativePath($root_folder, $folder),
                    true
                )
            )
        );

        $dir_content = array_diff(scandir($folder), $this->exludeFileList());

        foreach ($dir_content as $file_or_folder) {
            $filepath = $folder . "/" . $file_or_folder;
            if (is_dir($filepath)) {
                $result['ArchiveUnits'][($this->idGeneratorFunction)()] = $this->getArchiveUnitFromFolder($fluxData, $description, $filepath, $field, $root_folder, $specific_info, $do_not_put_mime_type);
            } elseif (is_file($filepath)) {
                $relative_path = $this->getRelativePath($root_folder, $filepath);
                $file_unit = [];
                $file_unit['Filename'] = $relative_path;
                $file_unit['MessageDigest'] = hash_file('sha256', $filepath);
                $file_unit['Size'] = filesize($filepath);
                $fileContentType = new FileContentType();
                if (!$do_not_put_mime_type) {
                    $file_unit['MimeType'] = $fileContentType->getContentType($filepath);
                }

                $local_description = $this->getLocalDescription($description, $relative_path, false);
                $file_unit['Title'] = $this->getStringWithMetatadaReplacement($local_description);
                $result['Files'][($this->idGeneratorFunction)()] = $file_unit;
                $fluxData->setFileList(
                    $field,
                    $relative_path,
                    $filepath
                );
            }
        }
        return $result;
    }

    private function getSedaInfoFromSpecificInfoWithLocalDescription(array $specif_info, string $filepath, bool $is_dir): array
    {
        $result = [];
        foreach ($specif_info as $id => $expression) {
            $result[$id] = $this->getLocalDescription($expression, $filepath, $is_dir);
        }
        return $result;
    }

    private function getRelativePath(string $root_folder, string $local_folder): string
    {
        $relative_path = preg_replace("#$root_folder#", "", $local_folder);
        return ltrim($relative_path, "/");
    }

    private function getLocalDescription(string $description, string $filepath, bool $id_dir): string
    {
        $local_description = preg_replace("/#FILEPATH#/", $filepath, $description);
        $local_description = preg_replace("/#FILENAME#/", basename($filepath), $local_description);
        $local_description = preg_replace("/#IS_DIR#/", $id_dir ? "true" : "false", $local_description);
        return preg_replace("/#IS_FILE#/", $id_dir ? "false" : "true", $local_description);
    }

    private function exludeFileList(): array
    {
        return ['.','..','__MACOSX','.DS_Store','.gitkeep'];
    }
}
