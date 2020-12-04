<?php

use Pastell\Service\SimpleTwigRenderer;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;

require_once PASTELL_PATH . "/connecteur/seda-ng/lib/FluxData.class.php";
require_once PASTELL_PATH . "/connecteur/seda-ng/lib/FluxDataTest.class.php";
require_once PASTELL_PATH . "/connecteur/seda-ng/SedaNG.class.php";
require_once __DIR__ . "/lib/GenerateurSedaFillFiles.class.php";

class SedaGenerique extends SedaNG
{
    /** @var DonneesFormulaire */
    private $connecteurConfig;
    private $curlWrapperFactory;

    private const SEDA_GENERATOR_GENERATE_PATH = "/generate";
    private const SEDA_GENERATOR_GENERATE_PATH_WITH_TEMPLATE = "/generateWithTemplate";

    private $idGeneratorFunction = false;

    public function __construct(CurlWrapperFactory $curlWrapperFactory)
    {
        $this->curlWrapperFactory = $curlWrapperFactory;
        $this->setIdGeneratorFunction(function () {
            return "id_" . uuid_create(UUID_TYPE_RANDOM);
        });
    }

    public function setConnecteurConfig(DonneesFormulaire $donneesFormulaire)
    {
        $this->connecteurConfig = $donneesFormulaire;
    }

    public function setIdGeneratorFunction(callable $idGeneratorFunction)
    {
        $this->idGeneratorFunction = $idGeneratorFunction;
    }

    /**
     * @param array $transactionsInfo
     * @return string|void
     * @throws UnrecoverableException
     */
    public function getBordereau(array $transactionsInfo)
    {
        throw new UnrecoverableException("Le connecteur SEDA n'est pas supporté par ce flux...");
    }

    public static function getPastellToSeda(): array
    {
        $result = [
            'version' => [
                'seda' => 'version',
                'libelle' => 'Version du SEDA',
                'value' => ['1.0','2.1']
            ],
            'archival_agency_identifier' => [
                'seda' => 'ArchivalAgency.Identifier',
                'libelle' => "Identifiant du service d'archive",
            ],
            'archival_agency_name' => [
                'seda' => 'ArchivalAgency.Name',
                'libelle' => "Nom du service d'archive",
            ],
            'transferring_agency_identifier' => [
                'seda' => 'TransferringAgency.Identifier',
                'libelle' => "Identifiant du service versant",
            ],
            'transferring_agency_name' => [
                'seda' => 'TransferringAgency.Name',
                'libelle' => "Nom du service versant",
            ],
            'originating_agency_identifier' => [
                'seda' => 'OriginatingAgency.Identifier',
                'libelle' => "Identifiant du service producteur",
            ],
            'originating_agency_name' => [
                'seda' => 'OriginatingAgency.Name',
                'libelle' => "Nom du service producteur",
            ],

            'commentaire' => [
                'seda' => 'Comment',
                'libelle' => 'Commentaire',
            ],
            'titre' => [
                'seda' => 'Title',
                'libelle' => 'Titre',
            ],
            'archival_agreement' => [
                'seda' => 'ArchivalAgreement',
                'libelle' => 'Accord de versement'
            ],
            'ArchivalProfile' => [
                'seda' => 'ArchivalProfile',
                'libelle' => "Profil d'archivage"
            ],
            'CustodialHistory' => [
                'seda' => 'CustodialHistory',
                'libelle' => "Historique de conservation"
            ],
            'Language' => [
                'seda' => 'Language',
                'libelle' => "Langue du contenu"
            ],
            'DescriptionLanguage' => [
                'seda' => 'DescriptionLanguage',
                'libelle' => "Langue du la description"
            ],
            'StartDate' => [
                'seda' => 'StartDate',
                'libelle' => "Date de début (Y-m-d)"
            ],
            'EndDate' => [
                'seda' => 'EndDate',
                'libelle' => "Date de fin (Y-m-d)"
            ],
            'AccessRule' => [
                'seda' => 'AccessRule',
                'libelle' => "Règle de restriction d'accès",
            ],
            'AppraisalRule_Rule' => [
                'seda' => 'AppraisalRule.Rule',
                'libelle' => "Règle du sort final ",
                'commentaire' => "Encoder en xsd:duration, voir http://www.datypic.com/sc/xsd/t-xsd_duration.html"
            ],
            'AppraisalRule_FinalAction' => [
                'seda' => 'AppraisalRule.FinalAction',
                'libelle' => "Action finale",
                'value' => ['Conserver','Détruire']
            ],
            'archiveunits_title' => [
                'seda' => "ArchiveUnits.Title",
                'libelle' => "Description de l'unité d'archive principale"
            ]
        ];

        foreach (range(38, 62) as $nb) {
            $result['AccessRule']['value'][] = "AR0$nb";
        }

        return $result;
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
        $keywords = explode("\n", $keywords_data);
        foreach ($keywords as $keyword_line) {
            $seda_keywords = [];
            $keyword_line = trim($keyword_line);
            if (! $keyword_line) {
                continue;
            }
            $keyword_properties = explode(",", $keyword_line, 3);
            $seda_keywords["KeywordContent"] =  $this->getStringWithMetatadaReplacement($keyword_properties[0]);
            if (! empty($keyword_properties[1])) {
                $seda_keywords["KeywordReference"] = $this->getStringWithMetatadaReplacement($keyword_properties[1]);
            }
            if (! empty($keyword_properties[2])) {
                $seda_keywords["KeywordType"] = $this->getStringWithMetatadaReplacement($keyword_properties[2]);
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
     */
    private function getInputDataFiles(FluxData $fluxData): array
    {
        $sedaGeneriqueFilleFiles = new GenerateurSedaFillFiles($this->connecteurConfig->getFileContent('files'));
        $result = [];

        $result[] = $this->getArchiveUnitDefintion($fluxData, $sedaGeneriqueFilleFiles, "");
        return $result;
    }

    private function getArchiveUnitDefintion(FluxData $fluxData, GenerateurSedaFillFiles $sedaGeneriqueFilleFiles, string $parent_id = "")
    {
        $seda_archive_units = [];
        $seda_archive_units['Id'] = ($this->idGeneratorFunction)();
        if ($parent_id) {
            $seda_archive_units['Title'] = $this->getStringWithMetatadaReplacement($sedaGeneriqueFilleFiles->getDescription($parent_id));
        }
        foreach ($sedaGeneriqueFilleFiles->getFiles($parent_id) as $files) {
            $field = $this->getStringWithMetatadaReplacement(strval($files['field_expression']));

            if (preg_match("/#ZIP#/", $field)) {
                $seda_archive_units['ArchiveUnits'][($this->idGeneratorFunction)()] =
                    $this->getArchiveUnitFromZip(
                        $fluxData,
                        strval($files['description']),
                        $field,
                        0
                    );
                continue;
            }

            if (! is_array($this->getDocDonneesFormulaire()->get($field))) {
                continue;
            }
            foreach ($this->getDocDonneesFormulaire()->get($field) as $filenum => $filename) {
                $file_unit = [];
                $file_unit['Filename'] = $filename;
                $file_unit['MessageDigest'] = $this->getDocDonneesFormulaire()->getFileDigest($field, $filenum, 'sha256');
                $file_unit['Size'] = strval($this->getDocDonneesFormulaire()->getFileSize($field, $filenum));
                $file_unit['MimeType'] = $this->getDocDonneesFormulaire()->getContentType($field, $filenum);
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

            $seda_archive_units['ArchiveUnits'][($this->idGeneratorFunction)()] = $this->getArchiveUnitDefintion($fluxData, $sedaGeneriqueFilleFiles, $archiveUnit['id']);
        }
        return $seda_archive_units;
    }

    /**
     * @param FluxData $fluxData
     * @return array
     * @throws LoaderError
     * @throws SyntaxError
     */
    private function getInputData(FluxData $fluxData): array
    {
        $data_file_content = json_decode($this->connecteurConfig->getFileContent('data'), true);
        if (!$data_file_content) {
            $data_file_content = [];
        }

        $data = $this->getInputDataElement($data_file_content);
        $data['Keywords'] = $this->getInputDataKeywords($data_file_content['keywords'] ?? "");
        $data['ArchiveUnits'] = $this->getInputDataFiles($fluxData);

        if (! empty($data_file_content['archiveunits_title'])) {
            $data['ArchiveUnits'][0]['Title'] = $this->getStringWithMetatadaReplacement($data_file_content['archiveunits_title']);
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
            rtrim($this->connecteurConfig->get('seda_generator_url'), "/"),
            $endpoint_path
        );
    }

    /**
     * @param FluxData $fluxData
     * @return bool|string
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function getBordereauNG(FluxData $fluxData)
    {
        if (! $this->connecteurConfig->get('seda_generator_url')) {
            throw new UnrecoverableException("Il faut spécifier l'URL du générateur de SEDA");
        }
        $curlWrapper = $this->curlWrapperFactory->getInstance();

        if ($this->connecteurConfig->get('template')) {
            $curlWrapper->addPostFile('template', $this->connecteurConfig->getFilePath('template'));
            $data = $this->getInputData($fluxData);
            $tmpFolder = new TmpFolder();
            $tmp_folder = $tmpFolder->create();
            file_put_contents($tmp_folder . "/data.json", json_encode($data));
            $curlWrapper->addPostFile('json_data', $tmp_folder . "/data.json");
            $url = $this->getURLEndpoint(self::SEDA_GENERATOR_GENERATE_PATH_WITH_TEMPLATE);
        } else {
            $curlWrapper->setJsonPostData(
                $this->getInputData($fluxData),
                0
            );
            $url = $this->getURLEndpoint(self::SEDA_GENERATOR_GENERATE_PATH);
        }

        $result = $curlWrapper->get($url);
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

    public function validateBordereau(string $bordereau)
    {
        return true;
    }

    public function getLastValidationError()
    {
        return false;
    }

    /**
     * @param FluxData $fluxData
     * @param string $archive_path
     * @return false|void
     * @throws UnrecoverableException
     * @throws Exception
     */
    public function generateArchive(FluxData $fluxData, string $archive_path)
    {
        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();

        $files_list = "";

        foreach ($fluxData->getFilelist() as $file_id) {
            $filename = $file_id['filename'];
            $filepath = $file_id['filepath'];

            if (! $filepath) {
                break;
            }
            $dirname = dirname($tmp_folder . "/" . $filename);
            if (! file_exists($dirname)) {
                mkdir($dirname, 0777, true);
            }
            copy($filepath, "$tmp_folder/$filename");
            $files_list .= escapeshellarg($filename) . " ";
        }

        $command = "tar cvzf $archive_path --directory $tmp_folder -- $files_list 2>&1";

        exec($command, $output, $return_var);

        if ($return_var != 0) {
            $output = implode("\n", $output);
            throw new UnrecoverableException("Impossible de créer le fichier d'archive $archive_path - status : $return_var - output: $output");
        }

        $tmpFolder->delete($tmp_folder);
    }

    private function getArchiveUnitFromZip(FluxData $fluxData, string $description, string $field_expression, int $filenum = 0): array
    {
        $field = preg_replace("/#ZIP#/", "", $field_expression);

        $zip_file_path = $this->getDocDonneesFormulaire()->getFilePath($field);
        if (! $zip_file_path) {
            return [];
        }

        $tmpFolder = new TmpFolder();
        $tmp_folder = $tmpFolder->create();

        $zip = new ZipArchive();
        $handle = $zip->open($zip_file_path);
        if (!$handle) {
            throw new UnrecoverableException("Impossible d'ouvrir le fichier zip");
        }

        $zip->extractTo($tmp_folder);
        $zip->close();

        return $this->getArchiveUnitFromFolder($fluxData, $description, $tmp_folder, $field, $tmp_folder);
    }

    private function getArchiveUnitFromFolder(FluxData $fluxData, string $description, string $folder, string $field, string $root_folder): array
    {

        $local_description = $this->getLocalDescription(
            $description,
            $this->getRelativePath($root_folder, $folder),
            true
        );

        $result['id'] = ($this->idGeneratorFunction)();
        $result['Title'] = $this->getStringWithMetatadaReplacement($local_description);

        $dir_content = array_diff(scandir($folder), $this->exludeFileList());

        foreach ($dir_content as $file_or_folder) {
            $filepath = $folder . "/" . $file_or_folder;
            if (is_dir($filepath)) {
                $result['ArchiveUnits'][($this->idGeneratorFunction)()] = $this->getArchiveUnitFromFolder($fluxData, $description, $filepath, $field, $root_folder);
            } elseif (is_file($filepath)) {
                $relative_path = $this->getRelativePath($root_folder, $filepath);
                $file_unit = [];
                $file_unit['Filename'] = $relative_path;
                $file_unit['MessageDigest'] = hash_file('sha256', $filepath);
                $file_unit['Size'] = filesize($filepath);
                $fileInfo = new finfo();
                $file_unit['MimeType'] = $fileInfo->file($filepath, FILEINFO_MIME_TYPE);

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

    private function exludeFileList()
    {
        return ['.','..','__MACOSX','.DS_Store','.gitkeep'];
    }
}
